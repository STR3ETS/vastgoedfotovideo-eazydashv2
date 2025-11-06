<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aanvraag_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aanvraag_website_id')
                  ->constrained('aanvraag_websites')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32);
            $table->timestamp('changed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aanvraag_status_logs');
    }
};
