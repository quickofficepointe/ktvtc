<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kcb_transactions', function (Blueprint $table) {
            $table->id();

            // From IPN payload
            $table->string('transaction_reference', 100)->unique();
            $table->string('request_id', 100)->unique();
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

            // Internal tracking
            $table->string('student_number', 50)->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('fee_payment_id')->nullable()->constrained('fee_payments')->nullOnDelete();

            // Metadata
            $table->json('raw_payload');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('customer_reference');
            $table->index('student_number');
            $table->index('transaction_reference');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kcb_transactions');
    }
};
