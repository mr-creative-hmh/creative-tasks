<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\Task;
use App\Models\Department;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class EmployeeTaskController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        // Check if attendance exists today
        $todayAttendance = AttendanceLog::where('user_id', $user->id)
            ->whereDate('log_date', $today)
            ->latest('id')
            ->first();

        // Get assigned tasks for today
        $assignedTasks = Task::with('assigner')
            ->where('user_id', $user->id)
            ->where('task_type', 'assigned')
            ->whereDate('task_date', $today)
            ->latest('id')
            ->get();

        // Get self-reported tasks for today
        $selfReportedTasks = Task::where('user_id', $user->id)
            ->where('task_type', 'self_reported')
            ->whereDate('task_date', $today)
            ->latest('id')
            ->get();

        // Summary metrics
        $allTodayTasks = Task::where('user_id', $user->id)->whereDate('task_date', $today)->get();
        $totalCount = $allTodayTasks->count();
        $completedCount = $allTodayTasks->where('status', 'completed')->count();
        $avgProgress = $totalCount > 0 ? round($allTodayTasks->avg('progress')) : 0;

        // Department fallback for admins/users without direct department
        $department = $user->department ?? Department::first();

        return Inertia::render('Employee/Dashboard', [
            'assignedTasks' => $assignedTasks,
            'selfReportedTasks' => $selfReportedTasks,
            'todayAttendance' => $todayAttendance,
            'summary' => [
                'total' => $totalCount,
                'completed' => $completedCount,
                'avg_progress' => $avgProgress,
                'today_date' => $today,
            ],
            'department' => $department,
        ]);
    }

    public function updateProgress(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        if ($task->user_id !== $user->id && !in_array($user->role, ['admin', 'head'])) {
            abort(403, 'Unauthorized task modification.');
        }

        $validated = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $progress = (int) $validated['progress'];
        $status = 'pending';
        if ($progress === 100) {
            $status = 'completed';
        } elseif ($progress > 0) {
            $status = 'in_progress';
        }

        $task->update([
            'progress' => $progress,
            'status' => $status,
        ]);

        return back()->with('success', 'تم تحديث نسبة إنجاز المهمة بنجاح.');
    }

    public function storeSelfReported(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $deptId = $user->department_id ?? Department::first()?->id;

        $progress = isset($validated['progress']) ? (int) $validated['progress'] : 100;
        $status = $progress === 100 ? 'completed' : ($progress > 0 ? 'in_progress' : 'pending');

        Task::create([
            'department_id' => $deptId,
            'user_id' => $user->id,
            'assigned_by' => null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'progress' => $progress,
            'task_type' => 'self_reported',
            'status' => $status,
            'task_date' => Carbon::today()->toDateString(),
        ]);

        return back()->with('success', 'تمت إضافة العمل اليومي بنجاح.');
    }
}
