<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceStatus; 
use Illuminate\Support\Facades\DB;


class AttendanceStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $statuses = [
            '勤務外',
            '出勤中',
            '休憩中',
            '退勤済',
        ];

        foreach($statuses as $status) {
            DB::table('attendance_statuses')->insert([
                'status' => $status,
            ]);
        }
    }
}
