<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Terms counted from each student's own intake.
 *
 *   courses.term_count / term_length / term_length_unit  -> how a course is split into terms
 *   course_term_fees                                      -> fee breakdown per course per term number,
 *                                                            versioned by the intake date it applies from
 *   term_registrations                                    -> a student registered for term N of their enrollment
 *   enrollments.billing_mode                              -> 'course' (one course fee, shared across terms; all
 *                                                            existing students) or 'term' (each term billed on registration)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedSmallInteger('term_count')->nullable()->after('duration');
            $table->unsignedSmallInteger('term_length')->nullable()->after('term_count');
            $table->string('term_length_unit', 10)->nullable()->after('term_length'); // weeks | months
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('billing_mode', 10)->default('course')->after('balance');
        });

        Schema::create('course_term_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->unsignedSmallInteger('term_number');
            $table->date('effective_from');           // applies to students whose intake is on/after this date
            $table->json('items');                    // [{"name": "Tuition", "amount": 15000}, ...]
            $table->decimal('total', 12, 2);
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['course_id', 'term_number', 'effective_from'], 'uq_course_term_fee_version');
        });

        Schema::create('term_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->unsignedSmallInteger('term_number');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->decimal('fee_amount', 12, 2);      // this term's share of what the student owes
            $table->json('fee_items')->nullable();     // breakdown copied at registration (term billing)
            $table->boolean('bills_enrollment')->default(false); // true: fee_amount was added to enrollments.total_fees
            $table->foreignId('course_term_fee_id')->nullable()->constrained('course_term_fees')->nullOnDelete();
            $table->string('registration_type', 10);   // self | staff | legacy
            $table->string('status', 15)->default('registered'); // registered | cancelled
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('override_reason')->nullable();
            $table->timestamp('exam_card_downloaded_at')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'term_number']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_registrations');
        Schema::dropIfExists('course_term_fees');
        Schema::table('enrollments', fn (Blueprint $t) => $t->dropColumn('billing_mode'));
        Schema::table('courses', fn (Blueprint $t) => $t->dropColumn(['term_count', 'term_length', 'term_length_unit']));
    }
};
