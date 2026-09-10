<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       // 18_plan_installments.php (MODIFIED)
// 18_plan_installments.php (Keep)
Schema::create('plan_installments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('payment_plan_id')->constrained()->onDelete('cascade');
    $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

    $table->integer('installment_number');
    $table->string('description')->nullable();
    $table->decimal('amount', 12, 2);
    $table->decimal('amount_paid', 12, 2)->default(0);
    $table->decimal('balance', 12, 2)->virtualAs('amount - amount_paid');
    $table->date('due_date');
    $table->date('paid_date')->nullable();

    $table->enum('status', ['upcoming', 'pending', 'partial', 'paid', 'overdue', 'waived'])->default('upcoming');

    $table->boolean('late_fee_applied')->default(false);
    $table->decimal('late_fee_amount', 12, 2)->default(0);
    $table->integer('days_overdue')->default(0);

    $table->string('payment_reference')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['payment_plan_id', 'installment_number']);
    $table->index('due_date');
    $table->index('status');
    $table->unique(['payment_plan_id', 'installment_number']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plan_installments');
    }
};
