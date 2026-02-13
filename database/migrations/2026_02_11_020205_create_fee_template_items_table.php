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
        Schema::create('fee_template_items', function (Blueprint $table) {
            $table->id();

            // ========== RELATIONSHIPS ==========
            $table->foreignId('fee_template_id')->constrained('course_fee_templates')->onDelete('cascade');
            $table->foreignId('fee_category_id')->constrained('fee_categories');

            // ========== ITEM DETAILS ==========
            $table->string('item_name'); // Specific name: "ID Fee", "NITA Exam", "Tuition Q1"
            $table->text('description')->nullable();

            // ========== FINANCIAL ==========
            $table->decimal('amount', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total_amount', 12, 2)->virtualAs('amount * quantity');

            // ========== TERM APPLICATION ==========
            $table->string('applicable_terms'); // "1", "1,2,3", "all", "1,4"
            $table->boolean('is_required')->default(true);
            $table->boolean('is_refundable')->default(false);

            // ========== TIMING ==========
            $table->integer('due_day_offset')->default(0); // Days after term start
            $table->boolean('is_advance_payment')->default(false); // Pay before term starts?

            // ========== DISPLAY & ORDER ==========
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible_to_student')->default(true);

            // ========== CAMPUS OVERRIDE (Future) ==========
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            // NULL = Use template amount, Specific ID = Campus-specific override

            // ========== AUDIT TRAIL ==========
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // ========== INDEXES ==========
            $table->index('fee_template_id');
            $table->index('fee_category_id');
            $table->index('campus_id');
            $table->index('is_required');
            $table->index('sort_order');
            $table->index('applicable_terms');
            $table->index(['fee_template_id', 'fee_category_id', 'item_name'], 'unique_item_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_template_items');
    }
};
