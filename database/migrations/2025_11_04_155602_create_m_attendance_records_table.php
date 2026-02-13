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
        Schema::create('m_attendance_records', function (Blueprint $table) {
            $table->id('record_id');

            // Foreign keys
            $table->foreignId('attendance_id')
                  ->constrained('m_attendances', 'attendance_id')
                  ->onDelete('cascade');

            $table->foreignId('student_id')
                  ->nullable()
                  ->constrained('m_students', 'student_id')
                  ->onDelete('cascade');

            $table->foreignId('trainer_id')
                  ->nullable()
                  ->constrained('users') // Assuming trainers are users
                  ->onDelete('cascade');

            // Attendance status
            $table->enum('status', ['present', 'absent', 'late', 'leave', 'half_day', 'excused'])->default('present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->integer('duration_minutes')->nullable(); // Calculated duration

            // Late arrival details
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->text('late_reason')->nullable();

            // Leave details (nullable column only, no FK)
            $table->unsignedBigInteger('leave_application_id')->nullable();
            $table->text('absence_reason')->nullable();

            // Verification data
            $table->string('verification_method')->nullable(); // qr_scan, biometric, manual, etc.
            $table->string('verified_by')->nullable(); // User who verified
            $table->string('device_id')->nullable(); // Mobile device used
            $table->string('location_coordinates')->nullable(); // GPS coordinates

            // Digital evidence
            $table->string('signature_image')->nullable(); // For manual signing
            $table->string('photo_evidence')->nullable(); // Photo proof
            $table->json('verification_data')->nullable(); // Additional verification info

            // Marks/Score (for exam attendance or graded attendance)
            $table->decimal('marks_awarded', 5, 2)->nullable();
            $table->text('performance_notes')->nullable();

            // Status and flags
            $table->boolean('is_verified')->default(true);
            $table->boolean('needs_review')->default(false);
            $table->text('review_notes')->nullable();

            // Tracking
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Prevent duplicate records
            $table->unique(['attendance_id', 'student_id'], 'unique_student_attendance');
            $table->unique(['attendance_id', 'trainer_id'], 'unique_trainer_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_attendance_records');
    }
};
