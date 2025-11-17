<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Oude structuur weg. Data gaat hiermee verloren.
        Schema::dropIfExists('seo_audit_results');

        Schema::create('seo_audit_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seo_audit_id')
                ->constrained()
                ->cascadeOnDelete();

            // Ruw uit SERanking
            $table->string('raw_issue_id')->nullable();    // id uit SERanking, indien aanwezig
            $table->string('raw_name')->nullable();        // originele titel uit SERanking

            // Genormaliseerde data
            $table->string('severity')->nullable();        // critical / warning / info
            $table->unsignedInteger('pages_affected')->nullable(); // aantal pagina's

            $table->json('sample_urls')->nullable();       // voorbeeld URL's uit rapport
            $table->string('code')->nullable();            // interne code uit SERanking of eigen code
            $table->string('label')->nullable();           // nette titel voor in de UI

            $table->string('category')->nullable();        // Techniek / Content / Links / UX / Overig
            $table->string('impact')->nullable();          // hoog / middel / laag
            $table->string('effort')->nullable();          // hoog / middel / laag
            $table->string('owner')->nullable();           // developer / copywriter / seo / designer
            $table->string('priority')->nullable();        // quick_win / must_fix / normal / low

            $table->json('data')->nullable();              // volledige ruwe check data uit SERanking

            $table->timestamps();

            // Handige indexen voor filters
            $table->index(['seo_audit_id', 'category']);
            $table->index(['seo_audit_id', 'severity']);
            $table->index(['seo_audit_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_audit_results');

        // Oude minimalistische versie terugzetten
        Schema::create('seo_audit_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seo_audit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('section');                  // technical, onpage, backlinks, keywords, etc.
            $table->unsignedTinyInteger('score')->nullable(); // 0-100 per section
            $table->unsignedInteger('issues_found')->nullable();
            $table->json('payload')->nullable();

            $table->timestamps();
        });
    }
};
