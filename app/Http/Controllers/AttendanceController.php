<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;
use App\Models\AttendanceLog;
use App\Models\AttendanceLocationPoint;
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

        // If user is in fixed location mode configured by Admin, use the Admin-assigned coordinates
        if ($user->attendance_mode === 'fixed' && !empty($user->fixed_latitude) && !empty($user->fixed_longitude)) {
            $latitude = $user->fixed_latitude;
            $longitude = $user->fixed_longitude;
            $notes = 'حضور موقع ثابت معتمد (' . ($user->fixed_location_name ?: 'المقر المعتمد') . ')';
        } else {
            // Strictly require live GPS coordinates
            $validated = $request->validate([
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'accuracy' => ['nullable', 'numeric'],
            ]);
            $latitude = $validated['latitude'];
            $longitude = $validated['longitude'];
            $accuracyText = isset($validated['accuracy']) ? ' (دقة: ±' . round($validated['accuracy']) . 'م)' : '';
            $notes = 'حضور GPS حي ومباشر' . $accuracyText;
        }

        $today = Carbon::today()->toDateString();
        $log = AttendanceLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'log_date' => $today,
            ],
            [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'log_time' => Carbon::now()->toTimeString(),
                'notes' => $notes,
            ]
        );

        // Record location breadcrumb point if moved or after interval
        $lastPoint = AttendanceLocationPoint::where('user_id', $user->id)
            ->whereDate('recorded_at', $today)
            ->latest('id')
            ->first();

        $shouldRecord = false;
        if (!$lastPoint) {
            $shouldRecord = true;
        } else {
            // Calculate approximate distance moved (Haversine formula in PHP)
            $lat1 = deg2rad($lastPoint->latitude);
            $lon1 = deg2rad($lastPoint->longitude);
            $lat2 = deg2rad($latitude);
            $lon2 = deg2rad($longitude);
            $dLat = $lat2 - $lat1;
            $dLon = $lon2 - $lon1;
            $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * (sin($dLon / 2) ** 2);
            $distanceMeters = 6371000 * 2 * asin(sqrt($a));

            // Record if moved > 15 meters or if 3+ minutes passed
            if ($distanceMeters >= 15 || $lastPoint->created_at->diffInMinutes(now()) >= 3) {
                $shouldRecord = true;
            }
        }

        if ($shouldRecord) {
            AttendanceLocationPoint::create([
                'user_id' => $user->id,
                'attendance_log_id' => $log->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $validated['accuracy'] ?? null,
                'recorded_at' => now(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل وتحديث الحضور بنجاح',
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

    public function trail(Request $request, User $user): JsonResponse
    {
        $auth = $request->user();
        if ($auth->role === 'employee' && $auth->id !== $user->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }
        if ($auth->role === 'head' && $user->department_id !== $auth->department_id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $date = $request->input('date', Carbon::today()->toDateString());
        $points = AttendanceLocationPoint::where('user_id', $user->id)
            ->whereDate('recorded_at', $date)
            ->orderBy('recorded_at', 'asc')
            ->get(['id', 'latitude', 'longitude', 'accuracy', 'recorded_at'])
            ->map(fn($p) => [
                'id' => $p->id,
                'latitude' => (float) $p->latitude,
                'longitude' => (float) $p->longitude,
                'accuracy' => $p->accuracy,
                'time' => $p->recorded_at->format('H:i:s'),
                'time_human' => $p->recorded_at->diffForHumans(),
            ]);

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'job_title' => $user->job_title,
                'department' => $user->department?->name,
            ],
            'date' => $date,
            'points' => $points,
            'total_points' => $points->count(),
        ]);
    }

    public function liveLocations(Request $request): JsonResponse
    {
        $auth = $request->user();
        $date = $request->input('date', Carbon::today()->toDateString());

        $query = AttendanceLog::with('user.department')
            ->whereDate('log_date', $date);

        if ($auth->role === 'head') {
            $query->whereHas('user', fn($q) => $q->where('department_id', $auth->department_id));
        }

        $now = Carbon::now();

        $points = $query->get()->map(function ($log) use ($now) {
            $lastSync = $log->updated_at ?: $log->created_at;
            $diffSeconds = $lastSync ? $now->diffInSeconds($lastSync) : 99999;
            $status = 'offline';
            if ($diffSeconds < 180) {
                $status = 'active';
            } elseif ($diffSeconds < 900) {
                $status = 'recent';
            }

            return [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'user_name' => $log->user?->name,
                'job_title' => $log->user?->job_title,
                'department_name' => $log->user?->department?->name,
                'latitude' => (float) $log->latitude,
                'longitude' => (float) $log->longitude,
                'log_time' => $log->log_time,
                'last_seen_seconds' => $diffSeconds,
                'status' => $status,
                'notes' => $log->notes,
            ];
        });

        return response()->json([
            'status' => 'success',
            'date' => $date,
            'points' => $points,
            'active_count' => $points->where('status', 'active')->count(),
            'total_present' => $points->count(),
        ]);
    }
}
