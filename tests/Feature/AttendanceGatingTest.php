<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\AttendanceLog;
use App\Models\AttendanceLocationPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_auto_log_gps_attendance_and_records_trail_point(): void
    {
        $dept = Department::create([
            'name' => 'قسم الحاسوب',
            'work_start_time' => '08:00:00',
            'work_end_time' => '16:00:00',
        ]);

        $employee = User::factory()->create([
            'role' => 'employee',
            'department_id' => $dept->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($employee)->postJson('/attendance/log', [
            'latitude' => 33.31524,
            'longitude' => 44.36612,
            'accuracy' => 12.5,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $employee->id,
        ]);

        $this->assertDatabaseHas('attendance_location_points', [
            'user_id' => $employee->id,
            'latitude' => 33.31524,
            'longitude' => 44.36612,
        ]);
    }

    public function test_attendance_checkin_requires_valid_coordinates(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)->postJson('/attendance/log', [
            'latitude' => 'invalid_lat',
            'longitude' => null,
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_view_attendance_map_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/attendance');
        $response->assertStatus(200);
    }

    public function test_admin_can_fetch_live_attendance_radar_points(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);

        AttendanceLog::create([
            'user_id' => $employee->id,
            'latitude' => 33.31524,
            'longitude' => 44.36612,
            'log_date' => Carbon::today()->toDateString(),
            'log_time' => '08:30:00',
            'notes' => 'حضور تجريبي',
        ]);

        $response = $this->actingAs($admin)->getJson('/attendance/live');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'date',
            'points',
            'active_count',
            'total_present',
        ]);
    }

    public function test_admin_can_fetch_user_location_trail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);

        AttendanceLocationPoint::create([
            'user_id' => $employee->id,
            'latitude' => 33.31520,
            'longitude' => 44.36610,
            'accuracy' => 10,
            'recorded_at' => Carbon::now()->subMinutes(30),
        ]);

        AttendanceLocationPoint::create([
            'user_id' => $employee->id,
            'latitude' => 33.31580,
            'longitude' => 44.36670,
            'accuracy' => 8,
            'recorded_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin)->getJson("/attendance/trail/{$employee->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'user' => ['id', 'name', 'job_title', 'department'],
            'date',
            'points' => [
                '*' => ['id', 'latitude', 'longitude', 'accuracy', 'time', 'time_human']
            ],
            'total_points',
        ]);
        $this->assertEquals(2, $response->json('total_points'));
    }

    public function test_employee_cannot_fetch_other_users_trail(): void
    {
        $employee1 = User::factory()->create(['role' => 'employee']);
        $employee2 = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee1)->getJson("/attendance/trail/{$employee2->id}");
        $response->assertStatus(403);
    }

    public function test_admin_can_manually_update_employee_location_on_map(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($admin)->postJson('/attendance/manual-update', [
            'user_id' => $employee->id,
            'latitude' => 33.31680,
            'longitude' => 44.36750,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $employee->id,
            'latitude' => 33.31680,
            'longitude' => 44.36750,
        ]);
    }

    public function test_head_cannot_manually_update_location_since_restricted_to_admin(): void
    {
        $head = User::factory()->create(['role' => 'head']);
        $target = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($head)->postJson('/attendance/manual-update', [
            'user_id' => $target->id,
            'latitude' => 33.31680,
            'longitude' => 44.36750,
        ]);

        $response->assertStatus(403);
    }

    public function test_employee_cannot_manually_update_other_users_location(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $target = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->postJson('/attendance/manual-update', [
            'user_id' => $target->id,
            'latitude' => 33.31680,
            'longitude' => 44.36750,
        ]);

        $response->assertStatus(403);
    }
}