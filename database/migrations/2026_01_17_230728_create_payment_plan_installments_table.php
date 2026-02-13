<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_plan_installments', function (Blueprint $table) {
            $table->id();

            // ==================== LINKS ====================
            $table->foreignId('payment_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_fee_id')->nullable()->constrained()->onDelete('set null');

            // ==================== INSTALLMENT DETAILS ====================
            $table->integer('installment_number');
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->virtualAs('amount - amount_paid');

            // ==================== DATES ====================
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->date('invoice_generated_date')->nullable();

            // ==================== STATUS ====================
            $table->enum('status', [
                'upcoming',
                'pending',
                'partial',
                'paid',
                'overdue',
                'waived',
                'cancelled'
            ])->default('upcoming');

            // ==================== LATE FEE TRACKING ====================
            $table->boolean('late_fee_applied')->default(false);
            $table->decimal('late_fee_amount', 10, 2)->default(0);
            $table->date('late_fee_applied_date')->nullable();
            $table->integer('days_overdue')->default(0);

            // ==================== INVOICE TRACKING ====================
            $table->string('invoice_number')->nullable();
            $table->boolean('invoice_generated')->default(false);
            $table->timestamp('invoice_sent_at')->nullable();
            $table->boolean('invoice_reminder_sent')->default(false);
            $table->timestamp('last_reminder_sent_at')->nullable();

            // ==================== PAYMENT DETAILS ====================
            $table->json('payment_details')->nullable();
            $table->string('payment_reference')->nullable();
            $table->enum('payment_method', ['mpesa', 'bank', 'cash', 'cheque', 'card', 'other'])->nullable();

            // ==================== ADMINISTRATIVE ====================
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
            
            // ==================== INDEXES ====================
            $table->index(['payment_plan_id', 'installment_number']);
            $table->index(['due_date', 'status']);
            $table->index('invoice_number');
            $table->index('payment_reference');
            $table->unique(['payment_plan_id', 'installment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plan_installments');
    }
};
