<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeTaskController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;

// Public & Auth Routes
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return $user->role === 'employee' 
            ? redirect()->route('employee.tasks') 
            : redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/switch-locale', [AuthController::class, 'switchLocale'])->name('locale.switch');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
    // User Profile & Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Automatic GPS Attendance Store API
    Route::post('/attendance/log', [AttendanceController::class, 'store'])->name('attendance.store');

    // Daily Tasks Portal (Accessible to Employees, Heads, and Admins)
    Route::middleware(['role:admin,head,employee'])->prefix('employee')->name('employee.')->group(function () {
        Route::get('/tasks', [EmployeeTaskController::class, 'index'])->name('tasks');
        Route::patch('/tasks/{task}/progress', [EmployeeTaskController::class, 'updateProgress'])->name('tasks.progress');
        Route::post('/tasks/self-reported', [EmployeeTaskController::class, 'storeSelfReported'])->name('tasks.self-reported');
    });

    // Admin & Head Portal (Executive Dashboard, Delegation, Attendance Map, Reports)
    Route::middleware(['role:admin,head'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Task Management & Delegation
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // Field & GPS Attendance Map + Manual Admin Location Update
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/manual-update', [AttendanceController::class, 'manualUpdate'])->name('attendance.manual-update');

        // Periodic Performance Reports & Printable PDF + Native Excel (.xlsx) Export
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'printReport'])->name('reports.pdf');
        Route::get('/reports/print', [ReportController::class, 'printReport'])->name('reports.print');
        Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    });

    // Admin-Only Routes (Departments & Users Management)
    Route::middleware(['role:admin'])->group(function () {
        // Departments Management
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Users & Staff Management + Bulk Excel Import & Template Download
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/template', [UserController::class, 'downloadTemplate'])->name('users.template');
        Route::post('/users/import', [UserController::class, 'importExcel'])->name('users.import');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
