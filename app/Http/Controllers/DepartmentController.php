<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\Department;
use App\Models\User;

class DepartmentController extends Controller
{
    public function index(): Response
    {
        $departments = Department::with(['manager', 'users'])
            ->withCount('users')
            ->get();

        $heads = User::where('role', 'head')->where('is_active', true)->get();

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
            'heads' => $heads,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
        ]);

        $dept = Department::create($validated);

        if (!empty($validated['manager_id'])) {
            User::where('id', $validated['manager_id'])->update(['department_id' => $dept->id]);
        }

        return back()->with('success', 'تم إنشاء القسم بنجاح.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'work_start_time' => ['required', 'date_format:H:i'],
            'work_end_time' => ['required', 'date_format:H:i'],
        ]);

        $department->update($validated);

        if (!empty($validated['manager_id'])) {
            User::where('id', $validated['manager_id'])->update(['department_id' => $department->id]);
        }

        return back()->with('success', 'تم تحديث بيانات القسم وساعات العمل بنجاح.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->users()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف القسم لوجود موظفين مرتبطين به.');
        }

        $department->delete();

        return back()->with('success', 'تم حذف القسم بنجاح.');
    }
}
