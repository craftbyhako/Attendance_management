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
        $numberOfDays = 20;
        
        
        $daysSep = collect(range(1, 30))
            ->random($numberOfDays);
        foreach ($daysSep as $day) {
            Attendance::factory()->state([
                    'user_id' => $userId,
                    'year_month' => '2025-09',
                    'day' => $day,
            ])
            ->create();
        }

        $daysOct = collect(range(1, 31))
            ->random($numberOfDays);
        foreach ($daysOct as $day) {
        Attendance::factory()->state([
                'user_id' => $userId,
                'year_month' => '2025-10',
                'day' => $day,
        ])
        ->create();
        }

        $daysNov = collect(range(1, 30))
            ->random($numberOfDays);
        foreach ($daysNov as $day) {
        Attendance::factory()->state([
                'user_id' => $userId,
                'year_month' => '2025-11',
                'day' => $day,
        ])
        ->create();
        }


    }
}
