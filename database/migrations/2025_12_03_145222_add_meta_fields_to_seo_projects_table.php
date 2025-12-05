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
        Schema::table('seo_projects', function (Blueprint $table) {
            // Status van het traject: bv. active / paused / onboarding
            $table->string('status')
                ->default('active')
                ->after('seranking_project_id');

            // Interne prioriteit: bv. laag / normaal / hoog
            $table->string('priority')
                ->nullable()
                ->after('status');

            // Zichtbaarheidsscore (bijv. uit SERanking)
            $table->decimal('visibility_index', 8, 2)
                ->nullable()
                ->after('health_authority');

            // Geschat organisch verkeer per maand
            $table->unsignedInteger('organic_traffic')
                ->nullable()
                ->after('visibility_index');

            // Hoofddoel van SEO (korte samenvatting)
            $table->string('primary_goal')
                ->nullable()
                ->after('organic_traffic');

            // Verdere toelichting op het doel
            $table->text('goal_notes')
                ->nullable()
                ->after('primary_goal');

            // Interne notities voor je team
            $table->text('notes')
                ->nullable()
                ->after('goal_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_projects', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'priority',
                'visibility_index',
                'organic_traffic',
                'primary_goal',
                'goal_notes',
                'notes',
            ]);
        });
    }
};
