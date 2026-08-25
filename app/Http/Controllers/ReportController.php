<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Task;
use App\Models\Department;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $departmentId = $request->input('department_id');
        if ($user->role === 'head') {
            $departmentId = $user->department_id;
        }

        $userId = $request->input('user_id');
        $status = $request->input('status');
        $taskType = $request->input('task_type');
        $dateFrom = $request->input('date_from', Carbon::today()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());

        $query = Task::with(['user.department', 'assigner', 'department'])
            ->whereBetween('task_date', [$dateFrom, $dateTo]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($taskType) {
            $query->where('task_type', $taskType);
        }

        $tasks = (clone $query)->latest('task_date')->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $assignedTypeCount = $tasks->where('task_type', 'assigned')->count();
        $selfTypeCount = $tasks->where('task_type', 'self_reported')->count();
        $avgProgress = $totalTasks > 0 ? round($tasks->avg('progress'), 1) : 0;

        // Group by employee for leaderboard
        $employeePerformance = $tasks->groupBy('user_id')->map(function ($employeeTasks) {
            $emp = $employeeTasks->first()->user;
            $empTotal = $employeeTasks->count();
            $empCompleted = $employeeTasks->where('status', 'completed')->count();
            $empInProgress = $employeeTasks->where('status', 'in_progress')->count();
            $empPending = $employeeTasks->where('status', 'pending')->count();
            $empAvg = $empTotal > 0 ? round($employeeTasks->avg('progress'), 1) : 0;
            return [
                'user_id' => $emp?->id,
                'user_name' => $emp?->name,
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

        $departments = Department::all();
        $employeesQuery = User::where('role', 'employee');
        if ($user->role === 'head') {
            $employeesQuery->where('department_id', $user->department_id);
        }
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
                'department_id' => $departmentId,
                'user_id' => $userId,
                'status' => $status,
                'task_type' => $taskType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function exportPdf(Request $request): HttpResponse
    {
        $user = $request->user();
        $departmentId = $request->input('department_id');
        if ($user->role === 'head') {
            $departmentId = $user->department_id;
        }

        $userId = $request->input('user_id');
        $status = $request->input('status');
        $taskType = $request->input('task_type');
        $dateFrom = $request->input('date_from', Carbon::today()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());

        $query = Task::with(['user.department', 'assigner', 'department'])
            ->whereBetween('task_date', [$dateFrom, $dateTo]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($taskType) {
            $query->where('task_type', $taskType);
        }

        $tasks = $query->latest('task_date')->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $avgProgress = $totalTasks > 0 ? round($tasks->avg('progress'), 1) : 0;

        $department = $departmentId ? Department::find($departmentId) : null;
        $employee = $userId ? User::find($userId) : null;

        // Group summary by department
        $departmentSummary = $tasks->groupBy('department_id')->map(function ($deptTasks) {
            $dept = $deptTasks->first()->department;
            return [
                'name' => $dept?->name ?? 'غير محدد',
                'total' => $deptTasks->count(),
                'completed' => $deptTasks->where('status', 'completed')->count(),
                'avg_progress' => round($deptTasks->avg('progress'), 1),
            ];
        })->values();

        $pdf = Pdf::loadView('reports.task_performance_pdf', [
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
        ])->setPaper('a4', 'portrait');

        return $pdf->download("almamon-performance-report-{$dateFrom}-to-{$dateTo}.pdf");
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $user = $request->user();
        $departmentId = $request->input('department_id');
        if ($user->role === 'head') {
            $departmentId = $user->department_id;
        }

        $userId = $request->input('user_id');
        $status = $request->input('status');
        $taskType = $request->input('task_type');
        $dateFrom = $request->input('date_from', Carbon::today()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());

        $query = Task::with(['user.department', 'assigner', 'department'])
            ->whereBetween('task_date', [$dateFrom, $dateTo]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($taskType) {
            $query->where('task_type', $taskType);
        }

        $tasks = $query->latest('task_date')->get();

        $filename = "almamon-tasks-report-{$dateFrom}-to-{$dateTo}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tasks) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Microsoft Excel compatibility with Arabic
            fputs($handle, "\xEF\xBB\xBF");

            // Header Row
            fputcsv($handle, [
                '#',
                'عنوان المهمة / التكليف',
                'الموظف المكلف',
                'البريد الإلكتروني',
                'الكلية / القسم',
                'نوع المهمة',
                'نسبة الإنجاز (%)',
                'حالة المهمة',
                'جهة التكليف',
                'تاريخ المهمة',
                'التفاصيل والتوجيهات',
            ]);

            foreach ($tasks as $index => $task) {
                $statusLabel = match($task->status) {
                    'completed' => 'مكتملة',
                    'in_progress' => 'قيد التنفيذ',
                    default => 'معلقة',
                };

                $typeLabel = $task->task_type === 'assigned' ? 'تكليف رسمي' : 'عمل ذاتي';

                fputcsv($handle, [
                    $index + 1,
                    $task->title,
                    $task->user?->name ?? 'غير محدد',
                    $task->user?->email ?? '-',
                    $task->department?->name ?? 'بدون قسم',
                    $typeLabel,
                    $task->progress . '%',
                    $statusLabel,
                    $task->assigner?->name ?? 'تسجيل ذاتي',
                    $task->task_date ? $task->task_date->format('Y-m-d') : '',
                    $task->description ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
