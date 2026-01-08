<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_task_message_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_task_message_id')
                ->constrained('project_task_messages')
                ->cascadeOnDelete();

            $table->string('disk')->default('public');
            $table->string('path'); // storage path
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->timestamps();

            $table->index(['project_task_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_message_attachments');
    }
};
