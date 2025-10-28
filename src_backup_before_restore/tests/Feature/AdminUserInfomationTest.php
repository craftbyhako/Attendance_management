<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminUserInfomationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;
    protected $targetMonth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->seed(\Database\Seeders\AttendanceStatusesTableSeeder::class);


        $this->targetMonth = Carbon::create(2025, 10, 1);

        $this->admin = User::factory()->create([
            'user_name' => '管理者',
            'email' => 'admin@example.com',
            'admin_role' => 2,
        ]);

        $this->users = User::factory(3)->create();

        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => 4,
                'year_month' => $this->targetMonth->format('Y-m'),
                'day' => 15,
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'break1_start' => '12:00:00',
                'break1_end' => '13:00:00',
                'note' => null,
                'is_editable' => 1,
            ]);
        }

    }

    public function test_admin_users_name_and_email()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->user_name);
            $response->assertSee($user->email);
        }
    }

    public function test_admin_user_attendance_list()
    {
        $targetUser = $this->users->first();

        $response = $this->actingAs($this->admin)
            ->get('/admin/users/' . $targetUser->user_name . '/attendances?month=' . $this->targetMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee($targetUser->user_name);
    }

     public function test_admin_user_previous_month_attedance()
     {
        $prevMonth = $this->targetMonth->copy()->subMonth();
        $targetUser = $this->users->first();

        Attendance::factory()->create([
            'user_id' => $targetUser->id,
            'attendance_status_id' => 4,
            'year_month' => $prevMonth->format('Y-m'),
            'day' => 10,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'break1_start' => '12:00:00',
            'break1_end' => '13:00:00',
            'note' => null,
            'is_editable' => 1,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/users/' . $targetUser->user_name . '/attendances?month=' . $prevMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');

     }   

     public function test_admin_user_next_month_attendance()
     {
        $nextMonth = $this->targetMonth->copy()->addMonth();
        $targetUser = $this->users->first();

        Attendance::factory()->create([
            'user_id' => $targetUser->id,
            'attendance_status_id' => 4,
            'year_month' => $nextMonth->format('Y-m'),
            'day' => 5,
            'clock_in' => '8:00:00',
            'clock_out' => '16:00:00',
            'break1_start' => '12:00:00',
            'break1_end' => '13:00:00',
            'note' => null,
            'is_editable' => 1,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/users/' . $targetUser->user_name . '/attendances?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee('08:00');
        $response->assertSee('16:00');

     }

     public function test_admin_users_attendance_detail()
     {
        $targetUser = $this->users->first(); 
        $attendance = $targetUser->attendances()->first();

        $response = $this->actingAs($this->admin)->get('/admin/attendances/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee($targetUser->user_name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
     }

}
