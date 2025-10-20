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
            'clock_in' => '00:00:00',
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
        // Carbonで日時固定（必要に応じて）
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // 勤務外ステータスを作成（attendance_statusesテーブル）
        $onStatus = AttendanceStatus::create([
            'status' => '出勤中', // カラム名に合わせて変更
        ]);

        // ユーザーを作成してログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // attendancesテーブルにレコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $onStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now->format('H:i:s'),
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
        $response->assertSeeText('出勤中');

        // Carbonの固定を解除
        Carbon::setTestNow();
    }
}
