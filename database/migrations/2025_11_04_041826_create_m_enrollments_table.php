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
        Schema::create('m_enrollments', function (Blueprint $table) {
            $table->id('enrollment_id');

            // Foreign keys
            $table->foreignId('student_id')
                  ->constrained('m_students', 'student_id')
                  ->onDelete('cascade');

            $table->foreignId('course_id')
                  ->constrained('m_courses', 'course_id')
                  ->onDelete('cascade');

            $table->foreignId('mobile_school_id')
                  ->nullable()
                  ->constrained('mobile_schools')
                  ->onDelete('set null');

            // Enrollment details
            $table->string('enrollment_code')->unique()->nullable();
            $table->date('enrollment_date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('completion_date')->nullable();

            // Status and progress
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled', 'suspended'])->default('pending');
            $table->decimal('progress', 5, 2)->default(0); // Percentage 0-100
            $table->integer('current_semester')->default(1);

            // Payment and financial information
            $table->decimal('total_fees', 10, 2)->nullable();
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');

            // Academic information
            $table->string('academic_year', 20)->nullable();
            $table->string('semester', 20)->nullable();
            $table->string('batch', 50)->nullable();

            // Certificate information
            $table->string('certificate_number')->unique()->nullable();
            $table->date('certificate_issue_date')->nullable();
            $table->string('certificate_file_path')->nullable();

            // Remarks and metadata
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);

            // Tracking fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint to prevent duplicate enrollments
            $table->unique(['student_id', 'course_id', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_enrollments');
    }
};
