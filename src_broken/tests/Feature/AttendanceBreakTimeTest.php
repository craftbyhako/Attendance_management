<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;

class AttendanceBreakTimeTest extends TestCase
{
    use RefreshDatabase;

   public function test_break_button_works()
    {
    Carbon::setTestNow(Carbon::create(2025, 10, 21, 12, 0));

    \DB::table('attendance_statuses')->insertOrIgnore([
        ['id' => 1, 'status' => '勤務外'],
        ['id' => 2, 'status' => '出勤中'],
        ['id' => 3, 'status' => '休憩中'],
        ['id' => 4, 'status' => '退勤済'],
    ]);

    $user = User::factory()->create();

    // 出勤中の勤怠レコード作成（dateではなく year_month + day）
    $attendance = Attendance::factory()->create([
        'user_id' => $user->id,
        'attendance_status_id' => 2,
        'year_month' => '2025-10',
        'day' => 21,
        'clock_in' => Carbon::now()->subHours(2),
        'clock_out' => null,
    ]);

    $this->actingAs($user);

    // 出勤中の画面を開く
    $response = $this->get(route('user.create'));
    $response->assertStatus(200);
    $response->assertSee('休 憩 入');

    // 「休憩入」ボタン押下
    $response = $this->post(route('user.store'), [
        'action' => 'break_in',
    ]);

    // 処理後、再度確認
    $response = $this->get(route('user.create'));
    $response->assertStatus(200);
    $response->assertSee('休憩中');

    Carbon::setTestNow();
    }
}
