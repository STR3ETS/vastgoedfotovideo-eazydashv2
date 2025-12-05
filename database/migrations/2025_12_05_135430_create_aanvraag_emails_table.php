<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aanvraag_emails', function (Blueprint $table) {
            $table->id();

            // ✅ Koppeling naar AanvraagWebsite
            // Laravel verwacht default table: aanvraag_websites
            $table->foreignId('aanvraag_id')
                ->nullable()
                ->constrained('aanvraag_websites')
                ->nullOnDelete();

            $table->string('direction')->default('inbound'); // inbound | outbound
            $table->string('mailbox')->nullable();

            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();

            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();

            $table->string('subject')->nullable();
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();

            $table->string('message_id')->nullable()->index();
            $table->string('in_reply_to')->nullable()->index();
            $table->json('references')->nullable();

            $table->timestamp('received_at')->nullable();
            $table->json('raw')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aanvraag_emails');
    }
};
