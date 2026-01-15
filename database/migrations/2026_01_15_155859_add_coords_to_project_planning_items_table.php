<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('project_planning_items', function (Blueprint $table) {
            $table->decimal('location_lat', 10, 7)->nullable()->after('location');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            $table->timestamp('location_geocoded_at')->nullable()->after('location_lng');

            $table->index(['assignee_user_id', 'start_at']);
            $table->index(['assignee_user_id', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::table('project_planning_items', function (Blueprint $table) {
            $table->dropIndex(['assignee_user_id', 'start_at']);
            $table->dropIndex(['assignee_user_id', 'end_at']);

            $table->dropColumn(['location_lat', 'location_lng', 'location_geocoded_at']);
        });
    }
};
