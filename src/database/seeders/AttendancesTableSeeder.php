<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;


class AttendancesTableSeeder extends Seeder
{
    public function run()
    {
        $userId = 1;

        Attendance::factory()->count(10)
            ->state([
                'user_id' => $userId,
                'year_month' => '2025-09',
            ])
            ->create();

        Attendance::factory()->count(20)
            ->state([
                'user_id' => $userId,
                'year_month' => '2025-10',
            ])
            ->create();

        Attendance::factory()->count(10)
            ->state([
                'user_id' => $userId,
                'year_month' => '2025-11',
            ])
            ->create();

        // $param = [
        //     'user_id' => 1,
        //     'attendance_status_id' => 2,
        //     'year_month' => '2025-09',
        //     'day' => 10,
        //     'clock_in' => '9:00',
        //     'clock_out' => '17:00',
        //     'break1_start' => '12:00',
        //     'break1_end' => '13:00',
        //     'break2_start' => null,
        //     'break2_end' => null,
        // ];
        // DB::table('attendances')->insert($param);

        // $param = [
        //     'user_id' => 1,
        //     'attendance_status_id' => 2,
        //     'year_month' => '2025-09',
        //     'day' => 10,
        //     'clock_in' => '9:00',
        //     'clock_out' => '17:00',
        //     'break1_start' => null,
        //     'break1_end' => null,
        //     'break2_start' => null,
        //     'break2_end' => null,
        // ];
        // DB::table('attendances')->insert($param);

    }
}
