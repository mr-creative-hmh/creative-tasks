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
            'email' => 'admin@almamonuc.edu.iq',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/users/template');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_bulk_import_users_from_excel(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@almamonuc.edu.iq',
            'role' => 'admin',
        ]);

        $dept = Department::create([
            'name' => 'قسم علوم الحاسوب',
            'work_start_time' => '08:00:00',
            'work_end_time' => '15:30:00',
        ]);

        // Create a temporary Excel file for testing
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A4', 'الاسم الكامل');
        $sheet->setCellValue('B4', 'البريد الإلكتروني');
        $sheet->setCellValue('C4', 'المسمى الوظيفي');
        $sheet->setCellValue('D4', 'الكلية أو القسم');
        $sheet->setCellValue('E4', 'الدور');
        $sheet->setCellValue('F4', 'كلمة المرور');

        $sheet->setCellValue('A5', 'أحمد محمود');
        $sheet->setCellValue('B5', 'ahmed.m@almamonuc.edu.iq');
        $sheet->setCellValue('C5', 'مدرس دكتور');
        $sheet->setCellValue('D5', 'قسم علوم الحاسوب');
        $sheet->setCellValue('E5', 'head');
        $sheet->setCellValue('F5', 'secret123');

        $sheet->setCellValue('A6', 'مريم جاسم');
        $sheet->setCellValue('B6', 'maryam.j@almamonuc.edu.iq');
        $sheet->setCellValue('C6', 'مهندسة برمجيات');
        $sheet->setCellValue('D6', 'كلية جديدة');
        $sheet->setCellValue('E6', 'employee');
        $sheet->setCellValue('F6', 'password');

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
            'email' => 'ahmed.m@almamonuc.edu.iq',
            'name' => 'أحمد محمود',
            'job_title' => 'مدرس دكتور',
            'role' => 'head',
            'department_id' => $dept->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'maryam.j@almamonuc.edu.iq',
            'name' => 'مريم جاسم',
            'job_title' => 'مهندسة برمجيات',
            'role' => 'employee',
        ]);

        $this->assertDatabaseHas('departments', [
            'name' => 'كلية جديدة',
        ]);

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }
    }
}
