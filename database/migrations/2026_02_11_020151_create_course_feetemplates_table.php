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
        Schema::create('course_fee_templates', function (Blueprint $table) {
            $table->id();

            // ========== BASIC INFO ==========
            $table->string('name'); // e.g., "SHEP NITA Package", "HDBT CDACC Package"
            $table->string('code')->unique()->nullable(); // Optional: "SHEP-NITA-2024"

            // ========== COURSE & EXAM TYPE ==========
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->enum('exam_type', ['nita', 'cdacc', 'school_assessment', 'mixed'])->default('school_assessment');

            // ========== DURATION & STRUCTURE ==========
            $table->integer('total_terms')->default(1); // 1, 2, 3, 4 terms
            $table->integer('duration_months')->nullable(); // Course duration in months
            $table->string('intake_periods')->nullable(); // Which intakes this applies to: "Jan,May,Sept"

            // ========== FINANCIAL SUMMARY ==========
            $table->decimal('total_tuition_fee', 12, 2)->default(0);
            $table->decimal('total_other_fees', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0); // Auto-calculated

            // ========== STATUS & FLAGS ==========
            $table->boolean('is_default')->default(false); // Default template for this course+exam
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true); // Show to students during application?

            // ========== DESCRIPTION ==========
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            // ========== CAMPUS SPECIFIC (Optional) ==========
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            // NULL = All campuses, Specific ID = Campus-specific template

            // ========== AUDIT TRAIL ==========
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES ==========
            $table->index('code');
            $table->index('course_id');
            $table->index('exam_type');
            $table->index('campus_id');
            $table->index('is_default');
            $table->index('is_active');
            $table->index('is_public');
            $table->index('total_terms');
            $table->index(['course_id', 'exam_type', 'is_default']);
            $table->index(['course_id', 'exam_type', 'campus_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_fee_templates');
    }
};
