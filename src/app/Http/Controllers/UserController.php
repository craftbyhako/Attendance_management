<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Requests\DetailRequest;
use Illuminate\Support\Facades\Auth;

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

        // 「出勤前」にする　※今朝、退勤済でリロードしたとき
        if (!$attendance) 
        {
            $attendance = new Attendance ([
                'attendance_status_id' => 1, 
                'year_month' => $todayYearMonth,
                'day' => $todayDay,
            ]);
        }

        return view('user.create-attendance', compact('attendance'));
    }

    public function store(DetailRequest $request)
    {
        // dd($request->all(), Auth::id());
        
        $action = $request->input('action');
        $user = Auth::user();


        switch($action) {
            // 出勤処理
            case 'clock_in' :
                $todayAttendance = $user->attendances()
                    ->where('year_month', now()->format('Y-m'))
                    ->where('day', now()->day)
                    ->first();

                if(!$todayAttendance) {
                    $user->attendances()->create
                    ([
                        'attendance_status_id' => 2,
                        'clock_in' => now(),
                        'year_month' => now()->format('Y-m'),
                        'day' => now()->day,
                        'clock_out' => null,
                        'break1_start' =>null,
                        'break1_end' =>null,
                        'break2_start' =>null,
                        'break2_end' =>null,
                    ]);
                }
                break;

            // 退勤処理
            case 'clock_out' :
                $user->attendances()->latest()->first()->update
                ([
                    'attendance_status_id' => 4,
                    'clock_out' => now(),
                ]);
                break;

            // 休憩１開始
            case 'break1_start' :
                $user->attendances()->latest()->first()->update
                ([
                    'attendance_status_id' => 3,
                    'break1_start' => now(),
                ]);
                break;

            // 休憩１終了
            case 'break1_end' :
                $user->attendances()->latest()->first()->update
                ([
                    'attendance_status_id' => 2,
                    'break1_end' => now(),
                ]);
                break;
        }

        return redirect()->back();
    }
    
}
