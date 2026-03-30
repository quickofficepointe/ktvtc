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
        Schema::create('m_exams', function (Blueprint $table) {
            $table->id('exam_id');

            // Basic exam info
            $table->string('exam_name', 255);
            $table->string('exam_code', 50)->unique()->nullable();
            $table->text('description')->nullable();

            // Foreign keys
            $table->foreignId('subject_id')
                  ->constrained('m_subjects', 'subject_id')
                  ->onDelete('cascade');

            $table->foreignId('course_id')
                  ->constrained('m_courses', 'course_id')
                  ->onDelete('cascade');

            // Exam type and category (dynamic - can add more types later)
            $table->enum('exam_type', ['cat1', 'cat2', 'cat3', 'main_exam', 'assignment', 'practical', 'project', 'quiz', 'final'])->default('cat1');
            $table->string('exam_category', 100)->nullable(); // Additional categorization

            // Timing and scheduling
            $table->date('exam_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->nullable(); // Exam duration in minutes

            // Grading system
            $table->decimal('total_marks', 8, 2); // Total possible marks
            $table->decimal('passing_marks', 8, 2)->nullable(); // Passing marks
            $table->integer('weightage')->default(0); // Percentage weight in final grade

            // Exam configuration
            $table->integer('number_of_questions')->nullable();
            $table->json('question_types')->nullable(); // ['multiple_choice', 'essay', 'practical']
            $table->json('sections')->nullable(); // Exam sections with their marks

            // Status and settings
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_retake')->default(false);
            $table->integer('max_attempts')->default(1);

            // Location and instructions
            $table->string('venue', 255)->nullable();
            $table->text('instructions')->nullable();
            $table->text('materials_allowed')->nullable();

            // Academic context
            $table->string('academic_year', 20)->nullable();
            $table->string('semester', 20)->nullable();
            $table->integer('term')->nullable(); // For schools with terms

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate exams for same subject and type
           $table->unique(
    ['subject_id', 'course_id', 'exam_type', 'academic_year', 'semester'],
    'm_exams_unique_idx' // short name ≤ 64 chars
);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_exams');
    }
};
