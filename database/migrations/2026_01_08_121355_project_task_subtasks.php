<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_task_subtasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();

            $table->string('name');
            $table->string('status')->default('active'); // pending|active|done|cancelled|archived
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['project_task_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_subtasks');
    }
};
