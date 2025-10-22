<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\UpdatedAttendance;
use App\Models\ApproveStatus;


class UserAttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $attendance;
    protected $pending;
    protected $updated;

    protected function setUp(): void
    {
        parent::setUp();

        // 勤怠ステータスをSeederで投入
        $this->seed(\Database\Seeders\AttendanceStatusesTableSeeder::class);

         // 承認ステータスを作成
        $this->pending = ApproveStatus::factory()->create(['status' => '承認待ち']);
        $this->updated = ApproveStatus::factory()->create(['status' => '承認済み']);


        // ユーザー作成
        $this->user = User::factory()->create(['user_name' => 'テストユーザー']);
        $this->admin = User::factory()->create(['user_name' => '管理者', 'is_admin' => true]);

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
            'note' => '初期備考',
        ]);
    }

    /** @test */
    public function test_clock_in_after_clock_out_shows_error()
    {
        $response = $this->actingAs($this->user)->patch(
            route('user.updateDetail', ['id' => $this->attendance->id]),
            [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'break1_start' => '12:00',
                'break1_end' => '13:00',
                'note' => 'テスト備考'
            ]
        );

        $response->assertSessionHasErrors(['clock_in']);
    }

    /** @test */
    public function test_break_start_after_clock_out_shows_error()
    {
        $response = $this->actingAs($this->user)->patch(
            route('user.updateDetail', ['id' => $this->attendance->id]),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break1_start' => '19:00',
                'break1_end' => '20:00',
                'note' => 'テスト備考'
            ]
        );

        $response->assertSessionHasErrors(['break1_start']);
    }

    /** @test */
    public function test_break_end_after_clock_out_shows_error()
    {
        $response = $this->actingAs($this->user)->patch(
            route('user.updateDetail', ['id' => $this->attendance->id]),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break1_start' => '12:00',
                'break1_end' => '19:00',
                'note' => 'テスト備考'
            ]
        );

        $response->assertSessionHasErrors(['break1_end']);
    }

    /** @test */
    public function test_note_is_required_shows_error()
    {
        $response = $this->actingAs($this->user)->patch(
            route('user.updateDetail', ['id' => $this->attendance->id]),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break1_start' => '12:00',
                'break1_end' => '13:00',
                'note' => ''
            ]
        );

        $response->assertSessionHasErrors(['note']);
    }

    /** @test */
    public function test_updated_attendance_is_created()
    {
        $response = $this->actingAs($this->user)->patch(
            route('user.updateDetail', ['id' => $this->attendance->id]),
            [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'break1_start' => '12:30',
                'break1_end' => '13:30',
                'note' => '修正備考'
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('user.showDetail', ['id' => $this->attendance->id]));

        $this->assertDatabaseHas('attendance_correction_requests', [
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'note' => '修正備考',
        ]);
    }
}

