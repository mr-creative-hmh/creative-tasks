<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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
            'name' => 'د. حسام الكرخي',
            'email' => 'hussam@almamonuc.edu.iq',
            'password' => 'secret123',
            'role' => 'head',
            'department_id' => $dept->id,
            'is_active' => true,
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'hussam@almamonuc.edu.iq',
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

    public function test_user_can_update_profile_info(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@almamonuc.edu.iq']);

        $response = $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => 'New Name',
            'email' => 'new@almamonuc.edu.iq',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@almamonuc.edu.iq',
        ]);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->from('/profile')->put('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));
    }
}
