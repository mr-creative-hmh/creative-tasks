<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\AttendanceLog;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $todayAttendance = null;
        $activeDepartment = null;

        if ($user) {
            $user->loadMissing('department');
            $activeDepartment = $user->department;
            
            $todayAttendance = AttendanceLog::where('user_id', $user->id)
                ->whereDate('log_date', now()->toDateString())
                ->latest('id')
                ->first();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'job_title' => $user->job_title,
                    'email' => $user->email,
                    'role' => $user->role,
                    'department_id' => $user->department_id,
                    'department' => $user->department ? [
                        'id' => $user->department->id,
                        'name' => $user->department->name,
                        'work_start_time' => $user->department->work_start_time,
                        'work_end_time' => $user->department->work_end_time,
                    ] : null,
                    'is_active' => $user->is_active,
                ] : null,
            ],
            'activeDepartment' => $activeDepartment,
            'todayAttendance' => $todayAttendance,
            'locale' => session('locale', config('app.locale', 'ar')),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
