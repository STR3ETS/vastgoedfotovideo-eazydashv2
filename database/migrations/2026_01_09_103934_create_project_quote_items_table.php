<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('project_quote_items', function (Blueprint $table) {
      $table->id();

      $table->foreignId('project_quote_id')->constrained('project_quotes')->cascadeOnDelete();

      $table->unsignedInteger('position')->default(0);

      $table->string('description');
      $table->unsignedInteger('quantity')->default(1);

      $table->integer('unit_price_cents')->default(0);
      $table->integer('line_total_cents')->default(0);

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('project_quote_items');
  }
};
