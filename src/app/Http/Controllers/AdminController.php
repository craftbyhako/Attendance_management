<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\UpdatedAttendance;
use App\Models\ApproveStatus;


class AdminController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $target_day = $request->input('day', Carbon::now()->format('Y-m-d'));
        
        $attendances = Attendance::with('user')
        ->where('day', $target_day)
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

        $carbonDay = Carbon::parse($target_day);
        $prev_day = $carbonDay->copy()->subDay()->format('ddd');
        $next_day = $carbonDay->copy()->addDay()->format('ddd');

        return view ('admin.index', compact('attendances', 'target_day', 'prev_day', 'next_day'));
    }
}
