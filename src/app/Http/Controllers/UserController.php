<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\UpdatedAttendance;
use App\Models\ApproveStatus;
use App\Http\Requests\DetailRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


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
                'user_id' => auth()->id(),
                'attendance_status_id' => 1, 
                'year_month' => $todayYearMonth,
                'day' => $todayDay,
            ]);
        }

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

    public function store(Request $request)
    {
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

        // dd($todayAttendance->toArray());

        switch($action) {
            // 出勤処理
            case 'clock_in' :
                if (is_null($todayAttendance->clock_in)) {
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

        return redirect()->route('user.create');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $target_month = $request->input('month', Carbon::now()->format('Y-m'));
        
        $attendances = Attendance::where('year_month', $target_month)
        ->where('user_id', $user->id)
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

    public function showDetail($id)
    {
        $user = Auth::user();
        
        $attendance = Attendance::find($id);

         // 存在しなければ 404
        if (!$attendance) {
            abort(404, '勤怠情報が存在しません');
    }
        
        $isLocked = (bool)$attendance->is_editable === false;

        $targetDate = Carbon::parse($attendance->year_month. '-'. $attendance->day)
        ->locale('ja')
        ->isoFormat('YYYY年M月D日');

        $clock_in = $attendance->clock_in ? trim(Carbon::parse($attendance->clock_in)->format('H:i')) : '';
        $clock_out = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';
        $break1_start = $attendance->break1_start ? trim(Carbon::parse($attendance->break1_start)->format('H:i')) : '';
        $break1_end = $attendance->break1_end ? trim(Carbon::parse($attendance->break1_end)->format('H:i')) : '';
        $break2_start = $attendance->break2_start ? trim(Carbon::parse($attendance->break2_start)->format('H:i')) : '';
        $break2_end = $attendance->break2_end ? trim(Carbon::parse($attendance->break2_end)->format('H:i')) : '';

        return view('user.detail', compact('attendance','user','targetDate', 'clock_in', 'clock_out','break1_start', 'break1_end', 'break2_start', 'break2_end', 'isLocked'));
    }

    public function updateDetail(DetailRequest $request, $id) 
    {
        $user = Auth::user();

        $attendance = Attendance::find($id);

        //１ フォームの内容をセット
        $attendance->clock_in = $request->input('clock_in');
        $attendance->clock_out = $request->input('clock_out');
        $attendance->break1_start = $request->input('break1_start');
        $attendance->break1_end = $request->input('break1_end');
        $attendance->break2_start = $request->input('break2_start');
        $attendance->break2_end = $request->input('break2_end');
        $attendance->note = $request->input('note');

        // ２　編集不可状態にする
        $attendance->is_editable = false;

        // ３　Attendanceを上書き
        $attendance->save();
        \Log::debug('is_editable after save: '.$attendance->is_editable);

        

        // ４　修正申請を作る
        // ４ー１　承認待ちステータスを取得
        $pendingAttendance = ApproveStatus::where('status', '承認待ち')->first();

        // ４－２　修正申請を登録
        $updatedAttendance = new UpdatedAttendance();
        $updatedAttendance->user_id = $user->id;
        $updatedAttendance->attendance_id = $id;
        $updatedAttendance->approve_status_id =             $pendingAttendance->id ?? 1;
        $updatedAttendance->update_date = now();
        $updatedAttendance->note = $request->input('note');
        $updatedAttendance->save();
        
        return redirect()->route('user.showDetail', ['id' => $id]);
    }

    public function indexUpdated (Request $request)
    {
        $user = Auth::user();
        
        // クエリパラメータ ?page=pending or ?page=updated
        $page = $request->query('page', 'pending'); // デフォルトは「承認待ち」

        $query = UpdatedAttendance::with(['attendance', 'approveStatus'])
        ->where('user_id', $user->id)
        ->orderBy('updated_at', 'desc');

    // ステータスによって絞り込み
        if ($page === 'pending') {
            $query->whereHas('approveStatus', function($q) {
            $q->where('status', '承認待ち');
            });
        } elseif ($page === 'updated') {
            $query->whereHas('approveStatus', function($q) {
            $q->where('status', '承認済み');
            });
        }

        $requests = $query->get();

        return view('user.updated-attendance', compact('requests', 'page'));
    }
}
