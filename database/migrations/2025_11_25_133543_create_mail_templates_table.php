<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();

            // Weergavenaam van de template
            $table->string('name');

            // Categorie: nieuwsbrief, actie, aanbod, onboarding, opvolg
            $table->enum('category', [
                'nieuwsbrief',
                'actie',
                'aanbod',
                'onboarding',
                'opvolg',
            ]);

            // HTML-inhoud van de e-mail
            $table->longText('html');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
