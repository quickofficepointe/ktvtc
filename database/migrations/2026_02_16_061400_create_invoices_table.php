<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       // 14_invoices.php (NEW - Replaces student_fees)
// 14_invoices.php (NEW - Replaces student_fees)
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
    $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
    $table->string('invoice_number')->unique();
    $table->date('invoice_date');
    $table->date('due_date');
    $table->string('description');
    $table->decimal('subtotal', 12, 2);
    $table->decimal('discount', 12, 2)->default(0);
    $table->string('discount_reason')->nullable();
    $table->decimal('tax', 12, 2)->default(0);
    $table->decimal('total_amount', 12, 2);
    $table->decimal('amount_paid', 12, 2)->default(0);
    $table->decimal('balance', 12, 2)->virtualAs('total_amount - amount_paid');
    $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index('invoice_number');
    $table->index('student_id');
    $table->index('enrollment_id');
    $table->index('status');
    $table->index('due_date');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
