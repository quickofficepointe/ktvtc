<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_funding_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_account_id');

            // Funding Details
            $table->decimal('amount', 15, 2);
            $table->string('parent_phone', 20);
            $table->string('parent_name', 255)->nullable();

            // Student snapshot
            $table->string('student_name', 255)->nullable();
            $table->string('student_admission', 50)->nullable();

            // Status
            $table->enum('status', [
                'pending', 'processing', 'completed', 'failed', 'cancelled', 'timeout'
            ])->default('pending');

            // Parent Response
            $table->enum('parent_response', ['pending', 'approved', 'declined'])->default('pending');
            $table->timestamp('parent_response_at')->nullable();

            // KCB Tracking
            $table->string('checkout_request_id', 100)->unique()->nullable();
            $table->string('kcb_invoice_number', 100)->nullable();
            $table->string('mpesa_receipt', 100)->nullable();
            $table->string('ipn_transaction_id', 100)->nullable();

            // Retry Logic
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->integer('max_retries')->default(3);

            // Notification Tracking
            $table->boolean('sms_sent')->default(false);
            $table->timestamp('sms_sent_at')->nullable();
            $table->boolean('parent_notified')->default(false);

            // Completion
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();

            // Failure
            $table->string('failure_reason', 255)->nullable();
            $table->string('failure_code', 50)->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('card_account_id')->references('id')->on('card_accounts')->onDelete('cascade');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['card_account_id']);
            $table->index(['status']);
            $table->index(['parent_phone']);
            $table->index(['checkout_request_id']);
            $table->index(['ipn_transaction_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_funding_requests');
    }
};
