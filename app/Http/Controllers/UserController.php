<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\Department;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $departmentId = $request->input('department_id');

        $query = User::with('department')->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $users = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'departments' => $departments,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'department_id' => $departmentId,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,head,employee'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'is_active' => ['boolean'],
            'attendance_mode' => ['nullable', 'in:gps,fixed'],
            'fixed_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'fixed_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'fixed_location_name' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['attendance_mode'] = $validated['attendance_mode'] ?? 'gps';

        $user = User::create($validated);

        if ($user->attendance_mode === 'fixed') {
            AttendanceLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => Carbon::today()->toDateString(),
                ],
                [
                    'latitude' => $user->fixed_latitude ?? 33.31524,
                    'longitude' => $user->fixed_longitude ?? 44.36612,
                    'log_time' => Carbon::now()->toTimeString(),
                    'notes' => 'حضور مكتبي ثابت معتمد: ' . ($user->fixed_location_name ?? 'مقر الحرم الجامعي'),
                ]
            );
        }

        return back()->with('success', 'تمت إضافة الكادر بنجاح.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,head,employee'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'is_active' => ['boolean'],
            'attendance_mode' => ['nullable', 'in:gps,fixed'],
            'fixed_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'fixed_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'fixed_location_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($user->attendance_mode === 'fixed') {
            AttendanceLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => Carbon::today()->toDateString(),
                ],
                [
                    'latitude' => $user->fixed_latitude ?? 33.31524,
                    'longitude' => $user->fixed_longitude ?? 44.36612,
                    'log_time' => Carbon::now()->toTimeString(),
                    'notes' => 'حضور مكتبي ثابت معتمد: ' . ($user->fixed_location_name ?? 'مقر الحرم الجامعي'),
                ]
            );
        }

        return back()->with('success', 'تم تحديث بيانات الكادر بنجاح.');
    }

    public function setLocationSettings(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_mode' => ['required', 'in:gps,fixed'],
            'fixed_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'fixed_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'fixed_location_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['attendance_mode'] === 'fixed') {
            if (empty($validated['fixed_latitude']) || empty($validated['fixed_longitude'])) {
                $validated['fixed_latitude'] = 33.31524;
                $validated['fixed_longitude'] = 44.36612;
            }
            if (empty($validated['fixed_location_name'])) {
                $validated['fixed_location_name'] = $user->department?->name ? 'مقر ' . $user->department->name : 'مقر الحرم الجامعي';
            }

            AttendanceLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => Carbon::today()->toDateString(),
                ],
                [
                    'latitude' => $validated['fixed_latitude'],
                    'longitude' => $validated['fixed_longitude'],
                    'log_time' => Carbon::now()->toTimeString(),
                    'notes' => 'حضور مكتبي ثابت معتمد: ' . $validated['fixed_location_name'],
                ]
            );
        }

        $user->update($validated);

        return back()->with('success', 'تم تحديث نمط وإعدادات الموقع الجغرافي للموظف بنجاح.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'تمت إعادة تعيين كلمة المرور للمستخدم بنجاح.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'لا يمكنك حذف حسابك الشخصي الحالي.');
        }

        $user->delete();

        return back()->with('success', 'تم حذف الكادر بنجاح.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        $msg = $user->is_active ? 'تم تفعيل حساب المستخدم بنجاح.' : 'تم تعطيل حساب المستخدم بنجاح.';
        return back()->with('success', $msg);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        
        // --- Sheet 1: Main Template ---
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('بيانات الكوادر (Staff)');
        $sheet1->setRightToLeft(true);

        $sheet1->setCellValue('A1', 'نموذج استيراد بيانات الكوادر والتدريسيين - جامعة المأمون');
        $sheet1->mergeCells('A1:F1');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0284C7'));
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet1->setCellValue('A2', 'ملاحظة: يرجى كتابة اسم القسم تماماً كما هو مسجل في ورقة (الأقسام المعتمدة). الحقول المطلوبة: الاسم، البريد، الصلاحية.');
        $sheet1->mergeCells('A2:F2');
        $sheet1->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A4' => 'الاسم الرباعي واللقب * (Full Name)',
            'B4' => 'البريد الإلكتروني الجامعي * (Email)',
            'C4' => 'المسمى الوظيفي / الرتبة (Job Title)',
            'D4' => 'اسم القسم أو الكلية (Department Name)',
            'E4' => 'الصلاحية * (Role: admin, head, employee)',
            'F4' => 'كلمة المرور الابتدائية (Default Password)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet1->setCellValue($cell, $text);
        }

        $sheet1->getStyle('A4:F4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0369A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);
        $sheet1->getRowDimension(4)->setRowHeight(28);

        $samples = [
            ['د. علي حسين مكي', 'ali.maki@almamonuc.edu.iq', 'أستاذ دكتور', 'قسم علوم الحاسوب', 'head', 'Mamon@2026'],
            ['م. مريم صادق كريم', 'maryam.s@almamonuc.edu.iq', 'مدرس مساعد', 'قسم هندسة تقنيات الحاسوب', 'employee', 'Mamon@2026'],
            ['حيدر كاظم عبيد', 'haider.k@almamonuc.edu.iq', 'مهندس برمجيات', 'قسم شؤون الطلبة والتسجيل', 'employee', 'Mamon@2026'],
        ];

        $row = 5;
        foreach ($samples as $sample) {
            $sheet1->setCellValue("A{$row}", $sample[0]);
            $sheet1->setCellValue("B{$row}", $sample[1]);
            $sheet1->setCellValue("C{$row}", $sample[2]);
            $sheet1->setCellValue("D{$row}", $sample[3]);
            $sheet1->setCellValue("E{$row}", $sample[4]);
            $sheet1->setCellValue("F{$row}", $sample[5]);

            $sheet1->getStyle("A{$row}:F{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet1->getRowDimension($row)->setRowHeight(22);
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Sheet 2: Reference of Existing Departments ---
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('الأقسام المعتمدة (Departments)');
        $sheet2->setRightToLeft(true);

        $sheet2->setCellValue('A1', 'قائمة الأقسام والكليات المسجلة حالياً في النظام (للاسترشاد بها في عمود اسم القسم)');
        $sheet2->mergeCells('A1:D1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0D9488'));
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $deptHeaders = [
            'A3' => '# (ID)',
            'B3' => 'اسم القسم / الكلية (Department Name)',
            'C3' => 'رئيس القسم الحالي (Current Manager)',
            'D3' => 'أوقات الدوام الرسمي (Working Shift)',
        ];

        foreach ($deptHeaders as $cell => $text) {
            $sheet2->setCellValue($cell, $text);
        }

        $sheet2->getStyle('A3:D3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F766E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);
        $sheet2->getRowDimension(3)->setRowHeight(26);

        $departments = Department::with('manager')->orderBy('id')->get();
        $dRow = 4;
        foreach ($departments as $d) {
            $sheet2->setCellValue("A{$dRow}", $d->id);
            $sheet2->setCellValue("B{$dRow}", $d->name);
            $sheet2->setCellValue("C{$dRow}", $d->manager?->name ?? 'غير معين');
            $sheet2->setCellValue("D{$dRow}", substr($d->work_start_time, 0, 5) . ' - ' . substr($d->work_end_time, 0, 5));

            $sheet2->getStyle("A{$dRow}:D{$dRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet2->getRowDimension($dRow)->setRowHeight(20);
            $dRow++;
        }

        foreach (range('A', 'D') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // Set active sheet back to Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'almamon_users_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->with('error', 'فشل في قراءة ملف الإكسل: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 4) {
            return back()->with('error', 'الملف المرفوع فارغ أو لا يحتوي على بنية البيانات المطلوبة.');
        }

        $headerRowIndex = 4;
        foreach ($rows as $index => $cols) {
            $joined = implode(' ', array_filter($cols));
            if (str_contains($joined, 'الاسم') || str_contains(strtolower($joined), 'name') || str_contains($joined, 'البريد') || str_contains(strtolower($joined), 'email')) {
                $headerRowIndex = $index;
                break;
            }
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        $departments = Department::all();

        for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? [];
            if (empty($row)) continue;

            $name = trim($row['A'] ?? '');
            $email = trim($row['B'] ?? '');
            $jobTitle = trim($row['C'] ?? '');
            $deptName = trim($row['D'] ?? '');
            $roleInput = strtolower(trim($row['E'] ?? ''));
            $password = trim($row['F'] ?? '');

            if (empty($name) && empty($email)) {
                continue;
            }

            if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skippedCount++;
                continue;
            }

            $departmentId = null;
            if (!empty($deptName)) {
                $matchedDept = $departments->first(function ($d) use ($deptName) {
                    return mb_strtolower(trim($d->name)) === mb_strtolower($deptName)
                        || str_contains(mb_strtolower($d->name), mb_strtolower($deptName));
                });

                if ($matchedDept) {
                    $departmentId = $matchedDept->id;
                } else {
                    $newDept = Department::create([
                        'name' => $deptName,
                        'work_start_time' => '08:00:00',
                        'work_end_time' => '15:30:00',
                    ]);
                    $departments->push($newDept);
                    $departmentId = $newDept->id;
                }
            }

            $role = 'employee';
            if (str_contains($roleInput, 'head') || str_contains($roleInput, 'رئيس') || str_contains($roleInput, 'قسم')) {
                $role = 'head';
            } elseif (str_contains($roleInput, 'admin') || str_contains($roleInput, 'مدير') || str_contains($roleInput, 'عام') || str_contains($roleInput, 'مسؤول')) {
                $role = 'admin';
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                $updateData = [
                    'name' => $name,
                    'job_title' => $jobTitle ?: $user->job_title,
                    'department_id' => $departmentId ?: $user->department_id,
                    'role' => $role,
                ];

                if (!empty($password)) {
                    $updateData['password'] = Hash::make($password);
                }

                $user->update($updateData);
                $updatedCount++;
            } else {
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'job_title' => $jobTitle ?: null,
                    'department_id' => $departmentId,
                    'role' => $role,
                    'password' => Hash::make(!empty($password) ? $password : 'password'),
                    'is_active' => true,
                    'attendance_mode' => 'gps',
                ]);
                $createdCount++;
            }
        }

        $feedbackMsg = "تمت معالجة الملف بنجاح! تم إنشاء {$createdCount} كادر جديد، وتحديث {$updatedCount} حساب.";
        if ($skippedCount > 0) {
            $feedbackMsg .= " (تم تخطي {$skippedCount} سجل غير صالح).";
        }

        return back()->with('success', $feedbackMsg);
    }
}
