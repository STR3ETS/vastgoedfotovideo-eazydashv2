<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('invoice_number', 32)->unique();

            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            $table->string('status', 20)->default('draft'); // draft | sent | paid | cancelled
            $table->text('notes')->nullable();

            $table->unsignedSmallInteger('vat_rate')->default(21);

            $table->unsignedBigInteger('sub_total_cents')->default(0);
            $table->unsignedBigInteger('vat_cents')->default(0);
            $table->unsignedBigInteger('total_cents')->default(0);

            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invoices');
    }
};
