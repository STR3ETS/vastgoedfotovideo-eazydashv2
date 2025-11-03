<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Voeg FK toe; nullable zodat gebruikers ook zonder company kunnen bestaan
            $table->foreignId('company_id')
                  ->nullable()
                  ->after('id') // pas aan naar wens, bv. ->after('email')
                  ->constrained('companies')
                  ->cascadeOnUpdate()
                  ->nullOnDelete(); // als company verdwijnt, zet user.company_id op NULL
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eerst FK droppen, dan kolom
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
