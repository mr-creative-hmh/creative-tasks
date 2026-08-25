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
        $departments = Department::all();
        $employees = User::where('role', 'employee')->get();

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
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $now = Carbon::now();
        $today = $now->toDateString();

        // Auto-update or create attendance record for today
        $log = AttendanceLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'log_date' => $today,
            ],
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'log_time' => $now->toTimeString(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل وتحديث الحضور وإحداثيات الموقع تلقائياً بنجاح.',
            'log' => $log,
        ]);
    }

    public function manualUpdate(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'head'])) {
            return response()->json(['message' => 'غير مصرح لك بتعديل الموقع يدوياً.'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'date' => ['nullable', 'date'],
        ]);

        $date = $validated['date'] ?? Carbon::today()->toDateString();
        $now = Carbon::now();

        $log = AttendanceLog::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'log_date' => $date,
            ],
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'log_time' => $now->toTimeString(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث موقع وتواجد الموظف يدوياً بنجاح عبر الخريطة.',
            'log' => $log->load('user.department'),
        ]);
    }
}
