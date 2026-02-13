<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old table
        Schema::dropIfExists('m_certificates');

        // Create clean new table
        Schema::create('m_certificates', function (Blueprint $table) {
            $table->id('certificate_id');
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('m_certificate_templates', 'template_id')
                ->nullOnDelete();
            $table->foreignId('student_id')
                ->constrained('m_students', 'student_id');
            $table->foreignId('enrollment_id')
                ->constrained('m_enrollments', 'enrollment_id');
            $table->foreignId('course_id')
                ->constrained('m_courses', 'course_id');
            $table->string('certificate_number')->unique();
            $table->string('generated_pdf_path')->nullable();
            $table->date('issue_date');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: one certificate type per student per course
            $table->unique(['student_id', 'course_id', 'template_id'], 'unique_certificate_per_type');
        });
    }

    public function down(): void
    {
        // Just drop the table - this is a fresh start
        Schema::dropIfExists('m_certificates');
    }
};
