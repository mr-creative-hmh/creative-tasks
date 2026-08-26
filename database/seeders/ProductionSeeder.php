<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Core Enterprise Departments
        $depts = [
            [
                'name' => 'الإدارة التنفيذية والعامة',
                'work_start_time' => '08:00',
                'work_end_time' => '16:00',
            ],
            [
                'name' => 'قسم تطوير البرمجيات والتقنية',
                'work_start_time' => '08:30',
                'work_end_time' => '16:30',
            ],
            [
                'name' => 'قسم العمليات والمتابعة الميدانية',
                'work_start_time' => '07:30',
                'work_end_time' => '16:00',
            ],
            [
                'name' => 'قسم التسويق والمبيعات',
                'work_start_time' => '08:30',
                'work_end_time' => '16:00',
            ],
            [
                'name' => 'قسم الموارد البشرية والشؤون الإدارية',
                'work_start_time' => '08:00',
                'work_end_time' => '16:00',
            ],
            [
                'name' => 'قسم المالية والمحاسبة',
                'work_start_time' => '08:30',
                'work_end_time' => '16:00',
            ],
            [
                'name' => 'قسم خدمة العملاء والدعم الفني',
                'work_start_time' => '08:00',
                'work_end_time' => '16:30',
            ],
        ];

        $createdDepts = [];
        foreach ($depts as $d) {
            $createdDepts[$d['name']] = Department::firstOrCreate(
                ['name' => $d['name']],
                [
                    'work_start_time' => $d['work_start_time'],
                    'work_end_time' => $d['work_end_time'],
                ]
            );
        }

        // 2. Create Master Super Admin (CEO / System Admin)
        $execDept = $createdDepts['الإدارة التنفيذية والعامة'] ?? Department::first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@creativetasks.io'],
            [
                'name' => 'المدير التنفيذي / الإدارة العامة',
                'job_title' => 'المدير التنفيذي العام',
                'password' => Hash::make('Admin@2026!'),
                'role' => 'admin',
                'department_id' => $execDept?->id,
                'is_active' => true,
                'attendance_mode' => 'fixed',
                'fixed_location_name' => 'المقر الرئيسي للشركة',
                'fixed_latitude' => 33.31524,
                'fixed_longitude' => 44.36612,
            ]
        );

        if ($execDept && !$execDept->manager_id) {
            $execDept->update(['manager_id' => $admin->id]);
        }
    }
}