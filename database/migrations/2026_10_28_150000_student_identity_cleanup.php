<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Student numbers, duplicates and online admissions.
 *
 *   number_sequences      one college-wide running number for student numbers (CODE/NNN/YEAR),
 *                         starting after the highest number already issued
 *   students.merged_into_id  set on a duplicate record after staff merge it into the one kept
 *   applications.*        link an accepted online application to the student/enrollment it became
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->string('name', 30)->primary();
            $table->unsignedBigInteger('next_value');
            $table->timestamps();
        });

        // Highest running number in CODE/NNN/YEAR numbers ever issued (ignoring year-like values
        // produced by an old bug, e.g. STU/2026/2026) or plain numeric numbers.
        $max = 0;
        $numbers = DB::table('students')->pluck('student_number')
            ->merge(DB::table('enrollments')->pluck('student_number'))
            ->merge(DB::table('students')->whereNotNull('legacy_student_code')->pluck('legacy_student_code'));
        foreach ($numbers as $number) {
            $number = trim((string) $number);
            if (preg_match('~^[A-Z0-9]+/+(\d{1,5})/\d{4}$~i', $number, $m) || preg_match('~^(\d{1,5})$~', $number, $m)) {
                $n = (int) $m[1];
                if ($n < 1900 || $n > 2100) {
                    $max = max($max, $n);
                }
            }
        }
        DB::table('number_sequences')->insert(['name' => 'student', 'next_value' => $max + 1, 'created_at' => now(), 'updated_at' => now()]);

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('merged_into_id')->nullable()->after('id')->constrained('students')->nullOnDelete();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->timestamp('admitted_at')->nullable();
            $table->foreignId('admitted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['enrollment_id']);
            $table->dropForeign(['admitted_by']);
            $table->dropColumn(['student_id', 'enrollment_id', 'admitted_at', 'admitted_by']);
        });
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['merged_into_id']);
            $table->dropColumn('merged_into_id');
        });
        Schema::dropIfExists('number_sequences');
    }
};
