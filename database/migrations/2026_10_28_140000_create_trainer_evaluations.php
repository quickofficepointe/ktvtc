<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Students evaluate the trainer of each unit they take, every term.
 * Doing all of them is one of the two conditions for the term's exam card.
 *
 * student_id is kept only to know who has finished; answers are never shown
 * with a name, and trainers only see averages once enough students responded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('type', 10)->default('rating'); // rating (1-5) | text
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('evaluation_questions')->insert(array_map(fn ($q) => $q + ['created_at' => $now, 'updated_at' => $now], [
            ['text' => 'The trainer explained the unit content clearly.', 'type' => 'rating', 'is_required' => true, 'sort_order' => 1],
            ['text' => 'The trainer came to class prepared and on time.', 'type' => 'rating', 'is_required' => true, 'sort_order' => 2],
            ['text' => 'Practical sessions helped me build real skills.', 'type' => 'rating', 'is_required' => true, 'sort_order' => 3],
            ['text' => 'Assessments were fair and matched what was taught.', 'type' => 'rating', 'is_required' => true, 'sort_order' => 4],
            ['text' => 'The trainer was approachable and helped when I struggled.', 'type' => 'rating', 'is_required' => true, 'sort_order' => 5],
            ['text' => 'Overall, how would you rate this trainer?', 'type' => 'rating', 'is_required' => true, 'sort_order' => 6],
            ['text' => 'Anything the trainer should keep doing or change?', 'type' => 'text', 'is_required' => false, 'sort_order' => 7],
        ]));

        Schema::create('trainer_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_registration_id')->constrained('term_registrations')->cascadeOnDelete();
            $table->foreignId('course_unit_id')->constrained('course_units')->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete(); // trainer at the time
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();         // completion only
            $table->unsignedSmallInteger('term_number');
            $table->timestamps();

            $table->unique(['term_registration_id', 'course_unit_id'], 'uq_evaluation_per_unit');
            $table->index(['trainer_id', 'course_unit_id']);
        });

        Schema::create('trainer_evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_evaluation_id')->constrained('trainer_evaluations')->cascadeOnDelete();
            $table->foreignId('evaluation_question_id')->constrained('evaluation_questions')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('answer')->nullable();
            $table->timestamps();

            $table->index('evaluation_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_evaluation_answers');
        Schema::dropIfExists('trainer_evaluations');
        Schema::dropIfExists('evaluation_questions');
    }
};
