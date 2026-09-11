<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();

            // Owner
            $table->foreignId('pre_order_account_id')
                  ->constrained('pre_order_accounts')
                  ->cascadeOnDelete();

            // Which shop will fulfill it
            $table->foreignId('shop_id')
                  ->constrained('shops')
                  ->restrictOnDelete();

            // Reference number (human-readable)
            $table->string('order_number', 40)->unique();

            // Payment
            $table->enum('payment_method', ['card', 'mpesa'])->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            // Status flow:
            //   pending    -> just placed, awaiting payment or staff action
            //   processing -> M-Pesa STK sent, waiting for callback
            //   paid       -> payment confirmed (card deduction or M-Pesa)
            //   accepted   -> cafeteria staff accepted the order
            //   ready      -> prepared and ready for pickup
            //   completed  -> picked up by customer
            //   cancelled  -> cancelled by customer before acceptance
            //   rejected   -> rejected by cafeteria staff
            //   failed     -> payment failed
            $table->enum('status', [
                'pending',
                'processing',
                'paid',
                'accepted',
                'ready',
                'completed',
                'cancelled',
                'rejected',
                'failed',
            ])->default('pending');

            // M-Pesa / KCB tracking
            $table->string('checkout_request_id', 100)->nullable()->index();
            $table->string('merchant_request_id', 100)->nullable();
            $table->string('mpesa_receipt', 100)->nullable();
            $table->string('kcb_invoice_number', 100)->nullable();

            // Card-deduction tracking (for high school students)
            $table->foreignId('card_transaction_id')
                  ->nullable()
                  ->constrained('card_transactions')
                  ->nullOnDelete();

            // Timestamps for state changes
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('staff_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('rejection_reason')->nullable();

            // Audit
            $table->foreignId('accepted_by_user_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by_user_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['status', 'created_at']);
            $table->index(['shop_id', 'status']);
            $table->index(['pre_order_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
    }
};
