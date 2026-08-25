<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::create([
            'name' => 'المدير العام (Super Admin)',
            'email' => 'admin@creative-tasks.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 2. Create Departments
        $operationsDept = Department::create([
            'name' => 'قسم العمليات والمتابعة الميدانية',
            'work_start_time' => '08:00:00',
            'work_end_time' => '16:00:00',
        ]);

        $itDept = Department::create([
            'name' => 'قسم تقنية المعلومات والدعم الفني',
            'work_start_time' => '08:30:00',
            'work_end_time' => '16:30:00',
        ]);

        $hrDept = Department::create([
            'name' => 'قسم الموارد البشرية والشؤون الإدارية',
            'work_start_time' => '08:00:00',
            'work_end_time' => '15:30:00',
        ]);

        // 3. Create Department Heads
        $headOps = User::create([
            'name' => 'م. خالد المنصور (رئيس العمليات)',
            'email' => 'head.ops@creative-tasks.test',
            'password' => Hash::make('password'),
            'department_id' => $operationsDept->id,
            'role' => 'head',
            'is_active' => true,
        ]);
        $operationsDept->update(['manager_id' => $headOps->id]);

        $headIt = User::create([
            'name' => 'م. طارق العلي (رئيس تقنية المعلومات)',
            'email' => 'head.it@creative-tasks.test',
            'password' => Hash::make('password'),
            'department_id' => $itDept->id,
            'role' => 'head',
            'is_active' => true,
        ]);
        $itDept->update(['manager_id' => $headIt->id]);

        // 4. Create Employees
        $emp1 = User::create([
            'name' => 'أحمد حسام (مفتش ميداني)',
            'email' => 'ahmed@creative-tasks.test',
            'password' => Hash::make('password'),
            'department_id' => $operationsDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $emp2 = User::create([
            'name' => 'سارة الشمري (أخصائية متابعة)',
            'email' => 'sara@creative-tasks.test',
            'password' => Hash::make('password'),
            'department_id' => $operationsDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $emp3 = User::create([
            'name' => 'محمد رضوان (مهندس شبكات ميداني)',
            'email' => 'mohammed@creative-tasks.test',
            'password' => Hash::make('password'),
            'department_id' => $itDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $emp4 = User::create([
            'name' => 'فاطمة الزهراء (دعم فني أنظمة)',
            'email' => 'fatima@creative-tasks.test',
            'password' => Hash::make('password'),
            'department_id' => $itDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();

        // 5. Seed Attendance Logs
        AttendanceLog::create([
            'user_id' => $emp1->id,
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'log_date' => $today,
            'log_time' => '08:05:00',
        ]);

        AttendanceLog::create([
            'user_id' => $emp2->id,
            'latitude' => 24.7250,
            'longitude' => 46.6890,
            'log_date' => $today,
            'log_time' => '08:12:00',
        ]);

        AttendanceLog::create([
            'user_id' => $emp3->id,
            'latitude' => 24.7410,
            'longitude' => 46.6520,
            'log_date' => $today,
            'log_time' => '08:35:00',
        ]);

        // 6. Seed Sample Tasks for today
        Task::create([
            'department_id' => $operationsDept->id,
            'user_id' => $emp1->id,
            'assigned_by' => $headOps->id,
            'title' => 'معاينة الموقع الإنشائي في الفرع الشمالي',
            'description' => 'التأكد من التزام مقاولي التجهيزات بالمواصفات الفنية المعتمدة ورفع تقرير السلامة.',
            'progress' => 50,
            'task_type' => 'assigned',
            'status' => 'in_progress',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $operationsDept->id,
            'user_id' => $emp1->id,
            'assigned_by' => $headOps->id,
            'title' => 'مطابقة جداول استلام المواد الميدانية',
            'description' => 'مراجعة أذونات التوريد والتوقيع عليها مع مراقب المستودع الفرعي.',
            'progress' => 100,
            'task_type' => 'assigned',
            'status' => 'completed',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $operationsDept->id,
            'user_id' => $emp1->id,
            'assigned_by' => null,
            'title' => 'تسجيل تقرير الملاحظات الفورية عبر الجهاز اللوحي',
            'description' => 'إدخال ملاحظات الزيارة الاستثنائية بطلب من مشرف الأمن.',
            'progress' => 100,
            'task_type' => 'self_reported',
            'status' => 'completed',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $operationsDept->id,
            'user_id' => $emp2->id,
            'assigned_by' => $headOps->id,
            'title' => 'تدقيق تقارير الإنجاز اليومي للفرق الميدانية',
            'description' => 'تجميع مخرجات الفرق وإعداد ملخص تنفيذي للإدارة.',
            'progress' => 75,
            'task_type' => 'assigned',
            'status' => 'in_progress',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $itDept->id,
            'user_id' => $emp3->id,
            'assigned_by' => $headIt->id,
            'title' => 'صيانة وفحص نقطة الربط اللاسلكي بالمقر الفرعي',
            'description' => 'معايرة أجهزة التوجيه والتأكد من استقرار سرعة الاتصال وجودة الخدمة.',
            'progress' => 25,
            'task_type' => 'assigned',
            'status' => 'in_progress',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $itDept->id,
            'user_id' => $emp4->id,
            'assigned_by' => $headIt->id,
            'title' => 'تحديث تراخيص البرمجيات لأجهزة موظفي القسم',
            'description' => 'تثبيت التحديثات الأمنية وتجديد شهادات الوصول.',
            'progress' => 100,
            'task_type' => 'assigned',
            'status' => 'completed',
            'task_date' => $today,
        ]);
    }
}
