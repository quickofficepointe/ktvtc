<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_account_id');

            // Transaction Details
            $table->enum('transaction_type', [
                'funding', 'purchase', 'refund', 'reversal', 'adjustment', 'fee_charge'
            ]);

            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);

            // Status
            $table->enum('status', [
                'pending', 'processing', 'completed', 'failed', 'reversed', 'cancelled'
            ])->default('pending');

            // Reference
            $table->string('reference', 100)->unique();
            $table->string('description', 255)->nullable();

            // Links
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('funding_request_id')->nullable();
            $table->unsignedBigInteger('fee_payment_id')->nullable();

            // IPN/KCB Tracking
            $table->string('ipn_transaction_id', 100)->nullable();
            $table->string('mpesa_receipt', 100)->nullable();
            $table->string('checkout_request_id', 100)->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->string('location', 100)->nullable();
            $table->string('device_id', 100)->nullable();
            $table->string('ip_address', 45)->nullable();

            // Processing
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->string('failure_reason', 255)->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('card_account_id')->references('id')->on('card_accounts')->onDelete('cascade');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
            $table->foreign('funding_request_id')->references('id')->on('card_funding_requests')->onDelete('set null');
            $table->foreign('fee_payment_id')->references('id')->on('fee_payments')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['card_account_id', 'status', 'transaction_type']);
            $table->index(['reference']);
            $table->index(['ipn_transaction_id']);
            $table->index(['mpesa_receipt']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_transactions');
    }
};
