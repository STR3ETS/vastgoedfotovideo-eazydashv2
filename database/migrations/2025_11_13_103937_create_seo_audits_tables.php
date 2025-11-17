<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('domain');
            $table->string('type')->default('full'); // bijvoorbeeld: full, technical, keywords, backlinks

            $table->string('status')->default('pending'); // pending, running, completed, failed

            $table->unsignedTinyInteger('overall_score')->nullable(); // 0-100
            $table->json('meta')->nullable(); // land, taal, device, etc.

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });

        Schema::create('seo_audit_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seo_audit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('section'); // technical, onpage, backlinks, keywords, etc.
            $table->unsignedTinyInteger('score')->nullable(); // 0-100 per section
            $table->unsignedInteger('issues_found')->nullable();

            $table->json('payload')->nullable(); // ruwe SE Ranking response voor deze section

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_audit_results');
        Schema::dropIfExists('seo_audits');
    }
};
