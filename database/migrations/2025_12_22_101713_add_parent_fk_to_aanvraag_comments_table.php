<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) index op parent_id (als die nog niet bestaat)
        $indexes = DB::select("SHOW INDEX FROM `aanvraag_comments` WHERE Key_name = 'aanvraag_comments_parent_id_idx'");
        if (count($indexes) === 0) {
            Schema::table('aanvraag_comments', function (Blueprint $table) {
                $table->index('parent_id', 'aanvraag_comments_parent_id_idx');
            });
        }

        // 2) foreign key op parent_id (als die nog niet bestaat)
        $fks = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'aanvraag_comments'
              AND COLUMN_NAME = 'parent_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (count($fks) === 0) {
            Schema::table('aanvraag_comments', function (Blueprint $table) {
                // Laravel default naam zou zijn: aanvraag_comments_parent_id_foreign
                $table->foreign('parent_id', 'aanvraag_comments_parent_id_foreign')
                    ->references('id')->on('aanvraag_comments')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // FK droppen (als die bestaat)
        try {
            Schema::table('aanvraag_comments', function (Blueprint $table) {
                $table->dropForeign('aanvraag_comments_parent_id_foreign');
            });
        } catch (\Throwable $e) {}

        // index droppen (als die bestaat)
        try {
            Schema::table('aanvraag_comments', function (Blueprint $table) {
                $table->dropIndex('aanvraag_comments_parent_id_idx');
            });
        } catch (\Throwable $e) {}
    }
};
