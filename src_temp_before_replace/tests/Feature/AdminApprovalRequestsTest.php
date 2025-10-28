<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceStatus;
use Carbon\Carbon;


class AdminApprovalRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;
    protected $targetMonth;

    protected function setUp(): void
    {
        parent::setUp();

        // マイグレーション＆マスターデータ投入
        $this->artisan('migrate:fresh');
        $this->seed(\Database\Seeders\AttendanceStatusesTableSeeder::class);

        DB::table('approve_statuses')->insert([
                ['id' => 1, 'status' => 'pending'],
                ['id' => 2, 'status' => 'approved'],
            ]);

        $this->targetMonth = Carbon::create(2025, 10, 1);

        // 管理者ユーザー作成
        $this->admin = User::factory()->create([
            'user_name' => '管理者',
            'email' => 'admin@example.com',
            'admin_role' => 2,
        ]);

        // 一般ユーザー作成
        $this->users = User::factory(2)->create();

        foreach ($this->users as $user) {
            // 勤怠データ作成
            $attendanceId = Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => 2, // 出勤中
                'year_month' => $this->targetMonth->format('Y-m'),
                'day' => 15,
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'is_editable' => 1,
            ])->id;

            // 修正申請データ作成（承認待ち）
            DB::table('updated_attendances')->insert([
                'user_id' => $user->id,
                'attendance_id' => $attendanceId,
                'approve_status_id' => 1, // 承認待ち
                'update_date' => $this->targetMonth->format('Y-m-15'),
                'note' => '修正申請: 出勤時間を10:00に変更',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 修正申請データ作成（承認済み）
            DB::table('updated_attendances')->insert([
                'user_id' => $user->id,
                'attendance_id' => $attendanceId,
                'approve_status_id' => 2, // 承認済み
                'update_date' => $this->targetMonth->format('Y-m-15'),
                'note' => '修正申請: 退勤時間を19:00に変更',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }
    }

    /** @test */
    public function test_admin_pending_approval_requests()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/requests?page=pending');

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $pending = DB::table('updated_attendances')
                ->where('user_id', $user->id)
                ->where('approve_status_id', 1)
                ->first();

            $response->assertSee($user->user_name);
            $response->assertSee($pending->note);
        }
    }

    /** @test */
    public function test_admin_approved_approval_requests()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/requests?page=approved');

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $approved = DB::table('updated_attendances')
                ->where('user_id', $user->id)
                ->where('approve_status_id', 2)
                ->first();

            $response->assertSee($user->user_name);
            $response->assertSee('19:00');
        }
    }

    /** @test */
    public function test_admin_updated_attendance_detail()
    {
        $targetUser = $this->users->first();
        $pending = DB::table('updated_attendances')
            ->where('user_id', $targetUser->id)
            ->where('approve_status_id', 1)
            ->first();

        $response = $this->actingAs($this->admin)
            ->get('/admin/requests/' . $pending->id);

        $response->assertStatus(200);
        $response->assertSee($targetUser->user_name);
        $response->assertSee($pending->note); // 修正申請内容
    }

    /** @test */
    public function test_admin_approve_a_request()
    {
        $targetUser = $this->users->first();
        $pending = DB::table('updated_attendances')
            ->where('user_id', $targetUser->id)
            ->where('approve_status_id', 1)
            ->first();

        $response = $this->actingAs($this->admin)
            ->patch('/admin/requests/' . $pending->id);

        $response->assertRedirect('/admin/requests/' . $pending->id);

        // 承認済みに更新されているか確認
        $this->assertDatabaseHas('updated_attendances', [
            'id' => $pending->id,
            'approve_status_id' => 2,
        ]);

        // 元の勤怠データも更新されているか確認
        $attendance = Attendance::find($pending->attendance_id);
        $this->assertEquals('10:00:00', $attendance->clock_in);
    }
}
