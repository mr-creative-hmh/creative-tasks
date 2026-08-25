<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Response as HttpResponse;
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

        $tasks = (clone $query)->latest('task_date')->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $inProgressTasks = $tasks->where('status', 'in_progress')->count();
        $pendingTasks = $tasks->where('status', 'pending')->count();
        $avgProgress = $totalTasks > 0 ? round($tasks->avg('progress'), 1) : 0;

        // Group by employee for performance summary
        $employeePerformance = $tasks->groupBy('user_id')->map(function ($employeeTasks) {
            $emp = $employeeTasks->first()->user;
            $empTotal = $employeeTasks->count();
            $empCompleted = $employeeTasks->where('status', 'completed')->count();
            $empAvg = $empTotal > 0 ? round($employeeTasks->avg('progress'), 1) : 0;
            return [
                'user_id' => $emp?->id,
                'user_name' => $emp?->name,
                'department_name' => $emp?->department?->name,
                'total_tasks' => $empTotal,
                'completed_tasks' => $empCompleted,
                'avg_progress' => $empAvg,
            ];
        })->values();

        $departments = Department::all();
        $employees = User::where('role', 'employee')->get();

        return Inertia::render('Reports/Index', [
            'tasks' => $tasks,
            'summary' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'in_progress' => $inProgressTasks,
                'pending' => $pendingTasks,
                'avg_progress' => $avgProgress,
            ],
            'employeePerformance' => $employeePerformance,
            'departments' => $departments,
            'employees' => $employees,
            'filters' => [
                'department_id' => $departmentId,
                'user_id' => $userId,
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

        $tasks = $query->latest('task_date')->get();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $avgProgress = $totalTasks > 0 ? round($tasks->avg('progress'), 1) : 0;

        $department = $departmentId ? Department::find($departmentId) : null;
        $employee = $userId ? User::find($userId) : null;

        $pdf = Pdf::loadView('reports.task_performance_pdf', [
            'tasks' => $tasks,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'avgProgress' => $avgProgress,
            'department' => $department,
            'employee' => $employee,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'generatedBy' => $user->name,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return $pdf->download("task-performance-report-{$dateFrom}-to-{$dateTo}.pdf");
    }
}
