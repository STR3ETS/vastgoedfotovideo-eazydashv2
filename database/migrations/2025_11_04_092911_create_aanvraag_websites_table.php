<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aanvraag_websites', function (Blueprint $table) {
            $table->id();

            // Vanuit jouw validatie
            $table->string('choice', 20)->nullable();               // 'new' | 'renew' (optioneel)
            $table->string('url', 2048)->nullable();
            $table->string('company', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('goal')->nullable();
            $table->string('example1', 2048)->nullable();
            $table->string('example2', 2048)->nullable();

            $table->string('contactName', 255);                     // required
            $table->string('contactEmail', 255);                    // required
            $table->string('contactPhone', 50);                     // required

            // Visit tracking uit cookie
            $table->string('visit_id', 64)->nullable()->index();

            $table->timestamps();

            // Handige indexen
            $table->index('contactEmail');
            $table->index('choice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aanvraag_websites');
    }
};
