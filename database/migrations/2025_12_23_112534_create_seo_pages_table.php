<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seo_project_id')
                ->constrained('seo_projects')
                ->cascadeOnDelete();

            $table->foreignId('seo_keyword_id')
                ->nullable()
                ->constrained('seo_keywords')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();        // voorstel
            $table->string('planned_url')->nullable(); // voorstel url
            $table->string('live_url')->nullable();    // ingevuld na Sitejet

            $table->string('goal')->nullable(); // bv. "offerte", "bellen"
            $table->string('status')->default('todo'); // todo / writing / built / published

            // copy blokken voor sitejet copy-paste
            $table->json('content_blocks')->nullable(); // hero/intro/secties/faq/cta
            $table->json('meta')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['seo_project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
    }
};
