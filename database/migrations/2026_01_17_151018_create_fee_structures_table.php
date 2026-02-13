<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();

            // Links to course and campus
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('campus_id')->constrained()->onDelete('cascade');

            // TVET/CDACC Identification
            $table->string('cdacc_program_code')->nullable();
            $table->enum('tvet_qualification_type', ['artisan', 'certificate', 'diploma', 'higher_diploma'])->nullable();

            // Intake Period (MONTHLY intakes)
            $table->year('academic_year');
            $table->enum('intake_month', [
                'january', 'february', 'march', 'april', 'may', 'june',
                'july', 'august', 'september', 'october', 'november', 'december'
            ]);

            // Course Duration
            $table->integer('total_course_months');
            $table->enum('course_duration_type', ['weeks', 'months', 'years'])->default('months');

            // ==================== FEE BREAKDOWN ====================

            // A. INSTITUTION FEES (KTVTC)
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->decimal('tuition_per_month', 10, 2)->default(0);
            $table->decimal('caution_money', 10, 2)->default(0);
            $table->decimal('student_id_fee', 10, 2)->default(0);
            $table->decimal('library_fee', 10, 2)->default(0);
            $table->decimal('medical_fee', 10, 2)->default(0);
            $table->decimal('sports_fee', 10, 2)->default(0);
            $table->decimal('activity_fee', 10, 2)->default(0);

            // B. WORKSHOP/PRACTICAL FEES (TVET Specific)
            $table->decimal('workshop_levy', 10, 2)->default(0);
            $table->decimal('practical_materials', 10, 2)->default(0);
            $table->decimal('tool_kit_deposit', 10, 2)->default(0);
            $table->decimal('protective_clothing', 10, 2)->default(0);
            $table->decimal('industrial_attachment_fee', 10, 2)->default(0);

            // C. CDACC/TVET AUTHORITY FEES (MANDATORY)
            $table->decimal('cdacc_registration_fee', 10, 2)->default(0);
            $table->decimal('cdacc_examination_fee', 10, 2)->default(0);
            $table->decimal('cdacc_certification_fee', 10, 2)->default(0);
            $table->decimal('tvet_authority_levy', 10, 2)->default(0);
            $table->decimal('trade_test_fee', 10, 2)->default(0);

            // ==================== CALCULATED FIELDS ====================
            $table->decimal('monthly_total', 10, 2)->default(0);
            $table->decimal('one_time_fees', 10, 2)->default(0);
            $table->decimal('final_month_fees', 10, 2)->default(0);
            $table->decimal('total_course_fee', 10, 2)->default(0);

            // ==================== PAYMENT OPTIONS ====================
            $table->json('payment_plans')->nullable();
            $table->boolean('has_government_sponsorship')->default(false);
            $table->decimal('government_subsidy_amount', 10, 2)->default(0);
            $table->enum('sponsorship_type', ['cdacc', 'county', 'national', 'helb', 'other'])->nullable();

            // ==================== VALIDITY & STATUS ====================
            $table->date('valid_from');
            $table->date('valid_to');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // Late Payment Policy
            $table->integer('grace_period_days')->default(7);
            $table->decimal('late_fee_percentage', 5, 2)->default(5.00);
            $table->integer('suspension_days')->default(15);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(
    ['course_id', 'campus_id', 'academic_year', 'intake_month'],
    'fee_structures_course_campus_year_intake_uk'
);

            $table->index(['academic_year', 'intake_month']);
            $table->index(['course_id', 'is_active']);
            $table->index(['cdacc_program_code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
