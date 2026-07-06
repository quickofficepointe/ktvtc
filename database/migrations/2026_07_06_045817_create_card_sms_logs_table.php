<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('high_school_student_id')->nullable(); // CHANGED
            $table->unsignedBigInteger('contact_id')->nullable();

            // SMS Details
            $table->string('phone_number', 20);
            $table->text('message');
            $table->enum('direction', ['incoming', 'outgoing']);

            // Parent response (for incoming)
            $table->enum('response_type', ['fund', 'balance', 'help', 'stop', 'start', 'other'])->nullable();
            $table->decimal('parsed_amount', 15, 2)->nullable();

            // Status
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'received'])->default('pending');
            $table->string('provider_response', 255)->nullable();
            $table->string('provider_message_id', 100)->nullable();

            // Links
            $table->unsignedBigInteger('funding_request_id')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->string('error_message', 255)->nullable();

            // Timing
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();

            // Foreign keys - CHANGED
            $table->foreign('high_school_student_id')
                  ->references('id')
                  ->on('high_school_students')
                  ->onDelete('set null');

            $table->foreign('contact_id')->references('id')->on('student_contacts')->onDelete('set null');
            $table->foreign('funding_request_id')->references('id')->on('card_funding_requests')->onDelete('set null');

            // Indexes
            $table->index(['phone_number']);
            $table->index(['direction']);
            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['funding_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_sms_logs');
    }
};
