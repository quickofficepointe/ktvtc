<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();

            // ==================== LINKS ====================
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_structure_id')->nullable()->constrained()->onDelete('set null');

            // ==================== INVOICE DETAILS ====================
            $table->string('invoice_number')->unique();
            $table->string('description');
            $table->text('detailed_description')->nullable();

            // ==================== FEE CATEGORIZATION ====================
            $table->enum('fee_category', [
                'tuition',
                'registration',
                'examination',
                'certification',
                'workshop',
                'practical',
                'library',
                'medical',
                'sports',
                'activity',
                'caution_money',
                'tool_kit',
                'protective_clothing',
                'industrial_attachment',
                'late_fee',
                'other'
            ])->default('tuition');

            $table->enum('fee_type', ['recurring', 'one_time', 'penalty', 'refundable'])->default('recurring');

            // ==================== MONTH & PERIOD ====================
            $table->year('academic_year');
            $table->string('billing_month')->nullable(); // e.g., "January", "February"
            $table->integer('month_number')->nullable(); // 1, 2, 3... (in course sequence)
            $table->string('billing_cycle')->nullable(); // e.g., "Month 1", "Semester 1"

            // ==================== AMOUNTS ====================
            $table->decimal('amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->virtualAs('amount - discount');
            $table->decimal('total_amount', 10, 2)->virtualAs('amount - discount + tax');

            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->virtualAs('total_amount - amount_paid');

            // ==================== PAYMENT TRACKING ====================
            $table->enum('payment_status', [
                'draft',
                'pending',
                'partial',
                'paid',
                'overdue',
                'cancelled',
                'waived',
                'refunded'
            ])->default('draft');

            $table->date('invoice_date');
            $table->date('due_date');
            $table->date('paid_date')->nullable();

            // ==================== INSTALLMENT TRACKING ====================
            $table->integer('installment_number')->nullable();
            $table->integer('total_installments')->nullable();
            $table->boolean('is_installment')->default(false);

            // ==================== LATE FEE TRACKING ====================
            $table->boolean('late_fee_applied')->default(false);
            $table->decimal('late_fee_amount', 10, 2)->default(0);
            $table->date('late_fee_date')->nullable();
            $table->integer('days_overdue')->default(0);

            // ==================== REFUNDABLE DEPOSITS ====================
            $table->boolean('is_refundable')->default(false);
            $table->enum('refund_status', ['not_refunded', 'pending_refund', 'refunded'])->default('not_refunded');
            $table->date('refund_date')->nullable();
            $table->decimal('refund_amount', 10, 2)->default(0);

            // ==================== CDACC SPECIFIC ====================
            $table->boolean('is_cdacc_fee')->default(false);
            $table->string('cdacc_reference')->nullable();
            $table->enum('cdacc_status', ['pending', 'submitted', 'confirmed', 'rejected'])->nullable();

            // ==================== ADMINISTRATIVE ====================
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ==================== INDEXES ====================
            $table->index('invoice_number');
            $table->index(['student_id', 'payment_status']);
            $table->index(['due_date', 'payment_status']);
            $table->index(['registration_id', 'month_number']);
            $table->index(['fee_category', 'is_cdacc_fee']);
            $table->index(['academic_year', 'billing_month']);
            $table->index('cdacc_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
