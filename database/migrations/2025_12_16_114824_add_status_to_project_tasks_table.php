<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->string('status', 20)->default('open')->after('description');
            $table->index(['project_id', 'status']);
        });

        // backfill: als completed_at gevuld is -> done, anders open
        DB::table('project_tasks')
            ->whereNotNull('completed_at')
            ->update(['status' => 'done']);

        DB::table('project_tasks')
            ->whereNull('completed_at')
            ->whereNull('status')
            ->update(['status' => 'open']);
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'status']);
            $table->dropColumn('status');
        });
    }
};
