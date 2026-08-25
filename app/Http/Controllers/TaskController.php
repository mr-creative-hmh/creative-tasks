<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\Task;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class TaskController extends Controller
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
        $date = $request->input('date', Carbon::today()->toDateString());

        $query = Task::with(['user.department', 'assigner', 'department']);

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
        if ($date) {
            $query->whereDate('task_date', $date);
        }

        $tasks = $query->latest('id')->paginate(15)->withQueryString();

        // Fetch departments and eligible employees for task assignment
        $departments = Department::with('users')->get();
        $employeesQuery = User::where('role', 'employee')->where('is_active', true);
        if ($user->role === 'head') {
            $employeesQuery->where('department_id', $user->department_id);
        }
        $employees = $employeesQuery->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'departments' => $departments,
            'employees' => $employees,
            'filters' => [
                'department_id' => $departmentId,
                'user_id' => $userId,
                'status' => $status,
                'task_type' => $taskType,
                'date' => $date,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'task_date' => ['required', 'date'],
        ]);

        $assignee = User::findOrFail($validated['user_id']);
        
        // If head, ensure assignee belongs to their department
        if ($user->role === 'head' && $assignee->department_id !== $user->department_id) {
            abort(403, 'لا يمكنك تعيين مهام لموظف خارج قسمك.');
        }

        Task::create([
            'department_id' => $assignee->department_id,
            'user_id' => $assignee->id,
            'assigned_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'progress' => 0,
            'task_type' => 'assigned',
            'status' => 'pending',
            'task_date' => $validated['task_date'],
        ]);

        return back()->with('success', 'تم تعيين المهمة بنجاح.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();

        // If head, ensure task is in their department
        if ($user->role === 'head' && $task->department_id !== $user->department_id) {
            abort(403, 'غير مصرح.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'task_date' => ['required', 'date'],
        ]);

        $task->update($validated);

        return back()->with('success', 'تم تعديل المهمة بنجاح.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        if ($user->role === 'head' && $task->department_id !== $user->department_id) {
            abort(403, 'غير مصرح.');
        }

        $task->delete();

        return back()->with('success', 'تم حذف المهمة بنجاح.');
    }
}
