<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seo_projects', function (Blueprint $table) {
            $table->id();

            // Verplichte koppeling naar Company
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Optionele naam, bv. "Top Woning Ontruiming SEO traject"
            $table->string('name')->nullable();

            // Primair domein
            $table->string('domain');

            // Eventuele extra domeinen, subdomeinen, mirrors
            $table->json('extra_domains')->nullable();

            // Focusregio’s, bv. ["Arnhem", "Gelderland"]
            $table->json('regions')->nullable();

            // Business doelen, als JSON array of object
            $table->json('business_goals')->nullable();

            // Belangrijkste zoekwoorden, bv. ["woningontruiming arnhem", ...]
            $table->json('primary_keywords')->nullable();

            // Belangrijkste pagina’s, bv. [{"url":"/woningontruiming-arnhem","label":"Arnhem"}, ...]
            $table->json('main_pages')->nullable();

            // SERanking project koppeling via MCP
            $table->string('seranking_project_id')->nullable();

            // Health scores, 0–100
            $table->unsignedTinyInteger('health_overall')->nullable();
            $table->unsignedTinyInteger('health_technical')->nullable();
            $table->unsignedTinyInteger('health_content')->nullable();
            $table->unsignedTinyInteger('health_authority')->nullable();

            // Laatste audit die als "actief" geldt voor dit project
            $table->unsignedBigInteger('last_audit_id')->nullable();

            // Laatste keer dat we data uit SERanking hebben gesynct
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            // Handige indexen
            $table->index('domain');
            $table->index('health_overall');
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_projects');
    }
};
