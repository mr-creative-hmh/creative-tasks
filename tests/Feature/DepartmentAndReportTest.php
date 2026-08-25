<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DepartmentAndReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_new_department_with_shift(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->from('/departments')->post('/departments', [
            'name' => 'كلية طب الأسنان',
            'work_start_time' => '08:30',
            'work_end_time' => '15:30',
        ]);

        $response->assertRedirect('/departments');
        $this->assertDatabaseHas('departments', [
            'name' => 'كلية طب الأسنان',
        ]);
    }

    public function test_admin_can_view_reports_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/reports');
        $response->assertStatus(200);
    }

    public function test_admin_can_export_pdf_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create(['name' => 'قسم تكنولوجيا المعلومات']);
        Task::create([
            'department_id' => $dept->id,
            'user_id' => $admin->id,
            'title' => 'تقرير أداء تجريبي',
            'progress' => 100,
            'task_type' => 'assigned',
            'status' => 'completed',
            'task_date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get('/reports/pdf?date_from=2026-01-01&date_to=2026-12-31');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_export_excel_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create(['name' => 'قسم تكنولوجيا المعلومات']);
        Task::create([
            'department_id' => $dept->id,
            'user_id' => $admin->id,
            'title' => 'تقرير إكسل تجريبي',
            'progress' => 80,
            'task_type' => 'assigned',
            'status' => 'in_progress',
            'task_date' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get('/reports/excel?date_from=2026-01-01&date_to=2026-12-31');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_employee_cannot_access_reports(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get('/reports');
        $response->assertRedirect('/login');
    }
}
