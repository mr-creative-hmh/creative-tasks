<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the production database seeds.
     */
    public function run(): void
    {
        // 1. Create Core Corporate Departments
        $departments = [
            [
                'name' => 'تكنولوجيا المعلومات والبرمجيات',
                'description' => 'إدارة البنية التحتية والأنظمة الرقمية وتطوير الحلول البرمجية',
                'work_start_time' => '08:30:00',
                'work_end_time' => '16:30:00',
            ],
            [
                'name' => 'الإدارة العامة والموارد البشرية',
                'description' => 'شؤون الموظفين، التوظيف، التنظيم الإداري، والامتثال المؤسسي',
                'work_start_time' => '08:00:00',
                'work_end_time' => '16:00:00',
            ],
            [
                'name' => 'العمليات والمشاريع الميدانية',
                'description' => 'إدارة وتنفيذ ومتابعة المشاريع الميدانية والفرق المتنقلة',
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
            ],
            [
                'name' => 'التسويق وتطوير الأعمال',
                'description' => 'إدارة العلامة التجارية، نمو المبيعات، والعلاقات العامة',
                'work_start_time' => '09:00:00',
                'work_end_time' => '17:00:00',
            ],
            [
                'name' => 'المالية والمحاسبة',
                'description' => 'التخطيط المالي، الميزانيات، الرواتب، وإعداد التقارير المالية',
                'work_start_time' => '08:30:00',
                'work_end_time' => '16:30:00',
            ],
        ];

        $deptModels = [];
        foreach ($departments as $deptData) {
            $deptModels[] = Department::firstOrCreate(
                ['name' => $deptData['name']],
                $deptData
            );
        }

        // 2. Create Super Admin Account
        User::firstOrCreate(
            ['email' => 'admin@creativetasks.io'],
            [
                'name' => 'System Administrator',
                'job_title' => 'Chief Operations Officer',
                'password' => Hash::make('Admin@2026!'),
                'department_id' => $deptModels[0]->id,
                'role' => 'admin',
                'is_active' => true,
                'attendance_mode' => 'gps',
                'fixed_latitude' => 33.31280,
                'fixed_longitude' => 44.36150,
                'fixed_location_name' => 'المقر الرئيسي للمؤسسة',
            ]
        );
    }
}
