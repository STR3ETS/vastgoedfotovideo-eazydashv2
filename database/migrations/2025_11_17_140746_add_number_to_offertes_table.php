<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offertes', function (Blueprint $table) {
            // Nieuwe kolom na id
            $table->string('number', 32)
                ->nullable()
                ->after('id');

            // Optioneel: unieke constraint op offertenummer
            $table->unique('number');
        });
    }

    public function down(): void
    {
        Schema::table('offertes', function (Blueprint $table) {
            // Eerst de unique constraint droppen, daarna kolom
            $table->dropUnique(['number']);
            $table->dropColumn('number');
        });
    }
};
