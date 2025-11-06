<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Default veranderen naar 'prospect'
        Schema::table('aanvraag_websites', function (Blueprint $table) {
            $table->string('status', 32)->default('prospect')->change();
        });

        // Bestaande records bijwerken
        DB::table('aanvraag_websites')
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', 'nieuw');
            })
            ->update(['status' => 'prospect']);
    }

    public function down(): void
    {
        // Default terug naar 'nieuw'
        Schema::table('aanvraag_websites', function (Blueprint $table) {
            $table->string('status', 32)->default('nieuw')->change();
        });

        // Eventueel data terugzetten
        DB::table('aanvraag_websites')
            ->where('status', 'prospect')
            ->update(['status' => 'nieuw']);
    }
};
