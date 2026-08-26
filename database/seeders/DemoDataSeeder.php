<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProductionSeeder::class);

        $devDept = Department::where('name', 'قسم تطوير البرمجيات والتقنية')->first();
        $opsDept = Department::where('name', 'قسم العمليات والمتابعة الميدانية')->first();
        $salesDept = Department::where('name', 'قسم التسويق والمبيعات')->first();
        $hrDept = Department::where('name', 'قسم الموارد البشرية والشؤون الإدارية')->first();

        // 1. Department Heads
        $headOps = User::firstOrCreate(
            ['email' => 'head.ops@creativetasks.io'],
            [
                'name' => 'طارق عبد الرحيم النجار',
                'job_title' => 'مدير العمليات الميدانية',
                'password' => Hash::make('password'),
                'role' => 'head',
                'department_id' => $opsDept?->id,
                'is_active' => true,
                'attendance_mode' => 'gps',
                'fixed_location_name' => 'مركز العمليات الميدانية',
                'fixed_latitude' => 33.31524,
                'fixed_longitude' => 44.36612,
            ]
        );
        $opsDept?->update(['manager_id' => $headOps->id]);

        $headDev = User::firstOrCreate(
            ['email' => 'head.dev@creativetasks.io'],
            [
                'name' => 'م. زياد كمال الفهد',
                'job_title' => 'مدير التطوير التقني والبرمجيات',
                'password' => Hash::make('password'),
                'role' => 'head',
                'department_id' => $devDept?->id,
                'is_active' => true,
                'attendance_mode' => 'fixed',
                'fixed_location_name' => 'المقر الرئيسي - جناح التقنية',
                'fixed_latitude' => 33.31524,
                'fixed_longitude' => 44.36612,
            ]
        );
        $devDept?->update(['manager_id' => $headDev->id]);

        // 2. Staff Employees
        $empField = User::firstOrCreate(
            ['email' => 'emp.field1@creativetasks.io'],
            [
                'name' => 'ياسر منير الصالح',
                'job_title' => 'أخصائي ومفتش ميداني أول',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department_id' => $opsDept?->id,
                'is_active' => true,
                'attendance_mode' => 'gps',
                'fixed_location_name' => 'المقر الرئيسي للشركة',
                'fixed_latitude' => 33.31524,
                'fixed_longitude' => 44.36612,
            ]
        );

        $empDev = User::firstOrCreate(
            ['email' => 'emp.dev1@creativetasks.io'],
            [
                'name' => 'سارة نبيل العمر',
                'job_title' => 'مهندسة واجهات ومطور برمجيات',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department_id' => $devDept?->id,
                'is_active' => true,
                'attendance_mode' => 'fixed',
                'fixed_location_name' => 'المقر الرئيسي - جناح التقنية',
                'fixed_latitude' => 33.31524,
                'fixed_longitude' => 44.36612,
            ]
        );

        // 3. Demo Tasks
        $superAdmin = User::where('role', 'admin')->first();
        $today = Carbon::today()->toDateString();

        $tasks = [
            [
                'title' => 'معاينة وفحص تجهيزات الموقع الميداني في الفرع 4',
                'description' => 'إجراء مسح ميداني شامل للمعدات والتأكد من مطابقة شروط السلامة والتنفيذ في الموقع.',
                'department_id' => $opsDept?->id,
                'user_id' => $empField->id,
                'assigned_by' => $headOps->id,
                'progress' => 50,
                'task_type' => 'assigned',
                'status' => 'in_progress',
                'task_date' => $today,
            ],
            [
                'title' => 'تطوير واجهة التصدير التفاعلية للتقارير الشهرية',
                'description' => 'بناء محرك تصدير ملفات Excel و PDF مع دعم الجداول التراكمية والتخصيص المؤسسي.',
                'department_id' => $devDept?->id,
                'user_id' => $empDev->id,
                'assigned_by' => $headDev->id,
                'progress' => 100,
                'task_type' => 'assigned',
                'status' => 'completed',
                'task_date' => $today,
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::firstOrCreate(['title' => $taskData['title']], $taskData);
        }

        // 4. Attendance Logs
        $staffMembers = [$headOps, $headDev, $empField, $empDev];
        foreach ($staffMembers as $index => $staff) {
            AttendanceLog::firstOrCreate(
                [
                    'user_id' => $staff->id,
                    'log_date' => $today,
                ],
                [
                    'log_time' => Carbon::today()->setTime(8, 0 + ($index * 10))->format('H:i:s'),
                    'latitude' => $staff->fixed_latitude ?? (33.31524 + ($index * 0.0001)),
                    'longitude' => $staff->fixed_longitude ?? (44.36612 + ($index * 0.0001)),
                ]
            );
        }
    }
}