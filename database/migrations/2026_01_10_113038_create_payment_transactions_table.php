<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();

            // Sale reference (can be null for other payments like fees)
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();

            // Payment details
         $table->enum('payment_method', ['mpesa', 'cash', 'card', 'bank_transfer', 'credit', 'wallet', 'multiple', 'mpesa_manual']);
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('KES');

            // MPesa specific fields
            $table->string('mpesa_receipt')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('transaction_id')->nullable();

            // Card specific fields
            $table->string('card_last_four')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_authorization_code')->nullable();

            // Bank transfer specific
            $table->string('bank_name')->nullable();
            $table->string('bank_reference')->nullable();
            $table->date('transfer_date')->nullable();

            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('pending');
            $table->timestamp('completed_at')->nullable();

            // KCB Integration fields
            $table->string('checkout_request_id')->nullable();
            $table->string('merchant_request_id')->nullable();
            $table->json('kcb_response')->nullable();
            $table->timestamp('callback_received_at')->nullable();

            // Reconciliation
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reconciled_at')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('sale_id');
            $table->index('transaction_number');
            $table->index('mpesa_receipt');
            $table->index('checkout_request_id');
            $table->index('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
