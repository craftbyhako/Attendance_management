<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\UpdatedAttendance;
use App\Models\ApproveStatus;
use App\Models\User;


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

    public function showDetail($id) {
        
        $attendance = Attendance::with('user')->findOrFail($id);

        $isLocked = $attendance->is_editable === false;
        
        $targetDate = Carbon::parse($attendance->year_month. '-'. $attendance->day)
        ->locale('ja')
        ->isoFormat('YYYY年M月D日');

        $clock_in = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
        $clock_out = $attendance->clock_out ? trim(Carbon::parse(trim($attendance->clock_out))->format('H:i')) : '';
        $break1_start = $attendance->break1_start ? Carbon::parse($attendance->break1_start)->format('H:i') : '';
        $break1_end = $attendance->break1_end ? Carbon::parse($attendance->break1_end)->format('H:i') : '';
        $break2_start = $attendance->break2_start ? Carbon::parse($attendance->break2_start)->format('H:i') : '';
        $break2_end = $attendance->break2_end ? Carbon::parse($attendance->break2_end)->format('H:i') : '';
         
        return view('admin.detail', compact('attendance','targetDate', 'clock_in', 'clock_out','break1_start', 'break1_end', 'break2_start', 'break2_end', 'isLocked'));
    }
}
