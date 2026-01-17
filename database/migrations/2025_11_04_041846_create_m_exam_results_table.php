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
        Schema::create('m_exam_results', function (Blueprint $table) {
            $table->id('result_id');

            // Foreign keys
            $table->foreignId('exam_id')
                  ->constrained('m_exams', 'exam_id')
                  ->onDelete('cascade');

            $table->foreignId('student_id')
                  ->constrained('m_students', 'student_id')
                  ->onDelete('cascade');

            $table->foreignId('enrollment_id')
                  ->constrained('m_enrollments', 'enrollment_id')
                  ->onDelete('cascade');

            // Marks and grading
            $table->decimal('marks_obtained', 8, 2);
            $table->decimal('total_marks', 8, 2);
            $table->decimal('percentage', 5, 2); // Calculated percentage

            // Grade and status
            $table->string('grade', 10)->nullable(); // A, B, C, D, F
            $table->string('grade_point', 5)->nullable(); // 4.0, 3.5, etc.
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'graded', 'absent', 'cheated', 'special_case'])->default('pending');

            // Attempt tracking
            $table->integer('attempt_number')->default(1);
            $table->dateTime('attempt_date')->nullable();
            $table->integer('time_taken_minutes')->nullable(); // Time taken to complete

            // Section-wise marks (for detailed breakdown)
            $table->json('section_marks')->nullable();
            $table->json('question_wise_marks')->nullable();

            // Grading metadata
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('graded_at')->nullable();
            $table->text('grading_notes')->nullable();

            // Special cases
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_retake')->default(false);
            $table->boolean('is_supplementary')->default(false);
            $table->text('absent_reason')->nullable();

            // Ranking and statistics
            $table->integer('class_rank')->nullable();
            $table->integer('total_students')->nullable();
            $table->decimal('class_average', 5, 2)->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate results for same exam and student attempt
            $table->unique(['exam_id', 'student_id', 'attempt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_exam_results');
    }
};
