<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('onboarding_request_id')
                ->unique()
                ->constrained('onboarding_requests')
                ->cascadeOnDelete();

            $table->foreignId('client_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('status')->default('active');      // active/pending/done etc
            $table->string('category')->default('onboarding'); // onboarding/...
            $table->string('template')->nullable();           // optioneel (schakelaar “Sjabloon/Project”)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
