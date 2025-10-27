<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Database\Seeders\AttendanceStatusesTableSeeder; 

class AttendanceClockOutTest extends TestCase
{

    use RefreshDatabase;

     public function setUp(): void
    {
        parent::setUp();

        // Seeder を実行して attendance_statuses を準備
        $this->seed(AttendanceStatusesTableSeeder::class);
    }

      public function test_clock_out_button_works()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $onStatus = AttendanceStatus::where('status', '出勤中')->first();
        $clockedOutStatus = AttendanceStatus::where('status', '退勤済')->first();

        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $onStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now->subHours(8)->format('H:i:s'),
            'clock_out' => null,
            'is_editable' => 1,
        ]);

        $response = $this->post(route('user.store'), [
            'action' => 'clock_out',
        ]);

        $response->assertRedirect(route('user.create'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_status_id' => $clockedOutStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
        ]);

        $response = $this->get(route('user.create'));
        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    public function test_clock_out_time_in_attendance_list()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $onStatus = AttendanceStatus::where('status', '出勤中')->first();
        $clockedOutStatus = AttendanceStatus::where('status', '退勤済')->first();

        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $onStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now->subHours(8)->format('H:i:s'),
            'clock_out' => null,
            'is_editable' => 1,
        ]);

        $attendance->update([
            'attendance_status_id' => $clockedOutStatus->id,
            'clock_out' => $now->format('H:i:s'),
        ]);

        $response = $this->get(route('user.create'));
        $response->assertStatus(200);
        $response->assertSeeText($now->format('H:i'));

        Carbon::setTestNow();
    }
}
