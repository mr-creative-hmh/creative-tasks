<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('attendance_mode')->default('gps')->after('is_active');
            $table->decimal('fixed_latitude', 10, 8)->nullable()->after('attendance_mode');
            $table->decimal('fixed_longitude', 11, 8)->nullable()->after('fixed_latitude');
            $table->string('fixed_location_name')->nullable()->after('fixed_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['attendance_mode', 'fixed_latitude', 'fixed_longitude', 'fixed_location_name']);
        });
    }
};
