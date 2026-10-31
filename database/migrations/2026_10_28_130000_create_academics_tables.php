<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Academics: units taught in each term of a course, results per student per unit.
 *
 * Tracks (set per course):
 *   cdacc_cbet / knec_cbet: theory + practical -> Mastery / Proficient / Competent / Not Yet Competent
 *   internal:               CAT + exam         -> Distinction / Credit / Pass / Fail
 * Weights and bands live in grading_scales and are editable by the academic admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('academic_track', 15)->nullable()->after('term_length_unit');
        });

        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->string('track', 15)->unique();
            $table->string('name');
            $table->string('component_a', 30);          // label of first score, e.g. Theory / CAT
            $table->string('component_b', 30);          // label of second score, e.g. Practical / Exam
            $table->unsignedTinyInteger('weight_a');    // % ; weight_a + weight_b = 100
            $table->unsignedTinyInteger('weight_b');
            $table->json('bands');                      // [{"min":80,"label":"Mastery","passed":true}, ...] highest first
            $table->timestamps();
        });

        $now = now();
        $cbet = [
            ['min' => 80, 'label' => 'Mastery', 'passed' => true],
            ['min' => 65, 'label' => 'Proficient', 'passed' => true],
            ['min' => 50, 'label' => 'Competent', 'passed' => true],
            ['min' => 0, 'label' => 'Not Yet Competent', 'passed' => false],
        ];
        DB::table('grading_scales')->insert([
            ['track' => 'cdacc_cbet', 'name' => 'CDACC CBET', 'component_a' => 'Theory', 'component_b' => 'Practical', 'weight_a' => 50, 'weight_b' => 50, 'bands' => json_encode($cbet), 'created_at' => $now, 'updated_at' => $now],
            ['track' => 'knec_cbet', 'name' => 'KNEC CBET', 'component_a' => 'Theory', 'component_b' => 'Practical', 'weight_a' => 50, 'weight_b' => 50, 'bands' => json_encode($cbet), 'created_at' => $now, 'updated_at' => $now],
            ['track' => 'internal', 'name' => 'Internal (college)', 'component_a' => 'CAT', 'component_b' => 'Exam', 'weight_a' => 30, 'weight_b' => 70, 'bands' => json_encode([
                ['min' => 80, 'label' => 'Distinction', 'passed' => true],
                ['min' => 65, 'label' => 'Credit', 'passed' => true],
                ['min' => 50, 'label' => 'Pass', 'passed' => true],
                ['min' => 0, 'label' => 'Fail', 'passed' => false],
            ]), 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::create('course_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedSmallInteger('term_number');
            $table->string('code', 40)->nullable();
            $table->string('title');
            $table->string('unit_type', 10)->default('core');   // basic | common | core | other
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'term_number']);
            $table->index('trainer_id');
        });

        Schema::create('unit_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_unit_id')->constrained('course_units')->cascadeOnDelete();
            $table->foreignId('term_registration_id')->constrained('term_registrations')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt')->default(1);   // 2+ = re-sit
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete(); // who taught/assessed it
            $table->decimal('score_a', 5, 2)->nullable();          // theory or CAT, out of 100
            $table->decimal('score_b', 5, 2)->nullable();          // practical or exam, out of 100
            $table->decimal('total', 5, 2)->nullable();
            $table->string('grade', 30)->nullable();
            $table->boolean('passed')->nullable();
            $table->string('status', 12)->default('draft');        // draft | submitted | published
            $table->string('remarks')->nullable();
            $table->string('returned_reason')->nullable();         // admin sent it back to the trainer
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['course_unit_id', 'enrollment_id', 'attempt'], 'uq_unit_result_attempt');
            $table->index(['student_id', 'status']);
            $table->index(['course_unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_results');
        Schema::dropIfExists('course_units');
        Schema::dropIfExists('grading_scales');
        Schema::table('courses', fn (Blueprint $t) => $t->dropColumn('academic_track'));
    }
};
