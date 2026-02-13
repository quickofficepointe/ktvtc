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
        Schema::create('enrollment_fee_items', function (Blueprint $table) {
            $table->id();

            // ========== RELATIONSHIPS ==========
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_category_id')->constrained('fee_categories');
            $table->foreignId('fee_template_item_id')->nullable()->constrained('fee_template_items')->nullOnDelete();

            // ========== ITEM DETAILS ==========
            $table->string('item_name'); // e.g., "Tuition Fee Q1", "ID Fee", "NITA Exam"
            $table->text('description')->nullable();

            // ========== FINANCIAL ==========
            $table->decimal('amount', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);

            // ========== TERM APPLICATION ==========
            $table->string('applicable_terms')->nullable(); // "1", "1,2,3", "all", "Q1", "Q1,Q2"
            $table->integer('term_number')->nullable(); // 1,2,3,4
            $table->boolean('is_required')->default(true);
            $table->boolean('is_refundable')->default(false);

            // ========== TIMING ==========
            $table->integer('due_day_offset')->default(0); // Days after term start
            $table->date('due_date')->nullable(); // Specific due date
            $table->boolean('is_advance_payment')->default(false);

            // ========== STATUS ==========
            $table->enum('status', ['pending', 'paid', 'partially_paid', 'waived', 'cancelled'])->default('pending');
            $table->boolean('is_active')->default(true);

            // ========== DISPLAY ==========
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible_to_student')->default(true);

            // ========== AUDIT TRAIL ==========
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES - FIXED ==========
            $table->index('enrollment_id');
            $table->index('fee_category_id');
            $table->index('status');
            $table->index('term_number');
            $table->index('due_date');

            // ✅ FIXED: Added short custom name to prevent auto-generated long name
            $table->index(['enrollment_id', 'fee_category_id', 'item_name'], 'enroll_fee_cat_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_fee_items');
    }
};
