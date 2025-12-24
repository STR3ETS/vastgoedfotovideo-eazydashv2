<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_keywords', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seo_project_id')
                ->constrained('seo_projects')
                ->cascadeOnDelete();

            $table->string('keyword');
            $table->boolean('is_selected')->default(false);   // gekozen door medewerker
            $table->boolean('is_primary')->default(false);    // top 10 focus

            // metadata (kan uit MCP/SE Ranking komen)
            $table->unsignedInteger('search_volume')->nullable();
            $table->unsignedTinyInteger('difficulty')->nullable(); // 0-100
            $table->decimal('cpc', 8, 2)->nullable();
            $table->decimal('competition', 5, 2)->nullable();

            // intent / uitleg voor de leek
            $table->string('intent')->nullable(); // bv. "offerte", "bellen", "informatie"
            $table->text('reason')->nullable();   // 1 zin: waarom dit keyword

            // koppelingen / tracking
            $table->string('seranking_keyword_id')->nullable(); // keyword id in SE Ranking project API
            $table->string('target_url')->nullable();          // welke pagina moet hierop ranken

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['seo_project_id', 'is_selected']);
            $table->index(['seo_project_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_keywords');
    }
};
