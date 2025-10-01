<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Requests\DetailRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{

    public function create() 
    {
        $todayYearMonth = now()->format('Y-m');
        $todayDay = now()->day;

        // 今日の最新勤怠を取得
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('year_month', $todayYearMonth)
            ->where('day', $todayDay)
            ->first();

        // 日付が変わったら「出勤前」にする
        if (!$attendance) 
        {
            $attendance = new Attendance ([
                'attendance_status_id' => 1, 
                'year_month' => $todayYearMonth,
                'day' => $todayDay,
            ]);
        }

        // dd($attendance->attendance_status_id);

        switch ($attendance->attendance_status_id) {
            case 1:
                $statusLabel = '勤務外';
                break;
            case 2:
                $statusLabel = '出勤中';
                break;
            case 3:
                $statusLabel = '休憩中';
                break;
            case 4:
                $statusLabel = '退勤済';
                break;
            default:
                $statusLabel = '不明';
                break;
        };

        $breakPairs = ['break1_start', 'break1_end', 'break2_start', 'break2_end'];
        
        // buttonのvalue値の設定
        $breakAction = null;

        foreach ($breakPairs as $pair){
            if(is_null($attendance->$pair)){
                    $breakAction = $pair;
                    break;
            }    
        }
        return view('user.create-attendance', compact('attendance', 'statusLabel', 'breakAction'));
    }

    public function store(DetailRequest $request)
    {
        // dd($request->all(), Auth::id());
        
        $action = $request->input('action');
        $user = Auth::user();

        // 今日の勤怠を取得
        $todayAttendance = $user->attendances()
                    ->where('year_month', now()->format('Y-m'))
                    ->where('day', now()->day)
                    ->first();

        if(! $todayAttendance && $action === 'clock_in'){
            $todayAttendance = $user->attendances()->create([
                'attendance_status_id' => 2,
                'year_month' => now()->format('Y-m'),
                'day' => now()->day,
                'clock_in' => now(),
            ]);
        }

        if(!$todayAttendance) {
            return redirect()->back()->with('error', '今日の勤怠レコードがありません');
        }

        switch($action) {
            // 出勤処理
            case 'clock_in' :
                if(!$todayAttendance->clock_in) {
                    $todayAttendance->update([
                        'attendance_status_id' => 2,
                        'clock_in' => now(),
                    ]);
                }
            break;

            // 退勤処理
            case 'clock_out' :
                $todayAttendance->update([
                        'attendance_status_id' => 4,
                        'clock_out' => now(),
                    ]);
            break;

            // 休憩開始
            case 'break1_start':
            case 'break2_start':
                $todayAttendance->update([
                        'attendance_status_id' => 3,
                        $action => now(),
                    ]);
                break;

            // 休憩終了
            case 'break1_end':
            case 'break2_end':
                $todayAttendance->update([
                        'attendance_status_id' => 2,
                        $action => now(),
                    ]);
            break;
        }

        return redirect()->back();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $target_month = $request->input('month', Carbon::now()->format('Y-m'));
        
        $attendances = Attendance::where('year_month', $target_month)
        ->select(
            'id',
            'user_id',
            'year_month',
            'day',
            'clock_in',
            'clock_out',
            'break1_start',
            'break1_end',
            'break2_start',
            'break2_end'
        )
        ->get()
        ->map(function ($attendance) {
            // 秒なし表示
            $attendance->clock_in = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
            $attendance->clock_out = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';

            // 休憩時間の計算
            $totalBreakTime = 0;
            
            if ($attendance->break1_start && $attendance->break1_end) {
                $totalBreakTime += Carbon::parse($attendance->break1_end)->diffInMinutes(Carbon::parse($attendance->break1_start)); 
            }

            if ($attendance->break2_start && $attendance->break2_end) {
                $totalBreakTime+= Carbon::parse($attendance->break2_end)->diffInMinutes(Carbon::parse($attendance->break2_start));
            }

            // 分を変換
            $hours = floor($totalBreakTime / 60);
            $minutes = $totalBreakTime % 60;
            $attendance->totalBreakTime = sprintf('%02d:%02d', $hours, $minutes);

            // 労働時間の計算
            if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in)) - $totalBreakTime;
                
                if ($workMinutes < 0){
                    $workMinutes = 0;
                }
                
                $workHours = floor($workMinutes / 60);
                $workRemainMinutes = $workMinutes % 60;
                $attendance->totalWorkingTime = sprintf('%02d:%02d', $workHours, $workRemainMinutes);
            } else {
                $attendance->totalWorkingTime = '';
            }
            return $attendance;
        });

        $carbonMonth = Carbon::parse($target_month);
        $prev_month = $carbonMonth->copy()->subMonth()->format('Y-m');
        $next_month = $carbonMonth->copy()->addMonth()->format('Y-m');
        
        return view('user.index', compact('attendances', 'target_month', 'prev_month', 'next_month'));
    }

    public function showDetail($id){
        $attendance = Attendance::find($id);
        return view('user.detail', compact('attendance'));
    }

    // public function update($request) {
    //     $user = Auth::user();
    //     $attendance->clock_in = $request->clock_in;
    //     $attendance->save(); 
    //     return redirect('/attendance')->with('success', '詳細情報を更新しました');
    // }
}
