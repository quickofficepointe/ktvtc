<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('restrict');
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');

            $table->string('enrollment_number', 50)->unique();
            $table->string('legacy_enrollment_code', 50)->nullable()->index(); // Index defined here

            $table->string('intake_period', 20)->nullable();
            $table->year('intake_year')->nullable();
            $table->enum('study_mode', ['full_time', 'part_time', 'evening', 'weekend', 'online'])->default('full_time');
            $table->enum('student_type', ['new', 'continuing', 'alumnus', 'transfer'])->default('new');
            $table->enum('sponsorship_type', ['self', 'sponsored', 'government', 'scholarship', 'company'])->default('self');

            $table->integer('expected_duration_months')->nullable();
            $table->integer('number_of_terms')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();

            $table->enum('status', [
                'registered', 'in_progress', 'completed', 'dropped',
                'discontinued', 'suspended', 'deferred', 'transferred'
            ])->default('registered');

            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->decimal('total_course_fee', 12, 2)->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->enum('fee_structure_type', ['cdacc', 'nita', 'school_assessment', 'mixed'])->nullable();

            $table->boolean('requires_external_exam')->default(false);
            $table->string('external_exam_body', 100)->nullable();
            $table->string('exam_registration_number', 50)->nullable();
            $table->date('exam_registration_date')->nullable();

            $table->string('final_grade', 10)->nullable();
            $table->string('certificate_number', 50)->nullable();
            $table->date('certificate_issue_date')->nullable();
            $table->string('class_award', 50)->nullable();

            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);

            $table->string('import_batch', 50)->nullable();
            $table->boolean('requires_fee_import')->default(false);

            $table->date('enrollment_date');
            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES ==========
            // Note: enrollment_number is already unique() which creates an index
            // legacy_enrollment_code already has index() on line 11
            $table->index('student_id');
            $table->index('course_id');
            $table->index('campus_id');
            $table->index('status');
            $table->index('intake_year');
            $table->index(['intake_year', 'intake_period']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
