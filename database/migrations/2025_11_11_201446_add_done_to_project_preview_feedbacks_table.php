<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_preview_feedbacks', function (Blueprint $table) {
            $table->boolean('is_done')->default(false)->after('feedback');
            $table->timestamp('done_at')->nullable()->after('is_done');
        });
    }

    public function down(): void
    {
        Schema::table('project_preview_feedbacks', function (Blueprint $table) {
            $table->dropColumn(['is_done', 'done_at']);
        });
    }
};
