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

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        
        // --- Sheet 1: Main Template (Streamlined 5 Columns) ---
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('الكوادر (Staff)');
        $sheet1->setRightToLeft(true);

        // Header Title & Instructions
        $sheet1->setCellValue('A1', 'جامعة المأمون - نموذج استيراد الكوادر والمستخدمين');
        $sheet1->mergeCells('A1:E1');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0284C7'));
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet1->setCellValue('A2', 'ملاحظة: اختر القسم والدور مباشرة من القوائم المنسدلة (Dropdown Lists). كلمة المرور والـ GPS يتم تعيينها افتراضياً.');
        $sheet1->mergeCells('A2:E2');
        $sheet1->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A4' => 'الاسم الكامل * (Full Name)',
            'B4' => 'البريد الإلكتروني * (Email)',
            'C4' => 'المسمى الوظيفي (Job Title)',
            'D4' => 'القسم * (اختر من القائمة)',
            'E4' => 'الدور والصلاحية * (اختر من القائمة)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet1->setCellValue($cell, $text);
        }

        $sheet1->getStyle('A4:E4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0369A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);
        $sheet1->getRowDimension(4)->setRowHeight(28);

        // Sample rows
        $samples = [
            ['د. محمد الراوي', 'm.alrawi@almamonuc.edu.iq', 'عميد الكلية', 'عمادة الكلية', 'رئيس قسم (head)'],
            ['أ. هيثم الجبوري', 'h.aljuboori@almamonuc.edu.iq', 'مقرر قسم الحاسوب', 'قسم هندسة تقنيات الحاسوب', 'رئيس قسم (head)'],
            ['م. حيدر البغدادي', 'h.albaghdadi@almamonuc.edu.iq', 'مهندس برمجيات ونظم', 'قسم هندسة تقنيات الحاسوب', 'موظف (employee)'],
        ];

        $row = 5;
        foreach ($samples as $sample) {
            $sheet1->setCellValue("A{$row}", $sample[0]);
            $sheet1->setCellValue("B{$row}", $sample[1]);
            $sheet1->setCellValue("C{$row}", $sample[2]);
            $sheet1->setCellValue("D{$row}", $sample[3]);
            $sheet1->setCellValue("E{$row}", $sample[4]);

            $sheet1->getStyle("A{$row}:E{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet1->getRowDimension($row)->setRowHeight(22);
            $row++;
        }

        // --- Sheet 2: Data Sources for Dropdown Validation ---
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('الأقسام (Departments)');
        $sheet2->setRightToLeft(true);

        $sheet2->setCellValue('A1', 'قائمة الأقسام المعتمدة');
        $sheet2->setCellValue('B1', 'الأدوار والصلاحيات');

        $departments = Department::orderBy('name')->pluck('name')->toArray();
        if (empty($departments)) {
            $departments = ['عمادة الكلية', 'قسم هندسة تقنيات الحاسوب', 'قسم الصيدلة', 'الشؤون الإدارية والمالية'];
        }

        foreach ($departments as $idx => $deptName) {
            $dRow = $idx + 2;
            $sheet2->setCellValue("A{$dRow}", $deptName);
        }
        $lastDeptRow = count($departments) + 1;

        $roles = ['موظف (employee)', 'رئيس قسم (head)', 'مدير نظام (admin)'];
        foreach ($roles as $idx => $roleName) {
            $rRow = $idx + 2;
            $sheet2->setCellValue("B{$rRow}", $roleName);
        }
        $lastRoleRow = count($roles) + 1;

        // Set Dropdown Data Validation for Rows 5 to 500
        for ($r = 5; $r <= 500; $r++) {
            // Department Dropdown
            $deptVal = $sheet1->getCell("D{$r}")->getDataValidation();
            $deptVal->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $deptVal->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $deptVal->setAllowBlank(true);
            $deptVal->setShowInputMessage(true);
            $deptVal->setShowErrorMessage(true);
            $deptVal->setShowDropDown(true);
            $deptVal->setErrorTitle('اختيار القسم');
            $deptVal->setError('يرجى اختيار أحد الأقسام المعتمدة من القائمة.');
            $deptVal->setPromptTitle('القسم');
            $deptVal->setPrompt('اختر القسم من القائمة المنسدلة');
            $deptVal->setFormula1("'الأقسام (Departments)'!\$A\$2:\$A\${$lastDeptRow}");

            // Role Dropdown
            $roleVal = $sheet1->getCell("E{$r}")->getDataValidation();
            $roleVal->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $roleVal->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $roleVal->setAllowBlank(true);
            $roleVal->setShowInputMessage(true);
            $roleVal->setShowErrorMessage(true);
            $roleVal->setShowDropDown(true);
            $roleVal->setErrorTitle('اختيار الدور');
            $roleVal->setError('يرجى اختيار أحد الأدوار المعتمدة من القائمة.');
            $roleVal->setPromptTitle('الدور');
            $roleVal->setPrompt('اختر الدور من القائمة المنسدلة');
            $roleVal->setFormula1("'الأقسام (Departments)'!\$B\$2:\$B\${$lastRoleRow}");
        }

        foreach (range('A', 'E') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet2->getColumnDimension('A')->setAutoSize(true);
        $sheet2->getColumnDimension('B')->setAutoSize(true);

        $spreadsheet->setActiveSheetIndex(0);

        $tempPath = tempnam(sys_get_temp_dir(), 'almamon_template_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        while (ob_get_level()) {
            ob_end_clean();
        }

        return response()->download($tempPath, 'almamon_users_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ])->deleteFileAfterSend(true);
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
            return back()->with('error', 'تعذر قراءة ملف الإكسل: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 4) {
            return back()->with('error', 'ملف الإكسل فارغ أو لا يحتوي على صفوف بيانات.');
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

            $name = trim((string)($row['A'] ?? ''));
            $email = trim((string)($row['B'] ?? ''));
            $jobTitle = trim((string)($row['C'] ?? ''));
            $deptName = trim((string)($row['D'] ?? ''));
            $roleRaw = mb_strtolower(trim((string)($row['E'] ?? 'employee')));

            if (empty($name) || empty($email)) {
                $skippedCount++;
                continue;
            }

            // Role normalization (supports Arabic & English dropdown values)
            $role = 'employee';
            if (str_contains($roleRaw, 'admin') || str_contains($roleRaw, 'مدير')) {
                $role = 'admin';
            } elseif (str_contains($roleRaw, 'head') || str_contains($roleRaw, 'رئيس') || str_contains($roleRaw, 'مسؤول')) {
                $role = 'head';
            }

            // Department resolution (find matching or auto-create if not found)
            $departmentId = null;
            if (!empty($deptName)) {
                $matchedDept = $departments->first(function ($d) use ($deptName) {
                    return str_contains(mb_strtolower($d->name), mb_strtolower($deptName)) ||
                           str_contains(mb_strtolower($deptName), mb_strtolower($d->name));
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

            // Default password, GPS mode and University location
            $password = !empty($row['F']) && !in_array(strtolower((string)$row['F']), ['gps', 'fixed']) ? trim((string)$row['F']) : 'Mamon@2026';
            $attendanceMode = 'gps';
            $fixedLocationName = 'حرم جامعة المأمون الرئيسي';
            $fixedLatitude = 33.31524;
            $fixedLongitude = 44.36612;

            // Check if user exists
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                $existingUser->update([
                    'name' => $name,
                    'job_title' => $jobTitle ?: $existingUser->job_title,
                    'department_id' => $departmentId ?: $existingUser->department_id,
                    'role' => $role,
                    'attendance_mode' => $existingUser->attendance_mode ?: $attendanceMode,
                    'fixed_location_name' => $existingUser->fixed_location_name ?: $fixedLocationName,
                    'fixed_latitude' => $existingUser->fixed_latitude ?: $fixedLatitude,
                    'fixed_longitude' => $existingUser->fixed_longitude ?: $fixedLongitude,
                ]);
                $updatedCount++;
            } else {
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'job_title' => $jobTitle,
                    'department_id' => $departmentId,
                    'role' => $role,
                    'is_active' => true,
                    'attendance_mode' => $attendanceMode,
                    'fixed_location_name' => $fixedLocationName,
                    'fixed_latitude' => $fixedLatitude,
                    'fixed_longitude' => $fixedLongitude,
                ]);
                $createdCount++;
            }
        }

        $message = "تمت معالجة ملف الإكسل بنجاح: إضافة {$createdCount} مستخدم جديد، تحديث {$updatedCount} مستخدم، وتخطي {$skippedCount} صفوف فارغة.";
        return back()->with('success', $message);
    }
}
