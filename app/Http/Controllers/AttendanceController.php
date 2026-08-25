<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $departmentId = $request->input('department_id');
        if ($user->role === 'head') {
            $departmentId = $user->department_id;
        }

        $userId = $request->input('user_id');
        $date = $request->input('date', Carbon::today()->toDateString());

        $query = AttendanceLog::with('user.department');

        if ($departmentId) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $departmentId));
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($date) {
            $query->whereDate('log_date', $date);
        }

        $logs = $query->latest('id')->paginate(20)->withQueryString();
        
        $departmentsQuery = Department::query();
        $employeesQuery = User::where('role', 'employee');
        
        if ($user->role === 'head') {
            $departmentsQuery->where('id', $user->department_id);
            $employeesQuery->where('department_id', $user->department_id);
        }

        $departments = $departmentsQuery->get();
        $employees = $employeesQuery->get();

        // Coordinates for map pin plotting
        $mapPoints = (clone $query)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'user_name' => $log->user?->name,
                'department_name' => $log->user?->department?->name,
                'latitude' => (float) $log->latitude,
                'longitude' => (float) $log->longitude,
                'log_date' => $log->log_date ? $log->log_date->format('Y-m-d') : '',
                'log_time' => $log->log_time,
            ];
        });

        return Inertia::render('Attendance/Index', [
            'logs' => $logs,
            'mapPoints' => $mapPoints,
            'departments' => $departments,
            'employees' => $employees,
            'filters' => [
                'department_id' => $departmentId,
                'user_id' => $userId,
                'date' => $date,
            ],
            'canManualEdit' => $user->role === 'admin',
            'allEmployees' => $employees,
            'stats' => [
                'total_present_today' => AttendanceLog::whereDate('log_date', Carbon::today())->distinct('user_id')->count('user_id'),
                'total_employees' => User::where('role', 'employee')->count(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric'],
        ]);

        $log = AttendanceLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'log_date' => Carbon::today()->toDateString(),
            ],
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'log_time' => Carbon::now()->toTimeString(),
                'notes' => 'تتبع حضور ميداني ذكي (GPS Live Auto-Sync)',
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم توثيق الحضور وتحديث الموقع الميداني بنجاح',
            'log' => $log,
        ]);
    }

    public function manualUpdate(Request $request): JsonResponse
    {
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بهذا الإجراء. هذه الصلاحية خاصة بالمدير العام فقط.'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        $log = AttendanceLog::updateOrCreate(
            [
                'user_id' => $targetUser->id,
                'log_date' => Carbon::today()->toDateString(),
            ],
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'log_time' => Carbon::now()->toTimeString(),
                'notes' => 'تثبيت يدوي من قبل الإدارة العامة (Admin Manual Pin)',
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تثبيت وتحديث موقع الموظف بنجاح من قبل الإدارة',
            'log' => $log,
        ]);
    }
}
