<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class CheckShiftAndLocation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'employee') {
            $dept = $user->department;
            if ($dept && $dept->work_start_time && $dept->work_end_time) {
                $now = Carbon::now();
                $currentTime = $now->format('H:i:s');
                
                // Allow buffer or strict check
                $start = $dept->work_start_time;
                $end = $dept->work_end_time;
                
                $isShiftActive = true;
                if ($start <= $end) {
                    $isShiftActive = ($currentTime >= $start && $currentTime <= $end);
                } else {
                    // Overnight shift
                    $isShiftActive = ($currentTime >= $start || $currentTime <= $end);
                }

                // If outside shift hours, attach warning or redirect if strict
                $request->attributes->set('is_outside_shift', !$isShiftActive);
            }
        }

        return $next($request);
    }
}
