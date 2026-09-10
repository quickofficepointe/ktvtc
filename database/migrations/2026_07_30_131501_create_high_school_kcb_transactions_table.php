<?php
// database/migrations/2026_07_30_create_high_school_kcb_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('high_school_kcb_transactions', function (Blueprint $table) {
            $table->id();

            // From KCB IPN payload
            $table->string('transaction_reference', 100)->unique()->nullable();
            $table->string('request_id', 100)->unique()->nullable();
            $table->string('merchant_request_id', 100)->nullable();
            $table->string('checkout_request_id', 100)->nullable();
            $table->string('channel_code', 10)->nullable();
            $table->string('timestamp', 20)->nullable();
            $table->decimal('transaction_amount', 12, 2)->nullable();
            $table->string('currency', 5)->nullable();
            $table->string('customer_reference', 100);
            $table->string('customer_name', 200)->nullable();
            $table->string('customer_mobile_number', 20)->nullable();
            $table->decimal('balance', 12, 2)->nullable();
            $table->text('narration')->nullable();
            $table->string('till_number', 20)->nullable();
            $table->string('organization_short_code', 20)->nullable();

            // M-Pesa specific
            $table->string('mpesa_receipt_number', 50)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('invoice_number', 100)->nullable();

            // Card specific
            $table->foreignId('card_account_id')->nullable()->constrained('card_accounts')->nullOnDelete();
            $table->foreignId('high_school_student_id')->nullable()->constrained('high_school_students')->nullOnDelete();

            // Internal tracking
            $table->string('student_number', 50)->nullable();
            $table->string('transaction_type', 50)->default('card_funding'); // card_funding, card_purchase
            $table->string('status', 50)->default('initiated'); // initiated, processing, completed, failed
            $table->integer('result_code')->nullable();
            $table->text('result_description')->nullable();

            // Metadata
            $table->json('request_data')->nullable();
            $table->json('callback_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('customer_reference');
            $table->index('student_number');
            $table->index('transaction_reference');
            $table->index('checkout_request_id');
            $table->index('merchant_request_id');
            $table->index('mpesa_receipt_number');
            $table->index('card_account_id');
            $table->index('high_school_student_id');
            $table->index('status');
            $table->index('transaction_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('high_school_kcb_transactions');
    }
};
