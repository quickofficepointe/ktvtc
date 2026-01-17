<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kcb_buni_transactions', function (Blueprint $table) {
            $table->id();

            // Add application_id foreign key
            $table->foreignId('application_id')->nullable()->constrained('event_applications')->onDelete('cascade');

            $table->string('merchant_request_id')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->string('phone_number');
            $table->decimal('amount', 10, 2);
            $table->string('invoice_number');
            $table->string('transaction_type')->default('event_registration');
            $table->string('status')->default('initiated'); // initiated, completed, failed
            $table->integer('result_code')->nullable();
            $table->text('result_description')->nullable();
            $table->string('mpesa_receipt_number')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->json('callback_data')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('application_id');
            $table->index('merchant_request_id');
            $table->index('checkout_request_id');
            $table->index('phone_number');
            $table->index('status');
            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kcb_buni_transactions');
    }
};
