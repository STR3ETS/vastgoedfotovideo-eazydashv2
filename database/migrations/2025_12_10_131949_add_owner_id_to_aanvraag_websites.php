<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aanvraag_websites', function (Blueprint $table) {
            if (!Schema::hasColumn('aanvraag_websites', 'owner_id')) {
                $table->foreignId('owner_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aanvraag_websites', function (Blueprint $table) {
            if (Schema::hasColumn('aanvraag_websites', 'owner_id')) {
                $table->dropConstrainedForeignId('owner_id');
            }
        });
    }
};
