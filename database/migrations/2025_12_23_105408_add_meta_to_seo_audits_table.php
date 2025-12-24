<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_audits', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('raw_data');
            $table->unsignedBigInteger('remote_audit_id')->nullable()->after('meta');

            $table->index('remote_audit_id');
        });
    }

    public function down(): void
    {
        Schema::table('seo_audits', function (Blueprint $table) {
            $table->dropIndex(['remote_audit_id']);
            $table->dropColumn(['meta', 'remote_audit_id']);
        });
    }
};
