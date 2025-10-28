<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Database\Seeders\AttendanceStatusesTableSeeder;

class AdminAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;
    protected $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->seed(\Database\Seeders\AttendanceStatusesTableSeeder::class);

        $this->today = Carbon::today();

        $this->admin = User::factory()->create([
            'admin_role' => 2,
            'user_name' => '管理者',
        ]);

        $this->users = User::factory(3)->create();

        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => 4,
                'year_month' => $this->today->format('Y-m'),
                'day' => (int) $this->today->format('d'),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'break1_start' => '12:00:00',
                'break1_end' => '13:00:00',
                'note' => null,
                'is_editable' => 1,

            ]);
        }

    }


    public function test_admin_view_all_user_attendances()
    
    {
        $response = $this->actingAs($this->admin)->get('/admin/attendances');

        $response->assertStatus(200);

        // 全ユーザーの勤怠情報が表示されている
        foreach ($this->users as $user) {
            $response->assertSee($user->user_name);
            $response->assertSee('09:00');
            $response->assertSee('18:00');
        }
    }


    public function test_current_date_by_default()
    {
        $response = $this->actingAs($this->admin)->get('/admin/attendances');

        $response->assertStatus(200);
        $response->assertSee($this->today->format('Y年m月d日'));
    }


    public function test_previous_day_attendances()
    {
        $previousDay = $this->today->copy()->subDay();

        // 前日の勤怠を追加
        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => 4,
                'year_month' => $previousDay->format('Y-m'),
                'day' => (int) $previousDay->format('d'),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'break1_start' => '12:00:00',
                'break1_end' => '13:00:00',
                'note' => null,
                'is_editable' => 1,
            ]);
        }

            
            $response = $this->actingAs($this->admin)
                ->get('/admin/attendances?day=' . $previousDay->toDateString());
            $response->assertStatus(200);
            $response->assertSee($previousDay->format('Y年m月d日'));
            $response->assertSee('9:00');
            $response->assertSee('18:00');
    }


    public function test_next_day_attendances()
    {
        $nextDay = $this->today->copy()->addDay();

        // 前日の勤怠を追加
        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => 4,
                'year_month' =>  $nextDay->format('Y-m'),
                'day' => (int) $nextDay->format('d'),
                'clock_in' => '09:30:00',
                'clock_out' => '18:30:00',
                'break1_start' => '12:00:00',
                'break1_end' => '13:00:00',
                'note' => null,
                'is_editable' => 1,
            ]);
        }

            $response = $this->actingAs($this->admin)
                ->get('/admin/attendances?day=' . $nextDay->toDateString());

            $response->assertStatus(200);
            $response->assertSee($nextDay->format('Y年m月d日'));
            $response->assertSee('09:30');
            $response->assertSee('18:30');
    }

    			
}
