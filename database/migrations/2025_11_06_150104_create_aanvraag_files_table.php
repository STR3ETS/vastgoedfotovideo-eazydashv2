<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aanvraag_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aanvraag_website_id')
                  ->constrained('aanvraag_websites')
                  ->cascadeOnDelete();

            $table->string('original_name');
            $table->string('path');        // storage path
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aanvraag_files');
    }
};
