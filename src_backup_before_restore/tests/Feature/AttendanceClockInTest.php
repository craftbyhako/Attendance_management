<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_button_works()
    {
       // 現在時刻を固定
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // 「勤務外」「勤務中」ステータスを準備
        $offStatus = AttendanceStatus::create(['status' => '勤務外']);
        $onStatus = AttendanceStatus::create(['status' => '勤務中']);

        // 勤務外のユーザーを作成してログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今日の勤怠データを作らない（テスト対象は「日付が変わった場合に出勤ボタンで作成される」）

        // ① 出勤ボタンが表示されていることを確認
        $response = $this->get(route('user.create'));
        $response->assertStatus(200);
        $response->assertSee('出 勤');

        // ② 出勤ボタン押下（POSTリクエスト）
        $response = $this->post(route('user.store'), [
            'action' => 'clock_in', // ←必須
        ]);
        $response->assertRedirect(route('user.create'));

        // DBでステータスが「勤務中」となっていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_status_id' => $onStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
        ]);

        // ③ 再度画面を開いて「勤務中」が表示されていることを確認
        $response = $this->get(route('user.create'));
        $response->assertStatus(200);
        $response->assertSeeText('出勤中');

        // Carbonの固定解除
        Carbon::setTestNow();
    }

    public function test_clock_in_button_can_be_clicked_only_once()
    {
        // 現在時刻を固定
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // ステータス準備
        $offStatus = AttendanceStatus::create(['status' => '勤務外']);
        $onStatus = AttendanceStatus::create(['status' => '出勤中']);
        $clockedOutStatus = AttendanceStatus::create(['status' => '退勤済']);

        // ユーザー作成・ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今日の勤怠データ（退勤済）を作成
        Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $clockedOutStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now->subHours(8)->format('H:i:s'),
            'clock_out' => $now->format('H:i:s'),
            'is_editable' => 1,
        ]);

        // 勤怠打刻画面にアクセス
        $response = $this->get(route('user.create'));
        $response->assertStatus(200);

        // 出勤ボタンが表示されないことを確認
        $response->assertDontSeeText('出勤');

        Carbon::setTestNow(); // 時刻固定解除
    }

    public function test_clock_in_time_in_attendance_list()
    {
        // 現在時刻を固定
        $now = Carbon::now();
        Carbon::setTestNow($now);

        // ステータス準備
        $offStatus = AttendanceStatus::create(['status' => '勤務外']);
        $onStatus = AttendanceStatus::create(['status' => '出勤中']);

        // ユーザー作成・ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今日の勤怠データを作成（出勤処理）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_status_id' => $onStatus->id,
            'year_month' => $now->format('Y-m'),
            'day' => $now->format('d'),
            'clock_in' => $now->format('H:i:s'),
            'clock_out' => null,
            'is_editable' => 1,
        ]);

        // 勤怠一覧画面にアクセス
        $response = $this->get(route('user.index')); // 実際のルート名に置き換え
        $response->assertStatus(200);

        // 勤怠一覧画面に出勤時刻が表示されていることを確認
        $response->assertSeeText($now->format('H:i')); // 時間だけでも確認可能

        Carbon::setTestNow(); // 時刻固定解除
    }


}
