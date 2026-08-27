<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'job_title',
        'email',
        'password',
        'department_id',
        'role',
        'is_active',
        'attendance_mode',
        'fixed_latitude',
        'fixed_longitude',
        'fixed_location_name',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'fixed_latitude' => 'float',
            'fixed_longitude' => 'float',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHead(): bool
    {
        return $this->role === 'head';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isFixedLocation(): bool
    {
        return $this->attendance_mode === 'fixed';
    }

    public function locationPoints(): HasMany
    {
        return $this->hasMany(AttendanceLocationPoint::class);
    }
}