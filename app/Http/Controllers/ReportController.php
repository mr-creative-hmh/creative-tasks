<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Task;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportController extends Controller
{
    private function buildQuery(Request $request, $user)
    {
        $query = Task::with(['user.department', 'assigner', 'department']);

        // Strictly enforce Department for Head
        if ($user->role === 'head') {
            $query->where('department_id', $user->department_id);
        } elseif ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->input('task_type'));
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Robust date matching using whereDate
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereDate('task_date', '>=', $dateFrom)
                  ->whereDate('task_date', '<=', $dateTo);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('task_date', '>=', $dateFrom);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('task_date', '<=', $dateTo);
        }

        return $query;
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $dateFrom = $request->input('date_from', Carbon::today()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());

        $query = $this->buildQuery($request, $user);
        $tasks = (clone $query)->latest('task_date')->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $assignedTypeCount = $tasks->where('task_type', 'assigned')->count();
        $selfTypeCount = $tasks->where('task_type', 'self_reported')->count();
        $avgProgress = $totalTasks > 0 ? round($tasks->avg('progress'), 1) : 0;

        // Group by employee for performance leaderboard
        $employeePerformance = $tasks->groupBy('user_id')->map(function ($employeeTasks) {
            $emp = $employeeTasks->first()->user;
            $empTotal = $employeeTasks->count();
            $empCompleted = $employeeTasks->where('status', 'completed')->count();
            $empInProgress = $employeeTasks->where('status', 'in_progress')->count();
            $empPending = $employeeTasks->where('status', 'pending')->count();
            $empAvg = $empTotal > 0 ? round($employeeTasks->avg('progress'), 1) : 0;
            return [
                'user_id' => $emp?->id,
                'user_name' => $emp?->name ?? 'غير محدد',
                'department_name' => $emp?->department?->name ?? 'بدون قسم',
                'total_tasks' => $empTotal,
                'completed_tasks' => $empCompleted,
                'in_progress_tasks' => $empInProgress,
                'pending_tasks' => $empPending,
                'avg_progress' => $empAvg,
            ];
        })->values()->sortByDesc('avg_progress')->values();

        // Group by department for department chart
        $departmentPerformance = $tasks->groupBy('department_id')->map(function ($deptTasks) {
            $dept = $deptTasks->first()->department;
            $deptTotal = $deptTasks->count();
            $deptCompleted = $deptTasks->where('status', 'completed')->count();
            $deptAvg = $deptTotal > 0 ? round($deptTasks->avg('progress'), 1) : 0;
            return [
                'department_id' => $dept?->id,
                'department_name' => $dept?->name ?? 'عام / غير مصنف',
                'total_tasks' => $deptTotal,
                'completed_tasks' => $deptCompleted,
                'avg_progress' => $deptAvg,
            ];
        })->values();

        $departmentsQuery = Department::query();
        $employeesQuery = User::where('role', 'employee');

        if ($user->role === 'head') {
            $departmentsQuery->where('id', $user->department_id);
            $employeesQuery->where('department_id', $user->department_id);
        }

        $departments = $departmentsQuery->get();
        $employees = $employeesQuery->get();

        return Inertia::render('Reports/Index', [
            'tasks' => $tasks,
            'summary' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'in_progress' => $inProgressTasks,
                'pending' => $pendingTasks,
                'assigned_type' => $assignedTypeCount,
                'self_type' => $selfTypeCount,
                'avg_progress' => $avgProgress,
            ],
            'employeePerformance' => $employeePerformance,
            'departmentPerformance' => $departmentPerformance,
            'departments' => $departments,
            'employees' => $employees,
            'filters' => [
                'department_id' => $user->role === 'head' ? $user->department_id : $request->input('department_id', ''),
                'user_id' => $request->input('user_id', ''),
                'status' => $request->input('status', ''),
                'task_type' => $request->input('task_type', ''),
                'date_from' => $request->has('date_from') ? $request->input('date_from') : $dateFrom,
                'date_to' => $request->has('date_to') ? $request->input('date_to') : $dateTo,
            ],
        ]);
    }

    public function printReport(Request $request)
    {
        $user = $request->user();
        $query = $this->buildQuery($request, $user);
        $tasks = $query->latest('task_date')->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $avgProgress = $totalTasks > 0 ? round($tasks->avg('progress'), 1) : 0;

        $departmentId = $user->role === 'head' ? $user->department_id : $request->input('department_id');
        $department = $departmentId ? Department::find($departmentId) : null;
        $employee = $request->filled('user_id') ? User::find($request->input('user_id')) : null;

        $departmentSummary = $tasks->groupBy('department_id')->map(function ($deptTasks) {
            $dept = $deptTasks->first()->department;
            return [
                'name' => $dept?->name ?? 'غير محدد',
                'total' => $deptTasks->count(),
                'completed' => $deptTasks->where('status', 'completed')->count(),
                'avg_progress' => round($deptTasks->avg('progress'), 1),
            ];
        })->values();

        $dateFrom = $request->input('date_from', 'كافة الفترات');
        $dateTo = $request->input('date_to', 'اليوم');

        return view('reports.printable_report', [
            'tasks' => $tasks,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'inProgressTasks' => $inProgressTasks,
            'pendingTasks' => $pendingTasks,
            'avgProgress' => $avgProgress,
            'department' => $department,
            'employee' => $employee,
            'departmentSummary' => $departmentSummary,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'generatedBy' => $user->name,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i'),
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $user = $request->user();
        $query = $this->buildQuery($request, $user);
        $tasks = $query->latest('task_date')->get();

        $dateFrom = $request->input('date_from', 'كافة الفترات');
        $dateTo = $request->input('date_to', 'اليوم');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('تقرير المهام المعتمد');
        $sheet->setRightToLeft(true);

        // Header Title
        $sheet->setCellValue('A1', 'جامعة المأمون - تقرير الأداء ومتابعة إنجاز المهام المؤسسية');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0369A1'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Meta info
        $deptName = $user->role === 'head' ? ($user->department?->name ?? 'القسم') : 'كافة الأقسام';
        $sheet->setCellValue('A2', "الفترة: من {$dateFrom} إلى {$dateTo} | الكلية/القسم: {$deptName} | تاريخ السحب: " . date('Y-m-d H:i') . " | المشرف: {$user->name}");
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Column Headers
        $headers = [
            'A4' => '#',
            'B4' => 'عنوان المهمة / التكليف',
            'C4' => 'الموظف المكلف',
            'D4' => 'البريد الإلكتروني',
            'E4' => 'الكلية / القسم',
            'F4' => 'نوع المهمة',
            'G4' => 'نسبة الإنجاز',
            'H4' => 'حالة المهمة',
            'I4' => 'جهة التكليف',
            'J4' => 'تاريخ المهمة',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0369A1'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle('A4:J4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Fill Data Rows
        $row = 5;
        foreach ($tasks as $index => $task) {
            $statusLabel = match($task->status) {
                'completed' => 'مكتملة',
                'in_progress' => 'قيد التنفيذ',
                default => 'معلقة',
            };

            $typeLabel = $task->task_type === 'assigned' ? 'تكليف رسمي' : 'عمل ذاتي';

            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $task->title);
            $sheet->setCellValue("C{$row}", $task->user?->name ?? 'غير محدد');
            $sheet->setCellValue("D{$row}", $task->user?->email ?? '-');
            $sheet->setCellValue("E{$row}", $task->department?->name ?? 'بدون قسم');
            $sheet->setCellValue("F{$row}", $typeLabel);
            $sheet->setCellValue("G{$row}", $task->progress . '%');
            $sheet->setCellValue("H{$row}", $statusLabel);
            $sheet->setCellValue("I{$row}", $task->assigner?->name ?? 'تسجيل ذاتي');
            $sheet->setCellValue("J{$row}", $task->task_date ? $task->task_date->format('Y-m-d') : '-');

            // Alternating Row Colors
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }

            // Alignments
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Auto size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "almamon-tasks-report-" . date('Y-m-d') . ".xlsx";

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
