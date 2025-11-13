<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_preview_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('region', 80)->nullable();   // provincie/regio
            $table->string('country', 80)->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamps(); // created_at = bekeken op
            $table->index(['project_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_preview_views');
    }
};
