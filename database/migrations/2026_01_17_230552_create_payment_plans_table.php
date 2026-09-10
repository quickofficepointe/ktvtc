<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      // 17_payment_plans.php (MODIFIED - Use enrollment_id instead of registration_id)
// 17_payment_plans.php (Keep - uses enrollment_id)
Schema::create('payment_plans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('enrollment_id')->unique()->constrained()->onDelete('cascade');
    $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

    $table->string('plan_code')->unique();
    $table->string('plan_name');
    $table->enum('plan_type', ['monthly', 'quarterly', 'semester', 'annual', 'full_course', 'custom'])->default('monthly');

    $table->decimal('total_amount', 12, 2);
    $table->decimal('discount_amount', 12, 2)->default(0);
    $table->string('discount_reason')->nullable();
    $table->decimal('net_amount', 12, 2);
    $table->decimal('amount_paid', 12, 2)->default(0);
    $table->decimal('balance', 12, 2)->virtualAs('net_amount - amount_paid');

    $table->integer('number_of_installments')->default(1);
    $table->json('installment_schedule')->nullable();
    $table->date('start_date');
    $table->date('end_date');
    $table->date('first_payment_date');

    $table->decimal('late_fee_percentage', 5, 2)->default(5.00);
    $table->integer('grace_period_days')->default(7);
    $table->boolean('auto_generate_invoices')->default(true);

    $table->enum('status', ['draft', 'active', 'completed', 'cancelled', 'defaulted'])->default('draft');
    $table->boolean('is_approved')->default(false);
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();

    $table->string('agreement_document_path')->nullable();
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    $table->timestamps();
    $table->softDeletes();

    $table->index('plan_code');
    $table->index('student_id');
    $table->index('status');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
    }
};
