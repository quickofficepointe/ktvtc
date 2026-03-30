<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== SIMPLIFIED FEE PAYMENTS TABLE ==========
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();

            // ========== LINKS (KEEP THESE) ==========
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');

            // ========== PAYMENT DETAILS (ALL YOU NEED!) ==========
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('receipt_number')->unique();

            // ========== PAYMENT METHOD (SIMPLIFIED) ==========
            $table->enum('payment_method', [
                'cash', 'mpesa', 'bank', 'kcb', 'other'
            ])->default('cash');

            // ========== TRANSACTION REFERENCE (MPESA/BANK CODE) ==========
            $table->string('transaction_code')->nullable();

            // ========== FOR CSV IMPORT TRACKING ==========
            $table->string('payment_for_month', 20)->nullable(); // 'JUNE', 'JULY'

            // ========== PAYER INFO (KEEP SIMPLE) ==========
            $table->string('payer_name')->nullable();
            $table->string('payer_phone')->nullable();
            $table->enum('payer_type', ['student', 'parent', 'sponsor', 'employer', 'other'])
                  ->default('student');

            // ========== STATUS (SIMPLIFIED) ==========
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])
                  ->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // ========== NOTES & METADATA ==========
            $table->text('notes')->nullable();
            $table->string('recorded_by')->nullable(); // User who entered payment
            $table->string('import_source')->nullable(); // 'csv_2021', 'manual'

            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES ==========
            $table->index('student_id');
            $table->index('enrollment_id');
            $table->index('payment_date');
            $table->index('receipt_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
