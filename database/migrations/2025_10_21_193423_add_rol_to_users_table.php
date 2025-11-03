<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Gebruik ENUM in MySQL (zelfde als je andere project)
            if (!Schema::hasColumn('users', 'rol')) {
                $table->enum('rol', ['admin', 'medewerker', 'klant'])
                      ->default('klant')
                      ->after('password')
                      ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'rol')) {
                $table->dropColumn('rol');
            }
        });
    }
};
