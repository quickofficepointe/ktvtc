<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('term_id')
                ->nullable()
                ->after('campus_id')
                ->constrained('academic_terms')
                ->nullOnDelete()
                ->comment('Academic term this enrollment is for.');

            $table->boolean('is_self_enrolled')
                ->default(false)
                ->after('term_id')
                ->comment('True if the student enrolled themselves via the portal.');

            $table->foreignId('enrolled_by_user_id')
                ->nullable()
                ->after('is_self_enrolled')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('The user account that triggered self-enrollment.');

            $table->timestamp('self_enrolled_at')
                ->nullable()
                ->after('enrolled_by_user_id');

            $table->index(['student_id', 'term_id'], 'idx_enrollment_student_term');
            $table->index(['is_self_enrolled', 'created_at'], 'idx_enrollment_self_recent');
            $table->index(['term_id', 'status'], 'idx_enrollment_term_status');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->unique(
                ['student_id', 'course_id', 'term_id'],
                'uq_student_course_term'
            );
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropUnique('uq_student_course_term');
            $table->dropIndex('idx_enrollment_student_term');
            $table->dropIndex('idx_enrollment_self_recent');
            $table->dropIndex('idx_enrollment_term_status');
            $table->dropForeign(['term_id']);
            $table->dropForeign(['enrolled_by_user_id']);
            $table->dropColumn([
                'term_id',
                'is_self_enrolled',
                'enrolled_by_user_id',
                'self_enrolled_at',
            ]);
        });
    }
};