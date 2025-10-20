<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class GetDatetimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_page_datetime_matches_now()
    {
        $now = Carbon::now();
        Carbon::setTestNow($now);

         // 認証済みユーザーを作成してログイン
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('user.create'));
        $response->assertStatus(200);

        // 画面上に表示される日時が現在日時と一致することを確認
        $response->assertSee($now->format('Y年m月d日')); // 日付部分
        $response->assertSee($now->format('H:i'));       // 時間部分

        // Carbonの固定を解除
        Carbon::setTestNow();
    }
}
