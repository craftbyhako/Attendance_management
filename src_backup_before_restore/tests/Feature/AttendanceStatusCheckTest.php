<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;

class AttendanceStatusCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_status_is_off()
    {
        // Carbonで日時固定（必要に応じて）
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // 勤務外ステータスを作成（attendance_statusesテーブル）
        $offStatus = AttendanceStatus::create([
            'status' => '勤務外', // カラム名に合わせて変更
        ]);

        // ユーザーを作成してログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // attendancesテーブルにレコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $offStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now,
            'clock_out' => null,
            'break1_start' => null,
            'break1_end' => null,
            'break2_start' => null,
            'break2_end' => null,
            'note' => null,
            'is_editable' => 1,
        ]);

        // 勤怠打刻画面にアクセス
        $response = $this->get(route('user.create'));
        $response->assertStatus(200);

        // 画面上に「勤務外」と表示されていることを確認
        $response->assertSee('勤務外');

        // Carbonの固定を解除
        Carbon::setTestNow();
    }

    public function test_attendance_status_is_on()
    {
        // 現在時刻を固定
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // ステータス作成
        $onStatus = AttendanceStatus::create(['status' => '出勤中']);

        // 出勤中のユーザー作成
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今日の勤怠データ（出勤中）を作成
        Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $onStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now, // 出勤しているので null にしない
            'clock_out' => null,
            'is_editable' => 1,
        ]);

        // 勤怠打刻画面を開く
        $response = $this->get(route('user.create'));

        // ステータスが正しく表示されることを確認
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

     /** 休憩中の場合 */
    public function test_attendance_status_is_on_break()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $breakStatus = AttendanceStatus::create(['status' => '休憩中']);

        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $breakStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now->copy()->subHours(2),
            'break1_start' => $now->copy()->subMinutes(30),
            'is_editable' => 1,
        ]);

        $response = $this->get(route('user.create'));
        $response->assertStatus(200);
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    /** 退勤済の場合 */
    public function test_attendance_status_is_clocked_out()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

        $outStatus = AttendanceStatus::create(['status' => '退勤済']);

        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $outStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now->copy()->subHours(8),
            'clock_out' => $now,
            'is_editable' => 1,
        ]);

        $response = $this->get(route('user.create'));
        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }
}
