<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_employee_portal(): void
    {
        $dept = Department::create([
            'name' => 'قسم الشؤون الهندسية',
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $employee = User::factory()->create([
            'role' => 'employee',
            'department_id' => $dept->id,
        ]);

        $response = $this->actingAs($employee)->get('/employee/tasks');
        $response->assertStatus(200);
    }

    public function test_employee_can_update_task_progress_slider(): void
    {
        $dept = Department::create(['name' => 'قسم تكنولوجيا المعلومات']);
        $employee = User::factory()->create(['department_id' => $dept->id, 'role' => 'employee']);

        $task = Task::create([
            'department_id' => $dept->id,
            'user_id' => $employee->id,
            'title' => 'فحص وصيانة كابلات الشبكة',
            'progress' => 20,
            'task_type' => 'assigned',
            'status' => 'in_progress',
            'task_date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($employee)->from('/employee/tasks')->patch("/employee/tasks/{$task->id}/progress", [
            'progress' => 100,
        ]);

        $response->assertRedirect('/employee/tasks');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'progress' => 100,
            'status' => 'completed',
        ]);
    }

    public function test_employee_can_quick_add_self_reported_task(): void
    {
        $dept = Department::create(['name' => 'قسم الخدمات']);
        $employee = User::factory()->create(['department_id' => $dept->id, 'role' => 'employee']);

        $response = $this->actingAs($employee)->from('/employee/tasks')->post('/employee/tasks/self-reported', [
            'title' => 'إصلاح عطل كهربائي في مجمع المختبرات',
            'progress' => 100,
        ]);

        $response->assertRedirect('/employee/tasks');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $employee->id,
            'title' => 'إصلاح عطل كهربائي في مجمع المختبرات',
            'task_type' => 'self_reported',
            'progress' => 100,
            'status' => 'completed',
        ]);
    }

    public function test_admin_can_create_new_assigned_task(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create(['name' => 'شعبة المتابعة']);
        $employee = User::factory()->create(['department_id' => $dept->id, 'role' => 'employee']);

        $response = $this->actingAs($admin)->from('/tasks')->post('/tasks', [
            'title' => 'تأمين المؤتمر العلمي السنوي',
            'description' => 'تنظيم دخول الضيوف وتوزيع الباجات التعريفية',
            'department_id' => $dept->id,
            'user_id' => $employee->id,
            'task_type' => 'assigned',
            'progress' => 0,
            'task_date' => Carbon::today()->toDateString(),
        ]);

        $response->assertRedirect('/tasks');
        $this->assertDatabaseHas('tasks', [
            'title' => 'تأمين المؤتمر العلمي السنوي',
            'assigned_by' => $admin->id,
            'user_id' => $employee->id,
        ]);
    }

    public function test_admin_can_delete_task(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create(['name' => 'شعبة المتابعة']);
        $task = Task::create([
            'department_id' => $dept->id,
            'user_id' => $admin->id,
            'title' => 'مهمة سيتم حذفها',
            'progress' => 0,
            'task_type' => 'assigned',
            'status' => 'pending',
            'task_date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->from('/tasks')->delete("/tasks/{$task->id}");
        $response->assertRedirect('/tasks');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
