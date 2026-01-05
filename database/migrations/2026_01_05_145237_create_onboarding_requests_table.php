<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('onboarding_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // stap 1
            $table->string('address');
            $table->string('postcode', 20);
            $table->string('city', 120);
            $table->unsignedInteger('surface_home')->default(0);
            $table->unsignedInteger('surface_outbuildings')->default(0);
            $table->unsignedInteger('surface_plot')->default(0);

            // stap 2
            $table->string('contact_first_name', 100);
            $table->string('contact_last_name', 120);
            $table->string('contact_email');
            $table->string('contact_phone', 40);
            $table->boolean('contact_updates')->default(false);

            $table->string('agency_first_name', 100);
            $table->string('agency_last_name', 120);
            $table->string('agency_email');
            $table->string('agency_phone', 40);

            // stap 3/4
            $table->string('package', 40);
            $table->json('extras')->nullable();

            // stap 5
            $table->date('shoot_date');
            $table->string('shoot_slot', 30);

            // stap 6
            $table->boolean('confirm_truth')->default(false);
            $table->boolean('confirm_terms')->default(false);

            $table->string('status', 20)->default('new'); // new / planned / done / etc.

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_requests');
    }
};
