<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_admin_can_authenticate_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@creativetasks.io',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@creativetasks.io',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_employee_can_authenticate_and_redirects_to_employee_portal(): void
    {
        $user = User::factory()->create([
            'email' => 'staff@creativetasks.io',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staff@creativetasks.io',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/employee/tasks');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@creativetasks.io',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'admin@creativetasks.io',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
