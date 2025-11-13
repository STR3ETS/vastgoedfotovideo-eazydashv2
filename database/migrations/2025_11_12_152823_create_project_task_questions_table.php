<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_task_id')
                  ->constrained('project_tasks')
                  ->cascadeOnDelete();

            $table->string('question', 255);
            $table->text('answer')->nullable();
            $table->boolean('required')->default(true);
            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_questions');
    }
};
