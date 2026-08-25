<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\Department;
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
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        User::create($validated);

        return back()->with('success', 'تم إضافة المستخدم بنجاح.');
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
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي.');
        }

        $user->delete();

        return back()->with('success', 'تم حذف المستخدم بنجاح.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        $msg = $user->is_active ? 'تم تفعيل حساب المستخدم.' : 'تم تعطيل حساب المستخدم.';
        return back()->with('success', $msg);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return back()->with('success', 'تم إعادة تعيين كلمة المرور بنجاح للمستخدم: ' . $user->name);
    }

    /**
     * Download an official Excel template pre-populated with active departments and instructions.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // --- Sheet 1: Staff Data Input ---
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('بيانات الكوادر (Staff Data)');
        $sheet1->setRightToLeft(true);

        // Title Header
        $sheet1->setCellValue('A1', 'جامعة المأمون - نموذج استيراد الكوادر والموظفين (Staff Import Template)');
        $sheet1->mergeCells('A1:F1');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0284C7'));
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Instructions note
        $sheet1->setCellValue('A2', 'ملاحظات: الحقول بعلامة (*) إلزامية | كلمة المرور الافتراضية ستكون (password) إذا تركت فارغة | راجع ورقة (الأقسام المتاحة) لأسماء الأقسام المعرفة');
        $sheet1->mergeCells('A2:F2');
        $sheet1->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Columns
        $headers = [
            'A4' => 'الاسم الكامل * (Full Name)',
            'B4' => 'البريد الجامعي الإلكتروني * (Email)',
            'C4' => 'المسمى الوظيفي أو الأكاديمي (Job Title)',
            'D4' => 'الكلية أو القسم (Department)',
            'E4' => 'الدور والصلاحية (Role)',
            'F4' => 'كلمة المرور (Password - Optional)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet1->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0369A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ];
        $sheet1->getStyle('A4:F4')->applyFromArray($headerStyle);
        $sheet1->getRowDimension(4)->setRowHeight(28);

        // Sample Rows
        $sampleDepartments = Department::pluck('name')->toArray();
        $sampleDept1 = $sampleDepartments[0] ?? 'قسم علوم الحاسوب';
        $sampleDept2 = $sampleDepartments[1] ?? 'كلية القانون';
        $sampleDept3 = $sampleDepartments[2] ?? 'قسم الشبكات والاتصالات';

        $samples = [
            ['د. مصطفى العبيدي', 'm.obaidi@almamonuc.edu.iq', 'أستاذ دكتور', $sampleDept1, 'رئيس قسم (head)', 'password'],
            ['م. زينب حميد حسن', 'z.hameed@almamonuc.edu.iq', 'مدرس مساعد / مهندسة برمجيات', $sampleDept1, 'كادر (employee)', 'password'],
            ['علي حسين كاظم', 'a.hussain@almamonuc.edu.iq', 'مسؤول مختبر حاسوب', $sampleDept2, 'كادر (employee)', 'password'],
            ['سارة طارق مهدي', 's.tareq@almamonuc.edu.iq', 'باحثة أكاديمية', $sampleDept3, 'كادر (employee)', 'password'],
        ];

        $row = 5;
        foreach ($samples as $item) {
            $sheet1->setCellValue("A{$row}", $item[0]);
            $sheet1->setCellValue("B{$row}", $item[1]);
            $sheet1->setCellValue("C{$row}", $item[2]);
            $sheet1->setCellValue("D{$row}", $item[3]);
            $sheet1->setCellValue("E{$row}", $item[4]);
            $sheet1->setCellValue("F{$row}", $item[5]);

            $rowStyle = [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ];
            $sheet1->getStyle("A{$row}:F{$row}")->applyFromArray($rowStyle);
            $sheet1->getRowDimension($row)->setRowHeight(22);
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // --- Sheet 2: Reference of Existing Departments ---
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('الأقسام المتاحة (Departments)');
        $sheet2->setRightToLeft(true);

        $sheet2->setCellValue('A1', 'قائمة الكليات والأقسام المسجلة في المنظومة (يمكنك نسخ اسم القسم ولصقه في ورقة الكوادر)');
        $sheet2->mergeCells('A1:D1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0D9488'));
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $deptHeaders = [
            'A3' => '# (ID)',
            'B3' => 'اسم الكلية / القسم (Department Name)',
            'C3' => 'رئيس القسم المسؤول (Current Manager)',
            'D3' => 'ساعات الدوام الرسمي (Working Shift)',
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
            $sheet2->setCellValue("C{$dRow}", $d->manager?->name ?? 'غير محدد');
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

    /**
     * Import users & employees in bulk from uploaded Excel/CSV file.
     */
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
            return back()->with('error', 'الملف المرفوع فارغ أو لا يحتوي على بيانات مطابقة للنموذج.');
        }

        // Detect header row index
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

        // Cache existing departments
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

            // Skip empty or purely instruction rows
            if (empty($name) && empty($email)) {
                continue;
            }

            if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skippedCount++;
                continue;
            }

            // Match or auto-create Department
            $departmentId = null;
            if (!empty($deptName)) {
                $matchedDept = $departments->first(function ($d) use ($deptName) {
                    return mb_strtolower(trim($d->name)) === mb_strtolower($deptName)
                        || str_contains(mb_strtolower($d->name), mb_strtolower($deptName));
                });

                if ($matchedDept) {
                    $departmentId = $matchedDept->id;
                } else {
                    // Auto-create department
                    $newDept = Department::create([
                        'name' => $deptName,
                        'work_start_time' => '08:00:00',
                        'work_end_time' => '15:30:00',
                    ]);
                    $departments->push($newDept);
                    $departmentId = $newDept->id;
                }
            }

            // Map Role
            $role = 'employee';
            if (str_contains($roleInput, 'head') || str_contains($roleInput, 'رئيس') || str_contains($roleInput, 'عميد')) {
                $role = 'head';
            } elseif (str_contains($roleInput, 'admin') || str_contains($roleInput, 'رئاسة') || str_contains($roleInput, 'مشرف') || str_contains($roleInput, 'أدمن')) {
                $role = 'admin';
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                // Update existing user
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
                // Create new user
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'job_title' => $jobTitle ?: null,
                    'department_id' => $departmentId,
                    'role' => $role,
                    'password' => Hash::make(!empty($password) ? $password : 'password'),
                    'is_active' => true,
                ]);
                $createdCount++;
            }
        }

        $feedbackMsg = "اكتملت المعالجة بنجاح! تم إنشاء {$createdCount} كادر جديد، وتحديث {$updatedCount} حساب.";
        if ($skippedCount > 0) {
            $feedbackMsg .= " (تم تخطي {$skippedCount} سجلات لعدم اكتمال بياناتها).";
        }

        return back()->with('success', $feedbackMsg);
    }
}