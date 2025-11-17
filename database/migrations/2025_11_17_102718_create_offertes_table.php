<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('offertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->string('public_uuid')->unique();
            $table->string('status')->default('draft'); // bv. draft / sent / accepted / rejected

            // Hier komt alle AI-gegenereerde content in
            $table->json('generated')->nullable();

            // Later kun je hier ook nummer, datums, bedragen etc. toevoegen
            // $table->string('number')->nullable();
            // $table->date('offer_date')->nullable();
            // $table->date('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offertes');
    }
};

