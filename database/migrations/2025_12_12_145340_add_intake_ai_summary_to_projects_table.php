<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->longText('intake_ai_summary')->nullable()->after('contact_phone');
            $table->timestamp('intake_ai_summary_generated_at')->nullable()->after('intake_ai_summary');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['intake_ai_summary', 'intake_ai_summary_generated_at']);
        });
    }
};
