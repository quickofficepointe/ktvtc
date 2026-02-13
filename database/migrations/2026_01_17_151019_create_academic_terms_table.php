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
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();

            // ========== CAMPUS RELATIONSHIP ==========
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->onDelete('cascade');
            // NULL = Global term (all campuses)
            // Specific ID = Campus-specific term

            // ========== TERM IDENTIFICATION ==========
            $table->string('name'); // "Term 1 2023", "Q1 2024", "Jan-Mar 2023"
            $table->string('code')->unique(); // "Q1-2023", "T1-2023-MAIN" (campus specific)
            $table->string('short_code', 10)->nullable(); // "Q1", "T1"
            $table->integer('term_number'); // 1, 2, 3, 4

            // ========== ACADEMIC YEAR ==========
            $table->year('academic_year');
            $table->string('academic_year_name')->nullable(); // "2023/2024"

            // ========== TERM DATES (CRITICAL FOR FEES) ==========
            $table->date('start_date'); // Term start
            $table->date('end_date'); // Term end
            $table->date('fee_due_date'); // When fees are due
            $table->date('registration_start_date')->nullable(); // When registration opens
            $table->date('registration_end_date')->nullable(); // When registration closes
            $table->date('late_registration_start_date')->nullable(); // Late registration period
            $table->date('late_registration_end_date')->nullable();

            // ========== EXAM DATES ==========
            $table->date('exam_registration_start_date')->nullable();
            $table->date('exam_registration_end_date')->nullable();
            $table->date('exam_start_date')->nullable();
            $table->date('exam_end_date')->nullable();

            // ========== STATUS FLAGS ==========
            $table->boolean('is_active')->default(true);
            $table->boolean('is_current')->default(false);
            $table->boolean('is_registration_open')->default(false);
            $table->boolean('is_fee_generation_locked')->default(false); // Prevent duplicate invoices
            $table->boolean('allow_late_registration')->default(false);

            // ========== FINANCIAL SETTINGS ==========
            $table->decimal('late_registration_fee', 10, 2)->default(0);
            $table->decimal('late_payment_fee', 10, 2)->default(0);
            $table->integer('late_payment_percentage')->default(0); // Percentage of balance

            // ========== METADATA ==========
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);

            // ========== AUDIT TRAIL ==========
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES FOR PERFORMANCE ==========
            $table->index('code');
            $table->index('campus_id');
            $table->index('academic_year');
            $table->index('term_number');
            $table->index('is_current');
            $table->index('is_active');
            $table->index('is_registration_open');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('fee_due_date');
            $table->index(['campus_id', 'academic_year', 'term_number']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
