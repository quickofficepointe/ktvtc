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
        Schema::create('m_subjects', function (Blueprint $table) {
            $table->id('subject_id');
            $table->string('subject_name', 255);
            $table->string('subject_code', 50)->unique()->nullable();
            $table->text('description')->nullable();

            // Link to which course this subject belongs to
            $table->foreignId('course_id')
                  ->nullable()
                  ->constrained('m_courses', 'course_id')
                  ->onDelete('cascade');

            // Subject details
            $table->integer('credit_hours')->default(0);
            $table->integer('duration_weeks')->nullable(); // Duration in weeks
            $table->decimal('price', 8, 2)->nullable(); // If subject has individual pricing

            // Ordering and status
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_core')->default(true); // Core or elective subject

            // Prerequisites (could be self-referencing)
            $table->foreignId('prerequisite_subject_id')
                  ->nullable()
                  ->constrained('m_subjects', 'subject_id')
                  ->onDelete('set null');

            // Learning materials tracking
            $table->string('syllabus_file')->nullable();
            $table->string('cover_image')->nullable();

            // Assessment weights
            $table->integer('exam_weight')->default(70); // Percentage
            $table->integer('assignment_weight')->default(30); // Percentage

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_subjects');
    }
};
