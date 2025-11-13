<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('aanvraag_websites', function (Blueprint $table) {
            $table->dateTime('intake_at')->nullable()->after('status');
            $table->unsignedSmallInteger('intake_duration')->nullable()->after('intake_at');
        });
    }

    public function down()
    {
        Schema::table('aanvraag_websites', function (Blueprint $table) {
            $table->dropColumn(['intake_at', 'intake_duration']);
        });
    }
};
