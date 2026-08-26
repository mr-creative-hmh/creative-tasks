<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserExcelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_user_import_template(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@creativetasks.io',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/users/template');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_bulk_import_users_from_excel(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@creativetasks.io',
            'role' => 'admin',
        ]);

        $dept = Department::create([
            'name' => 'قسم تطوير البرمجيات والتقنية',
            'work_start_time' => '08:00:00',
            'work_end_time' => '15:30:00',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A4', 'الاسم الكامل');
        $sheet->setCellValue('B4', 'البريد الإلكتروني');
        $sheet->setCellValue('C4', 'المسمى الوظيفي');
        $sheet->setCellValue('D4', 'القسم');
        $sheet->setCellValue('E4', 'الدور والصلاحية');

        $sheet->setCellValue('A5', 'أحمد محمود');
        $sheet->setCellValue('B5', 'ahmed.m@creativetasks.io');
        $sheet->setCellValue('C5', 'مطور برمجيات أول');
        $sheet->setCellValue('D5', 'قسم تطوير البرمجيات والتقنية');
        $sheet->setCellValue('E5', 'head');

        $sheet->setCellValue('A6', 'مريم جاسم');
        $sheet->setCellValue('B6', 'maryam.j@creativetasks.io');
        $sheet->setCellValue('C6', 'مهندسة واجهات');
        $sheet->setCellValue('D6', 'قسم العمليات الميدانية');
        $sheet->setCellValue('E6', 'employee');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile($tempPath, 'users.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($admin)->post('/users/import', [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'ahmed.m@creativetasks.io',
            'name' => 'أحمد محمود',
            'job_title' => 'مطور برمجيات أول',
            'role' => 'head',
            'department_id' => $dept->id,
            'attendance_mode' => 'gps',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'maryam.j@creativetasks.io',
            'name' => 'مريم جاسم',
            'job_title' => 'مهندسة واجهات',
            'role' => 'employee',
            'attendance_mode' => 'gps',
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'قسم العمليات الميدانية',
        ]);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }
}