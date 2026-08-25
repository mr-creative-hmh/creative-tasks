<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create University Departments
        $itDept = Department::create([
            'name' => 'قسم تكنولوجيا المعلومات والاتصالات',
            'work_start_time' => '08:00',
            'work_end_time' => '15:00',
        ]);

        $engineeringMaintDept = Department::create([
            'name' => 'قسم الصيانة والشؤون الهندسية',
            'work_start_time' => '07:30',
            'work_end_time' => '15:00',
        ]);

        $securityDept = Department::create([
            'name' => 'شعبة المتابعة والأمن الجامعي',
            'work_start_time' => '07:00',
            'work_end_time' => '16:00',
        ]);

        $adminAffairsDept = Department::create([
            'name' => 'قسم الشؤون الإدارية والمالية',
            'work_start_time' => '08:30',
            'work_end_time' => '15:00',
        ]);

        $qualityDept = Department::create([
            'name' => 'شعبة ضمان الجودة والأداء الأكاديمي',
            'work_start_time' => '08:30',
            'work_end_time' => '14:30',
        ]);

        $studentsDept = Department::create([
            'name' => 'قسم شؤون الطلبة والتسجيل',
            'work_start_time' => '08:00',
            'work_end_time' => '15:00',
        ]);

        // 2. Create Super Admin (University Presidency / General Management)
        $superAdmin = User::create([
            'name' => 'المدير العام',
            'job_title' => 'رئيس الجامعة / المدير العام',
            'email' => 'admin@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department_id' => $adminAffairsDept->id,
            'is_active' => true,
        ]);

        // 3. Create Department Heads (Deans / Supervisors)
        $headIt = User::create([
            'name' => 'د. محمد الراوي',
            'job_title' => 'أستاذ مساعد / رئيس قسم IT',
            'email' => 'head.it@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $itDept->id,
            'role' => 'head',
            'is_active' => true,
        ]);
        $itDept->update(['manager_id' => $headIt->id]);

        $headAdmin = User::create([
            'name' => 'أ. هيثم الجبوري',
            'job_title' => 'مدير الشؤون الإدارية',
            'email' => 'head.admin@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $adminAffairsDept->id,
            'role' => 'head',
            'is_active' => true,
        ]);
        $adminAffairsDept->update(['manager_id' => $headAdmin->id]);

        $headSecurity = User::create([
            'name' => 'العقيد م. عادل الشمري',
            'job_title' => 'مسؤول المتابعة والأمن',
            'email' => 'head.security@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $securityDept->id,
            'role' => 'head',
            'is_active' => true,
        ]);
        $securityDept->update(['manager_id' => $headSecurity->id]);

        $headQuality = User::create([
            'name' => 'د. نادية العبيدي',
            'job_title' => 'مديرة ضمان الجودة',
            'email' => 'head.quality@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $qualityDept->id,
            'role' => 'head',
            'is_active' => true,
        ]);
        $qualityDept->update(['manager_id' => $headQuality->id]);

        // 4. Create Field & Academic Staff (Employees)
        $emp1 = User::create([
            'name' => 'علي الكرخي',
            'job_title' => 'مهندس شبكات وبنية تحتية',
            'email' => 'ali.it@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $itDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $emp2 = User::create([
            'name' => 'عمر الدليمي',
            'job_title' => 'مراقب متابعة وتفتيش ميداني',
            'email' => 'omar.security@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $securityDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $emp3 = User::create([
            'name' => 'م. حيدر البغدادي',
            'job_title' => 'مسؤول صيانة التجهيزات الهندسية',
            'email' => 'haider.eng@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $engineeringMaintDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $emp4 = User::create([
            'name' => 'نور الهدى الربيعي',
            'job_title' => 'أخصائية تدقيق جودة القاعات',
            'email' => 'nour.quality@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $qualityDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $emp5 = User::create([
            'name' => 'زينب الموسوي',
            'job_title' => 'مشرفة تسجيل وإرشاد الطلبة',
            'email' => 'zainab.reg@almamonuc.edu.iq',
            'password' => Hash::make('password'),
            'department_id' => $studentsDept->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $today = Carbon::today()->toDateString();

        // 5. Seed Attendance Logs
        AttendanceLog::create([
            'user_id' => $emp1->id,
            'latitude' => 33.31524,
            'longitude' => 44.36612,
            'log_date' => $today,
            'log_time' => '08:05:00',
        ]);

        AttendanceLog::create([
            'user_id' => $emp2->id,
            'latitude' => 33.31680,
            'longitude' => 44.36750,
            'log_date' => $today,
            'log_time' => '07:45:00',
        ]);

        AttendanceLog::create([
            'user_id' => $emp3->id,
            'latitude' => 33.31400,
            'longitude' => 44.36480,
            'log_date' => $today,
            'log_time' => '08:12:00',
        ]);

        AttendanceLog::create([
            'user_id' => $emp4->id,
            'latitude' => 33.31590,
            'longitude' => 44.36550,
            'log_date' => $today,
            'log_time' => '08:30:00',
        ]);

        // 6. Seed Tasks for Today
        Task::create([
            'department_id' => $itDept->id,
            'user_id' => $emp1->id,
            'assigned_by' => $headIt->id,
            'title' => 'فحص سيرفرات الامتحانات الإلكترونية ومسار بولونيا',
            'description' => 'التأكد من جاهزية شبكة الألياف الضوئية والخوادم المحلية في قاعات الكلية التقنية الهندسية.',
            'progress' => 50,
            'task_type' => 'assigned',
            'status' => 'in_progress',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $itDept->id,
            'user_id' => $emp1->id,
            'assigned_by' => $headIt->id,
            'title' => 'ربط كاميرات المراقبة الجديدة في مجمع القاعات الطبية',
            'description' => 'إكمال إعدادات الـ IP وتسجيل البث المباشر في غرفة التحكم المركزية.',
            'progress' => 100,
            'task_type' => 'assigned',
            'status' => 'completed',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $itDept->id,
            'user_id' => $emp1->id,
            'assigned_by' => null,
            'title' => 'معايرة أجهزة البصمة الإلكترونية عند مدخل كلية الصيدلة',
            'description' => 'تحديث قاعدة بيانات الموظفين وإجراء اختبار تسجيل الحضور.',
            'progress' => 100,
            'task_type' => 'self_reported',
            'status' => 'completed',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $securityDept->id,
            'user_id' => $emp2->id,
            'assigned_by' => $headSecurity->id,
            'title' => 'جولة التفتيش الأمني الميداني لمحيط الحرم الجامعي والبوابات',
            'description' => 'التأكد من انسيابية حركة الدخول وتدقيق باجات الطلبة والأساتذة.',
            'progress' => 75,
            'task_type' => 'assigned',
            'status' => 'in_progress',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $qualityDept->id,
            'user_id' => $emp4->id,
            'assigned_by' => $headQuality->id,
            'title' => 'تدقيق مطابقة معايير الاعتماد المؤسسي في مختبرات التحليلات',
            'description' => 'فحص سجلات الأجهزة والمواد المختبرية ومطابقتها لتعليمات وزارة التعليم العالي.',
            'progress' => 25,
            'task_type' => 'assigned',
            'status' => 'in_progress',
            'task_date' => $today,
        ]);

        Task::create([
            'department_id' => $studentsDept->id,
            'user_id' => $emp5->id,
            'assigned_by' => null,
            'title' => 'استلام وتدقيق وثائق الطلبة المتقدمين للدراسة المسائية',
            'description' => 'إدخال بيانات 45 استمارة في المنظومة المركزية ومطابقة صور القيد.',
            'progress' => 100,
            'task_type' => 'self_reported',
            'status' => 'completed',
            'task_date' => $today,
        ]);
    }
}
