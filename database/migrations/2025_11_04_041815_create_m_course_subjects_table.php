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
        Schema::create('m_course_subjects', function (Blueprint $table) {
            $table->id('course_subject_id');

            // Foreign keys to courses and subjects
            $table->foreignId('course_id')
                  ->constrained('m_courses', 'course_id')
                  ->onDelete('cascade');

            $table->foreignId('subject_id')
                  ->constrained('m_subjects', 'subject_id')
                  ->onDelete('cascade');

            // Pivot-specific fields
            $table->integer('semester')->nullable(); // Which semester this subject is taught
            $table->integer('year')->nullable(); // Which year of the course
            $table->boolean('is_compulsory')->default(true);
            $table->integer('credit_hours')->default(0); // Can override subject's default credit hours
            $table->integer('sort_order')->default(0);

            // Unique constraint to prevent duplicate course-subject combinations
            $table->unique(['course_id', 'subject_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_course_subjects');
    }
};
