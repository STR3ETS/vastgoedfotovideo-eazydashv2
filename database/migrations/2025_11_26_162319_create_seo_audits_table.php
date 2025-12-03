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
        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();

            // Koppeling naar SEO project
            $table->foreignId('seo_project_id')
                ->constrained('seo_projects')
                ->cascadeOnDelete();

            $table->string('source')->default('manual');
            $table->string('status')->default('pending');

            $table->unsignedTinyInteger('score_overall')->nullable();
            $table->unsignedTinyInteger('score_technical')->nullable();
            $table->unsignedTinyInteger('score_content')->nullable();
            $table->unsignedTinyInteger('score_authority')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->json('raw_summary')->nullable();

            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('source');
            $table->index('started_at');
            $table->index('finished_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_audits');
    }
};
