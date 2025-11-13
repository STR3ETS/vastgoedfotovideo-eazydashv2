<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // lengte 40 is prima; we gebruiken straks 20 tekens, maar heb je speling
            $table->string('preview_token', 40)
                ->nullable()
                ->unique()
                ->after('preview_url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('preview_token');
        });
    }
};
