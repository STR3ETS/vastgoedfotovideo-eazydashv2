<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_keyword_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seo_project_id')
                ->constrained('seo_projects')
                ->cascadeOnDelete();

            $table->foreignId('seo_keyword_id')
                ->constrained('seo_keywords')
                ->cascadeOnDelete();

            $table->date('snapshot_date');

            // 0 of null = niet gevonden (verschil hangt af van bron)
            $table->unsignedSmallInteger('position')->nullable();
            $table->string('url')->nullable();

            // als je later devices/engines wil scheiden
            $table->string('device')->nullable();       // desktop / mobile
            $table->string('search_engine')->nullable(); // google.nl etc

            $table->json('serp_features')->nullable();
            $table->json('raw')->nullable();

            $table->timestamps();

            $table->unique(['seo_keyword_id', 'snapshot_date', 'device', 'search_engine'], 'seo_kw_snap_unique');
            $table->index(['seo_project_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_keyword_snapshots');
    }
};
