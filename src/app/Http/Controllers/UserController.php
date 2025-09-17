<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create() 
    {
        return view('user.create_attendance');
    }

    public function store(DetailRequest $request)
    {
        $action = $request->input('action');
        $user = Auth::user;

        switch($action) {
            // 出勤処理
            case 'clock_in' :
                $user->attendances()->create
                ([
                    'attendance_status_id' => 2,
                    'clock_in' => now,
                ]);
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
                    'break1_start'
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

        return redirect()->back;
    }
    
}
