<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\UpdatedAttendance;
use App\Models\ApproveStatus;
use App\Http\Requests\DetailRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        $target_month_display = Carbon::createFromFormat('Y-m', $target_month)->format('Y/m');

        // 月初と月末
        $carbonMonth = Carbon::parse($target_month);
        $startOfMonth = $carbonMonth->copy()->startOfMonth();
        $endOfMonth = $carbonMonth->copy()->endOfMonth();

        // 1か月分の日付
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        // DBから取得
        $attendancesFromDB = Attendance::where('user_id', $user->id)
            ->where('year_month', $target_month)
            ->get()
            ->keyBy('day');

        // 1か月分の勤怠を作成（空日も含む）
        $attendances = collect($period)->map(function($date) use ($attendancesFromDB, $target_month) {
            $day = $date->day;

            $attendance = $attendancesFromDB->has($day) ? $attendancesFromDB[$day] : (object)[
                'id' => 0, // 空日の場合
                'year_month' => $target_month,
                'day' => $day,
                'clock_in' => '',
                'clock_out' => '',
                'break1_start' => null,
                'break1_end' => null,
                'break2_start' => null,
                'break2_end' => null,
            ];

            // 出勤・退勤
            $attendance->clock_in = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
            $attendance->clock_out = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';

            // 休憩時間
            $totalBreakTime = 0;
            if ($attendance->break1_start && $attendance->break1_end) {
                $totalBreakTime += Carbon::parse($attendance->break1_end)->diffInMinutes(Carbon::parse($attendance->break1_start));
            }
            if ($attendance->break2_start && $attendance->break2_end) {
                $totalBreakTime += Carbon::parse($attendance->break2_end)->diffInMinutes(Carbon::parse($attendance->break2_start));
            }

            $attendance->totalBreakTime = $totalBreakTime > 0
                ? sprintf('%02d:%02d', floor($totalBreakTime / 60), $totalBreakTime % 60)
                : '';

            // 労働時間
            if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in)) - $totalBreakTime;
                if ($workMinutes < 0) $workMinutes = 0;
                $attendance->totalWorkingTime = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
            } else {
                $attendance->totalWorkingTime = '';
            }

            return $attendance;
        });

        $prev_month = $carbonMonth->copy()->subMonth()->format('Y-m');
        $next_month = $carbonMonth->copy()->addMonth()->format('Y-m');

        return view('user.index', compact('attendances', 'target_month', 'prev_month', 'next_month', 'target_month_display'));
    }

    public function showDetail($id)
    {
        $user = Auth::user();
        
        $attendance = Attendance::with('updatedAttendances')->findOrFail($id);

        $latestUpdated = $attendance->updatedAttendances()
        ->latest('update_date')
        ->first();

        $isLocked = $latestUpdated && in_array($latestUpdated->approve_status_id, [1, 2]);   // 承認待ち,承認済み
        $isApproved = $latestUpdated && $latestUpdated->approve_status_id == 2; // 承認済み

        $targetDate = Carbon::parse($attendance->year_month. '-'. $attendance->day)
        ->locale('ja')
        ->isoFormat('YYYY年M月D日');

        $clock_in = $attendance->clock_in ? trim(Carbon::parse($attendance->clock_in)->format('H:i')) : '';
        $clock_out = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';
        $break1_start = $attendance->break1_start ? trim(Carbon::parse($attendance->break1_start)->format('H:i')) : '';
        $break1_end = $attendance->break1_end ? trim(Carbon::parse($attendance->break1_end)->format('H:i')) : '';
        $break2_start = $attendance->break2_start ? trim(Carbon::parse($attendance->break2_start)->format('H:i')) : '';
        $break2_end = $attendance->break2_end ? trim(Carbon::parse($attendance->break2_end)->format('H:i')) : '';

        return view('user.detail', compact('attendance','user','targetDate', 'clock_in', 'clock_out','break1_start', 'break1_end', 'break2_start', 'break2_end', 'isLocked', 'isApproved'));
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
        $updatedAttendance = UpdatedAttendance::firstOrNew([
            'user_id' => $user->id,
            'attendance_id' => $id,
        ]);
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

        $query = UpdatedAttendance::with(['attendance', 'approveStatus', 'user'])
        ->where('user_id', $user->id)
        ->orderBy('updated_at', 'desc');

        // ステータスによって絞り込み
        if ($page === 'pending') {
            $query->where('approve_status_id', 1); 
        } elseif ($page === 'updated') {
            $query->where('approve_status_id', 2);
        }

        $requests = $query->get();

        return view('user.updated-attendance', compact('requests', 'page'));
    }
}
