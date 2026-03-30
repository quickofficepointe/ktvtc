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
        Schema::create('m_attendances', function (Blueprint $table) {
            $table->id('attendance_id');

            // Core attendance information
            $table->date('attendance_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('session_name', 255)->nullable(); // Morning session, Workshop Day 1, etc.

            // Polymorphic relationship - can be for different types
            $table->string('attendable_type'); // 'workshop', 'class', 'exam', 'training', 'event'
            $table->unsignedBigInteger('attendable_id'); // ID of the workshop, class, exam, etc.

            // Foreign keys
            $table->foreignId('subject_id')
                  ->nullable()
                  ->constrained('m_subjects', 'subject_id')
                  ->onDelete('cascade');

            $table->foreignId('course_id')
                  ->nullable()
                  ->constrained('m_courses', 'course_id')
                  ->onDelete('cascade');

            $table->foreignId('mobile_school_id')
                  ->nullable()
                  ->constrained('mobile_schools')
                  ->onDelete('cascade');

            // Location information
            $table->string('venue', 255)->nullable();
            $table->string('room', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Attendance method
            $table->enum('recording_method', ['manual', 'qr_code', 'biometric', 'nfc', 'online', 'mobile_app'])->default('manual');
            $table->string('qr_code_data')->nullable(); // For QR code-based attendance
            $table->boolean('is_geofenced')->default(false); // Require location verification

            // Status and settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false); // Prevent further modifications
            $table->boolean('allow_late_marking')->default(true);
            $table->integer('late_threshold_minutes')->default(15); // Minutes after which marked late

            // Statistics (cached)
            $table->integer('total_expected')->default(0);
            $table->integer('total_present')->default(0);
            $table->integer('total_absent')->default(0);
            $table->integer('total_late')->default(0);
            $table->integer('total_leave')->default(0);

            // Notes and metadata
            $table->text('topic_covered')->nullable();
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable(); // Additional flexible data

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['attendable_type', 'attendable_id']);
            $table->index(['attendance_date', 'mobile_school_id']);
            $table->unique(['attendable_type', 'attendable_id', 'attendance_date', 'session_name'], 'unique_attendance_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_attendances');
    }
};
