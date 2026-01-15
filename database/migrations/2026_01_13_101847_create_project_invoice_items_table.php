<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_invoice_id')
                ->constrained('project_invoices')
                ->cascadeOnDelete();

            $table->unsignedInteger('position')->default(0);

            $table->string('description', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price_cents')->default(0);
            $table->unsignedBigInteger('line_total_cents')->default(0);

            $table->timestamps();

            $table->index(['project_invoice_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invoice_items');
    }
};
