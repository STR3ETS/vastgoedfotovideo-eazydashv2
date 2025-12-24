<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_audit_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seo_audit_id')
                ->constrained('seo_audits')
                ->cascadeOnDelete();

            $table->string('raw_issue_id')->nullable();
            $table->string('raw_name')->nullable();

            $table->string('severity')->nullable();          // critical / warning / info
            $table->unsignedInteger('pages_affected')->nullable();

            $table->json('sample_urls')->nullable();

            $table->string('code')->nullable();              // bv. images_4xx
            $table->string('label')->nullable();             // nette naam
            $table->string('category')->nullable();          // Techniek / Content / Links / UX / Overig

            $table->string('impact')->nullable();            // hoog / middel / laag
            $table->string('effort')->nullable();            // hoog / middel / laag
            $table->string('owner')->nullable();             // legacy
            $table->string('priority')->nullable();          // quick_win / must_fix / normal / low

            $table->json('data')->nullable();

            $table->timestamps();

            $table->index('severity');
            $table->index('category');
            $table->index('priority');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_audit_results');
    }
};
