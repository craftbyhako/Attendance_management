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

        $statusLabel = match($attendance->attendance_sutatus_id) {
                1 => '勤務外',
                2 => '出勤中',
                3 => '休憩中',
                4 => '退勤済',
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

    public function index()
    {
        $target_month = Carbon::now()->locale('ja')->isoFormat('YYYY/MM');
        $attendances = Attendance::where('year_month', $target_month)
        // ->select(
        //     'user_id',
        //     'year_month',
        //     'day',
        //     'clock_in',
        //     'clock_out',
        //     'break1_start',
        //     'break1_end',
        //     'break2_start',
        //     'break2_end'
        // )
        ->get();

        $prev_month = Carbon::now()->subMonth()->format('Y-m');
        $next_month = Carbon::now()->addMonth()->format('Y-m');

       
        
        
        
        return view('user.index', compact('attendances', 'target_month'));
    }
    
}
