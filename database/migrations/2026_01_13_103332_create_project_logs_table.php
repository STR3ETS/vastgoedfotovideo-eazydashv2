<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('project_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_id')->constrained()->cascadeOnDelete();
      $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

      $table->string('type', 80)->index();      // bv: finance_item.created
      $table->string('message', 255);           // exact toast-tekst
      $table->json('meta')->nullable();         // extra context (ids, totals, status, etc.)

      $table->timestamps();
      $table->index(['project_id', 'created_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('project_logs');
  }
};

