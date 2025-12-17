<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('preview_approved_at')->nullable()->after('preview_expires_at');
            $table->string('preview_approved_ip', 45)->nullable()->after('preview_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['preview_approved_at', 'preview_approved_ip']);
        });
    }
};
