<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_management_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create(['name' => 'قسم الحاسوب']);

        $response = $this->actingAs($admin)->from('/users')->post('/users', [
            'name' => 'د. حسام علي',
            'email' => 'hussam@creativetasks.io',
            'password' => 'secret123',
            'role' => 'head',
            'department_id' => $dept->id,
            'is_active' => true,
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'hussam@creativetasks.io',
            'role' => 'head',
        ]);
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->from('/users')->post("/users/{$target->id}/toggle-status");
        $response->assertRedirect('/users');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_active' => false,
        ]);
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
    }

    public function test_admin_can_update_profile_info(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Old Admin', 'email' => 'admin.old@creativetasks.io']);

        $response = $this->actingAs($admin)->from('/profile')->patch('/profile', [
            'name' => 'New Admin Name',
            'email' => 'admin.new@creativetasks.io',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'New Admin Name',
            'email' => 'admin.new@creativetasks.io',
        ]);
    }

    public function test_employee_cannot_update_profile_info(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'name' => 'Original Employee', 'email' => 'emp@creativetasks.io']);

        $response = $this->actingAs($employee)->from('/profile')->patch('/profile', [
            'name' => 'Hacked Employee Name',
            'email' => 'hacked@creativetasks.io',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Original Employee',
            'email' => 'emp@creativetasks.io',
        ]);
    }

    public function test_admin_can_change_own_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($admin)->from('/profile')->put('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertTrue(Hash::check('new-secret-123', $admin->fresh()->password));
    }

    public function test_employee_cannot_change_password_directly(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'password' => Hash::make('original-password'),
        ]);

        $response = $this->actingAs($employee)->from('/profile')->put('/profile/password', [
            'current_password' => 'original-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertTrue(Hash::check('original-password', $employee->fresh()->password));
    }

    public function test_admin_can_reset_user_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($admin)->from('/users')->put("/users/{$targetUser->id}/reset-password", [
            'password' => 'new-reset-password-2026',
        ]);

        $response->assertRedirect('/users');
        $this->assertTrue(Hash::check('new-reset-password-2026', $targetUser->fresh()->password));
    }

    public function test_admin_can_set_fixed_location_settings_for_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee', 'attendance_mode' => 'gps']);

        $response = $this->actingAs($admin)->from('/users')->put("/users/{$employee->id}/location-settings", [
            'attendance_mode' => 'fixed',
            'fixed_latitude' => 33.31524,
            'fixed_longitude' => 44.36612,
            'fixed_location_name' => 'مختبرات تكنولوجيا المعلومات - مبنى 3',
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'attendance_mode' => 'fixed',
            'fixed_location_name' => 'مختبرات تكنولوجيا المعلومات - مبنى 3',
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $employee->id,
            'log_date' => Carbon::today()->toDateString(),
            'latitude' => 33.31524,
            'longitude' => 44.36612,
        ]);
    }
}
