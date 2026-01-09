<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('project_quotes', function (Blueprint $table) {
      $table->id();

      $table->foreignId('project_id')->constrained()->cascadeOnDelete();
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

      // optioneel nummer (bijv. OFF-2026-0001)
      $table->string('quote_number')->nullable()->index();

      $table->date('quote_date');
      $table->date('expire_date')->nullable();

      // draft | sent | accepted | rejected
      $table->string('status')->default('draft')->index();

      $table->unsignedTinyInteger('vat_rate')->default(21);

      $table->integer('sub_total_cents')->default(0);
      $table->integer('vat_cents')->default(0);
      $table->integer('total_cents')->default(0);

      $table->text('notes')->nullable();

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('project_quotes');
  }
};
