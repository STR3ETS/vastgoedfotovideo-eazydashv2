<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offertes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            // Voor later publieke link naar klant
            $table->uuid('public_uuid')->nullable()->unique();

            // Concept / klaar om te versturen / verzonden / geaccepteerd etc.
            $table->string('status')->default('draft');

            $table->string('title')->nullable();
            $table->string('reference')->nullable();   // bv. "OFF-2025-001"
            $table->date('valid_until')->nullable();

            // Optioneel: bedragen
            $table->decimal('total_ex_vat', 10, 2)->nullable();
            $table->decimal('total_incl_vat', 10, 2)->nullable();

            // De daadwerkelijke tekst/HTML van de offerte (bewerkbaar)
            $table->longText('body')->nullable();

            // Extra data (intake, offertegesprek e.d.)
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offertes');
    }
};
