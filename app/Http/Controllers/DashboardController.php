<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Task;
use App\Models\Department;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();
        
        $departmentId = $request->input('department_id');
        if ($user->role === 'head') {
            $departmentId = $user->department_id;
        }

        // Query Tasks
        $taskQuery = Task::query()->whereDate('task_date', $today);
        if ($departmentId) {
            $taskQuery->where('department_id', $departmentId);
        }

        $totalTasks = (clone $taskQuery)->count();
        $completedTasks = (clone $taskQuery)->where('status', 'completed')->count();
        $inProgressTasks = (clone $taskQuery)->where('status', 'in_progress')->count();
        $pendingTasks = (clone $taskQuery)->where('status', 'pending')->count();
        $avgProgress = $totalTasks > 0 ? round((clone $taskQuery)->avg('progress')) : 0;

        // Query Attendance Logs
        $attendanceQuery = AttendanceLog::with('user.department')->whereDate('log_date', $today);
        if ($departmentId) {
            $attendanceQuery->whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        $todayAttendanceCount = (clone $attendanceQuery)->distinct('user_id')->count('user_id');
        $recentAttendanceLogs = (clone $attendanceQuery)->latest('id')->limit(8)->get();

        // Department list for filters
        $departments = Department::withCount('users')->get();

        // Recent tasks
        $recentTasks = (clone $taskQuery)->with(['user', 'department', 'assigner'])->latest('id')->limit(8)->get();

        // Department breakdown stats
        $departmentStats = Department::withCount([
            'users',
            'tasks as today_tasks_count' => fn($q) => $q->whereDate('task_date', $today),
            'tasks as completed_tasks_count' => fn($q) => $q->whereDate('task_date', $today)->where('status', 'completed'),
        ])->get();

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'pending_tasks' => $pendingTasks,
                'avg_progress' => $avgProgress,
                'today_attendance_count' => $todayAttendanceCount,
            ],
            'departments' => $departments,
            'departmentStats' => $departmentStats,
            'recentTasks' => $recentTasks,
            'recentAttendanceLogs' => $recentAttendanceLogs,
            'selectedDepartmentId' => $departmentId,
        ]);
    }
}
