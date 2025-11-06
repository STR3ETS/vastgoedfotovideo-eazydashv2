<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aanvraag_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aanvraag_website_id')->constrained()->cascadeOnDelete();
            $table->string('type');   // bv: call_customer, content_intake, etc.
            $table->string('title');
            $table->string('status')->default('open'); // open, done, cancelled
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aanvraag_tasks');
    }
};
