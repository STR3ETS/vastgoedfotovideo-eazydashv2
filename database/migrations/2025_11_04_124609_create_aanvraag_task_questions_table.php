<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aanvraag_task_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aanvraag_task_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->text('answer')->nullable();      // wat sales invult tijdens call
            $table->boolean('required')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aanvraag_task_questions');
    }
};
