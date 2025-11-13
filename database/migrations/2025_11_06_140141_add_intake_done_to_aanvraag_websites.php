<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('aanvraag_websites', function (Blueprint $t) {
            $t->boolean('intake_done')->default(false)->after('intake_duration');
            $t->timestamp('intake_completed_at')->nullable()->after('intake_done');
        });
    }

    public function down(): void {
        Schema::table('aanvraag_websites', function (Blueprint $t) {
            $t->dropColumn(['intake_done', 'intake_completed_at']);
        });
    }
};
