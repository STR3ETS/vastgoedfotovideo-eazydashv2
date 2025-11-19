<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offertes', function (Blueprint $table) {
            $table->json('content_overrides')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('offertes', function (Blueprint $table) {
            $table->dropColumn('content_overrides');
        });
    }
};
