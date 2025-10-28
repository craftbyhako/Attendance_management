<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // AttendanceStatus のデータをシーダーで投入
        $this->seed(\Database\Seeders\AttendanceStatusesTableSeeder::class);

        // テストユーザー作成
        $this->user = User::factory()->create([
            'user_name' => 'テストユーザー',
        ]);

        // 勤怠データ作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => 4, // 退勤済
            'year_month' => '2025-10',
            'day' => '15',
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break1_start' => '12:00',
            'break1_end' => '13:00',
        ]);
    }

    public function test_data_on_detail_page()
    {
        $response = $this->actingAs($this->user)
                         ->get(route('user.showDetail', ['id' => $this->attendance->id]));

        // 名前の表示確認
        $response->assertSee($this->user->user_name);

        // 日付が表示されているか（年/月/日形式に合わせる）
        $year = substr($this->attendance->year_month, 0, 4);
        $month = substr($this->attendance->year_month, 5, 2);
        $day = str_pad($this->attendance->day, 2, '0', STR_PAD_LEFT);
        $expectedDate = sprintf('%s年%s月%s日', $year, $month, $day);
        $response->assertSee($expectedDate);

        // 出勤・退勤・休憩１の <input> 値をまとめて確認（順序通り）
        $response->assertSeeInOrder([
            'value="' . $this->attendance->clock_in . '"',
            'value="' . $this->attendance->clock_out . '"',
            'value="' . $this->attendance->break1_start . '"',
            'value="' . $this->attendance->break1_end . '"',
        ], false);
    }
}
