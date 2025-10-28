<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーで attendance_statuses を作成
        $this->seed(\Database\Seeders\AttendanceStatusesTableSeeder::class);

        // テストユーザー作成
        $this->user = User::factory()->create();
    }

    /** @test */
    public function index_all_my_attendances()
    {
        $statusId = \DB::table('attendance_statuses')->first()->id;

        // 当月の勤怠データを作成
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $statusId,
        ]);

        $response = $this->actingAs($this->user)->get(route('user.index'));

        foreach ($attendances as $attendance) {
            // ビューに表示される日付形式でアサーション
            $day = sprintf('%02d', $attendance->day);
            $response->assertSee("10/{$day}");
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function current_month_by_default()
    {
        $statusId = \DB::table('attendance_statuses')->first()->id;

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $statusId,
            'year_month' => now()->format('Y-m'),
            'day' => 5,
        ]);

        $response = $this->actingAs($this->user)->get(route('user.index'));
        $response->assertSee(now()->format('m/05')); // 画面表示に合わせる
    }

    /** @test */
    public function previous_month()
    {
        $statusId = \DB::table('attendance_statuses')->first()->id;

        $prevMonth = now()->subMonth()->format('Y-m');
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $statusId,
            'year_month' => $prevMonth,
            'day' => 10,
        ]);

        $response = $this->actingAs($this->user)->get(route('user.index', ['month' => $prevMonth]));
        $response->assertSee(sprintf('%02d', 10)); // 10日
    }

    /** @test */
    public function next_month()
    {
        $statusId = \DB::table('attendance_statuses')->first()->id;

        $nextMonth = now()->addMonth()->format('Y-m');
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $statusId,
            'year_month' => $nextMonth,
            'day' => 5,
        ]);

        $response = $this->actingAs($this->user)->get(route('user.index', ['month' => $nextMonth]));
        $response->assertSee(sprintf('%02d', 5));
    }

    /** @test */
    public function redirect_to_attendance_detail()
    {
        $statusId = \DB::table('attendance_statuses')->first()->id;

        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'attendance_status_id' => $statusId,
            'year_month' => now()->format('Y-m'),
            'day' => 20,
        ]);

        $response = $this->actingAs($this->user)->get(route('user.index'));
        $response->assertSee(route('user.showDetail', ['id' => $attendance->id]));
    }
}
