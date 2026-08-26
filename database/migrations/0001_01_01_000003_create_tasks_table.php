<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->enum('task_type', ['assigned', 'self_reported'])->default('assigned');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->date('task_date');
            $table->timestamps();

            $table->index(['department_id', 'task_date']);
            $table->index(['user_id', 'task_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
