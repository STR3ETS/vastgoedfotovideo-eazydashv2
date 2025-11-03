<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Basis
            $table->string('name');                 // Bedrijfsnaam
            $table->string('trade_name')->nullable(); // Handelsnaam
            $table->string('legal_form')->nullable(); // Rechtsvorm

            // Contact / website
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Adres
            $table->string('street')->nullable();       // Straatnaam
            $table->string('house_number', 16)->nullable(); // Huisnummer (incl. toevoeging)
            $table->string('postal_code', 32)->nullable();  // Postcode
            $table->string('city')->nullable();             // Stad
            $table->char('country_code', 2)->nullable();    // Vestigingsland (ISO-3166 alpha-2, bv 'NL')

            // Registraties
            $table->string('kvk_number')->nullable()->unique(); // KVK-nummer
            $table->string('vat_number')->nullable()->unique(); // BTW-nummer

            $table->timestamps();
            // $table->softDeletes(); // optioneel, als je soft deletes wilt
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
