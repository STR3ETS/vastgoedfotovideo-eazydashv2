<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offertes', function (Blueprint $table) {
            // Wanneer is de offerte digitaal getekend
            $table->timestamp('signed_at')
                ->nullable()
                ->after('sent_at');

            // Pad naar de opgeslagen handtekening (storage/app/public/...)
            $table->string('signature_path')
                ->nullable()
                ->after('signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('offertes', function (Blueprint $table) {
            $table->dropColumn(['signed_at', 'signature_path']);
        });
    }
};
