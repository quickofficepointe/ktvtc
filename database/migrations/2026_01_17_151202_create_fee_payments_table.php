<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();

            // ==================== LINKS ====================
            $table->foreignId('student_fee_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');

            // ==================== PAYMENT IDENTIFIERS ====================
            $table->string('transaction_id')->unique();
            $table->string('receipt_number')->unique();
            $table->string('reference_number')->nullable();

            // ==================== PAYMENT DETAILS ====================
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->enum('currency', ['KES'])->default('KES');

            // ==================== PAYMENT METHOD (KTVTC-SPECIFIC) ====================
            $table->enum('payment_method', [
                'kcb_stk_push',      // KCB STK Push
                'paybill',           // Direct to Paybill
                'bank_deposit',      // Bank deposit slip
                'cash',              // Cash at bank/campus
                'helb',              // HELB disbursement
                'sponsor',           // Company/government sponsor
                'other'
            ])->default('kcb_stk_push');

            // KCB STK Push Specific
            $table->string('kcb_transaction_code')->nullable();
            $table->string('kcb_merchant_request_id')->nullable();
            $table->string('kcb_checkout_request_id')->nullable();
            $table->string('kcb_phone_number')->nullable();
            $table->string('kcb_account_number')->nullable();

            // Paybill Specific
            $table->string('paybill_number')->nullable();
            $table->string('paybill_account_number')->nullable();
            $table->string('paybill_transaction_code')->nullable();

            // Bank Deposit Specific
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('deposit_slip_number')->nullable();
            $table->date('deposit_date')->nullable();

            // ==================== PAYMENT DATES ====================
            $table->date('payment_date');
            $table->time('payment_time')->nullable();
            $table->timestamp('processed_at')->nullable();

            // ==================== STATUS & VERIFICATION ====================
            $table->enum('status', [
                'initiated',
                'pending',
                'completed',
                'failed',
                'reversed',
                'disputed',
                'refunded'
            ])->default('pending');

            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            // ==================== PAYER INFORMATION ====================
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payer_phone')->nullable();
            $table->string('payer_id_number')->nullable();
            $table->text('payer_address')->nullable();
            $table->enum('payer_type', ['student', 'parent', 'sponsor', 'employer', 'other'])->default('student');

            // ==================== RECEIPT INFORMATION ====================
            $table->string('receipt_generated_by')->nullable();
            $table->timestamp('receipt_generated_at')->nullable();
            $table->string('receipt_file_path')->nullable();
            $table->boolean('receipt_sent_to_payer')->default(false);
            $table->timestamp('receipt_sent_at')->nullable();

            // ==================== ADMINISTRATIVE ====================
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ==================== INDEXES ====================
            $table->index('transaction_id');
            $table->index('receipt_number');
            $table->index('kcb_transaction_code');
            $table->index('paybill_transaction_code');
            $table->index('reference_number');
            $table->index(['student_id', 'payment_date']);
            $table->index(['registration_id', 'status']);
            $table->index(['payment_date', 'status']);
            $table->index('payer_phone');
            $table->index(['is_verified', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
