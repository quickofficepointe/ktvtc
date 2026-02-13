<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();

            // ==================== LINKS ====================
            $table->foreignId('registration_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('fee_structure_id')->nullable()->constrained()->onDelete('set null');

            // ==================== PLAN IDENTIFICATION ====================
            $table->string('plan_code')->unique();
            $table->string('plan_name');
            $table->enum('plan_type', [
                'monthly',
                'quarterly',
                'semester',
                'annual',
                'full_course',
                'custom'
            ])->default('monthly');

            // ==================== FINANCIAL SUMMARY ====================
            $table->decimal('total_course_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('net_amount', 10, 2)->virtualAs('total_course_amount - discount_amount');

            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('total_balance', 10, 2)->virtualAs('net_amount - amount_paid');
            $table->decimal('total_due', 10, 2)->default(0);

            // ==================== INSTALLMENT CONFIGURATION ====================
            $table->integer('number_of_installments')->default(1);
            $table->string('installment_frequency')->nullable();
            $table->json('installment_schedule')->nullable();

            // ==================== DATES ====================
            $table->date('start_date');
            $table->date('end_date');
            $table->date('first_payment_date');
            $table->date('last_payment_date')->nullable();

            // ==================== TERMS & CONDITIONS ====================
            $table->text('terms_and_conditions')->nullable();
            $table->decimal('late_fee_percentage', 5, 2)->default(5.00);
            $table->integer('grace_period_days')->default(7);
            $table->boolean('auto_generate_invoices')->default(true);
            $table->integer('invoice_days_before_due')->default(7);

            // ==================== STATUS ====================
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'active',
                'completed',
                'cancelled',
                'suspended',
                'defaulted'
            ])->default('draft');

            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // ==================== SIGNATURES ====================
            $table->foreignId('student_signatory_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('parent_signatory_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('institution_signatory_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamp('student_signed_at')->nullable();
            $table->timestamp('parent_signed_at')->nullable();
            $table->timestamp('institution_signed_at')->nullable();

            $table->string('agreement_document_path')->nullable();

            // ==================== ADMINISTRATIVE ====================
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ==================== INDEXES ====================
            $table->index('plan_code');
            $table->index(['student_id', 'status']);
            $table->index(['registration_id', 'plan_type']);
            $table->index(['start_date', 'end_date']);
            $table->index('is_approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
    }
};
