<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offertes', function (Blueprint $table) {
            $table->uuid('public_view_uuid')->nullable()->after('public_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('offertes', function (Blueprint $table) {
            $table->dropColumn('public_view_uuid');
        });
    }
};
