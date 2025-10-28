<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;


class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $clockIn = $this->faker->dateTimeBetween('today 07:00', 'today 11:30')->format('H:i');
    
        $clockOut = $this->faker->dateTimeBetween('today 15:00', 'today 19:30')->format('H:i');

       return [
            'user_id' => 1,
            'attendance_status_id' => 4,
            'year_month' => '2025-09',
            'day' => $this->faker->numberBetween(1, 30),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break1_start' => '12:00',
            'break1_end' => '13:00',
            'break2_start' => null,
            'break2_end' => null,
        ];
    }
}
