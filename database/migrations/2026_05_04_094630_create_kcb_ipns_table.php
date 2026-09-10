<?php
// database/migrations/2026_05_04_094630_create_kcb_ipns_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kcb_ipns', function (Blueprint $table) {
            $table->id();

            // Header fields
            $table->string('message_id')->nullable();
            $table->string('originator_conversation_id')->nullable();
            $table->string('channel_code')->nullable();
            $table->timestamp('timestamp')->nullable();

            // Primary Data (from KCB)
            $table->string('business_key')->nullable();
            $table->string('business_key_type')->nullable();
            $table->string('debit_msisdn')->nullable();
            $table->decimal('transaction_amount', 12, 2)->nullable();
            $table->datetime('transaction_date')->nullable();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('currency')->default('KES');
            $table->string('balance')->nullable();
            $table->string('narration')->nullable();
            $table->string('transaction_type')->nullable();

            // Notification Data
            $table->string('invoice_number')->nullable();
            $table->string('bill_reference_number')->nullable();

            // Reconciliation tracking
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->string('matched_record_type')->nullable(); // 'sale', 'fee_payment', 'event_application', 'enrollment'
            $table->unsignedBigInteger('matched_record_id')->nullable();
            $table->text('reconciliation_notes')->nullable();

            // Raw payload for audit
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            // Indexes for fast reconciliation
            $table->index('invoice_number');
            $table->index('transaction_id');
            $table->index('processed');
            $table->index('created_at');
            $table->index(['matched_record_type', 'matched_record_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kcb_ipns');
    }
};
