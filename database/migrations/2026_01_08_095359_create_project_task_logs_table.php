<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('project_task_id')
                ->constrained('project_tasks')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // bijv: description_filled / description_updated / description_cleared
            $table->string('event', 80);

            // optioneel: welk veld
            $table->string('field', 80)->nullable();

            // optioneel: audit (kan ook null blijven)
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            $table->timestamps();

            $table->index(['project_task_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_logs');
    }
};
