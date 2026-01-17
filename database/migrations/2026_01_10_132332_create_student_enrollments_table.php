<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();

            // Student Reference
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            // Course Information
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('intake_id')->constrained('course_intakes')->cascadeOnDelete();

            // Enrollment Details
            $table->string('enrollment_number')->unique()->nullable();
            $table->enum('study_mode', ['full_time', 'part_time', 'evening', 'weekend', 'online']);
            $table->enum('enrollment_type', ['new', 'transfer_in', 'continuing', 'alumni_return']);
            $table->string('year_of_study')->default('1'); // 1, 2, 3, etc.

            // Enrollment Status
            $table->enum('status', [
                'pending',      // Applied for enrollment
                'approved',     // Enrollment approved
                'active',       // Currently enrolled
                'completed',    // Course completed
                'deferred',     // Studies deferred
                'suspended',    // Enrollment suspended
                'transferred',  // Transferred to another course
                'withdrawn',    // Withdrawn from course
                'graduated',    // Graduated from course
                'expelled'      // Expelled from course
            ])->default('pending');

            // Academic Progress
            $table->decimal('gpa', 4, 2)->nullable();
            $table->enum('class', ['distinction', 'credit', 'pass', 'fail'])->nullable();

            // Dates
            $table->date('enrollment_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->date('withdrawal_date')->nullable();

            // Transfer Information (if applicable)
            $table->foreignId('previous_enrollment_id')->nullable()->constrained('student_enrollments')->nullOnDelete();
            $table->text('transfer_reason')->nullable();
            $table->json('transferred_credits')->nullable(); // JSON of transferred course units

            // Financial Information
            $table->decimal('total_fees', 12, 2)->default(0);
            $table->decimal('fees_paid', 12, 2)->default(0);
            $table->decimal('fees_balance', 12, 2)->default(0);

            // Sponsorship
            $table->enum('sponsorship_type', ['self', 'government', 'company', 'sponsor', 'scholarship'])->default('self');
            $table->string('sponsor_name')->nullable();
            $table->string('sponsor_contact')->nullable();

            // System Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['student_id', 'course_id', 'intake_id']);
            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'intake_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
