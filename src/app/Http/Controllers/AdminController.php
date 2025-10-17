<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\UpdatedAttendance;
use App\Models\ApproveStatus;
use App\Models\User;
use App\Http\Requests\DetailRequest;



class AdminController extends Controller
{
    public function index(Request $request)
    {        
        $target_day = $request->input('day', Carbon::now()->format('Y-m-d'));
        
        $target_day_display = Carbon::parse($target_day)
        ->locale('ja')
        ->isoFormat('YYYY年MM月DD日');

        $target_year_month = Carbon::parse($target_day)->format('Y-m');
        $target_day_only = (int) Carbon::parse($target_day)->format('d');

        $attendances = Attendance::with('user')
        ->where('year_month', $target_year_month)
        ->where('day', $target_day_only)
        ->whereHas('user', function($query) {
            $query->where('admin_role', 0)
                ->orWhereNull('admin_role');
        })
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

        $carbonDay = Carbon::parse($target_day);
        $prev_day = $carbonDay->copy()->subDay()->format('Y-m-d');
        $next_day = $carbonDay->copy()->addDay()->format('Y-m-d');

        return view ('admin.index', compact('attendances', 'target_day','target_day_display', 'target_year_month', 'target_day_only','prev_day', 'next_day'));
    }

    public function showDetail($id)
    {
        
        $attendance = Attendance::with('user')->findOrFail($id);

        $isLocked = !(bool)$attendance->is_editable;
        
        $targetDate = Carbon::parse($attendance->year_month. '-'. $attendance->day)
        ->locale('ja')
        ->isoFormat('YYYY年M月D日');

        $clock_in = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
        $clock_out = $attendance->clock_out ? trim(Carbon::parse(trim($attendance->clock_out))->format('H:i')) : '';
        $break1_start = $attendance->break1_start ? Carbon::parse($attendance->break1_start)->format('H:i') : '';
        $break1_end = $attendance->break1_end ? Carbon::parse($attendance->break1_end)->format('H:i') : '';
        $break2_start = $attendance->break2_start ? Carbon::parse($attendance->break2_start)->format('H:i') : '';
        $break2_end = $attendance->break2_end ? Carbon::parse($attendance->break2_end)->format('H:i') : '';
         
        $user = $attendance->user;

        return view('admin.detail', compact('attendance', 'user', 'targetDate', 'clock_in', 'clock_out','break1_start', 'break1_end', 'break2_start', 'break2_end', 'isLocked'));
    }

    public function updateDetail(DetailRequest $request, $id) 
    {
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

        // ４　修正申請を作る
        // ４ー１　承認待ちステータスを取得
        $pendingAttendance = ApproveStatus::where('status', '承認待ち')->first();

        // ４－２　修正申請を登録
        $updatedAttendance = new UpdatedAttendance();
        $updatedAttendance->user_id = $attendance->user_id;
        $updatedAttendance->attendance_id = $id;
        $updatedAttendance->approve_status_id =             $pendingAttendance->id ?? 1;
        $updatedAttendance->update_date = now();
        $updatedAttendance->note = $request->input('note');
        $updatedAttendance->save();
        

        return redirect()->route('admin.showDetail', ['id' => $id]);
    }

    public function indexUpdated (Request $request)
    {
        $user = Auth::user();
        
        // クエリパラメータ ?page=pending or ?page=updated
        $page = $request->query('page', 'pending'); // デフォルトは「承認待ち」

        $query = UpdatedAttendance::with(['attendance', 'approveStatus'])
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

        return view('admin.updated-attendance', compact('requests', 'page'));
    }

    public function indexUsers () 
    {

        $users = User::select('user_name', 'email')->get();

        return view('admin.user-list', compact('users'));
    }

    public function userAttendances (Request $request, $user)
    {

        $user = User::select('user_name', 'id')
            ->where('user_name', $user)
            ->firstOrFail();
        
        $target_month = $request->input('month', Carbon::now()->format('Y-m'));
        $target_month_display = Carbon::createFromFormat('Y-m', $target_month)->format('Y/m');

        // 月次テーブルを作る
        $carbonMonth = Carbon::parse($target_month);
        $startOfMonth = $carbonMonth->copy()->startOfMonth();
        $endOfMonth = $carbonMonth->copy()->endOfMonth();

        // 1か月分の日付を作る
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendancesFromDB = Attendance::where('user_id', $user->id)
            ->where('year_month', $target_month)
            ->get()
            ->keyBy('day');

         // 1か月分の勤怠配列を作成
        $attendances = collect($period)->map(function($date) use ($attendancesFromDB, $target_month) {
            $day = $date->day;


        // DBにある場合はそのまま取得、ない場合は空データ
        $attendance = $attendancesFromDB->has($day) ? $attendancesFromDB[$day] : (object)[
            'id' => 0, 
            'year_month' => $target_month,
            'day' => $day,
            'clock_in' => '',
            'clock_out' => '',
            'break1_start' => null,
            'break1_end' => null,
            'break2_start' => null,
            'break2_end' => null,
        ];

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
            if($totalBreakTime > 0) {
                $hours = floor($totalBreakTime / 60);
                $minutes = $totalBreakTime % 60;
                $attendance->totalBreakTime = sprintf('%02d:%02d', $hours, $minutes);
            }else{
                $attendance->totalBreakTime = '';
            }

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

        $prev_month = $carbonMonth->copy()->subMonth()->format('Y-m');
        $next_month = $carbonMonth->copy()->addMonth()->format('Y-m');
        
        return view('admin.user-attendance-list', compact('user','attendances', 'target_month','target_month_display', 'prev_month', 'next_month'));
    }

}
