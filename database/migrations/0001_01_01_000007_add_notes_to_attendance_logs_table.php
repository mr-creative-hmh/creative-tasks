<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_logs') && !Schema::hasColumn('attendance_logs', 'notes')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->string('notes')->nullable()->after('log_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_logs') && Schema::hasColumn('attendance_logs', 'notes')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};