<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A student asks to move from one course to another. Academics (start term),
 * then finance (money moved), then admissions approve; the student then gets
 * a new enrollment with its own number and the old one is marked 'transferred'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_enrollment_id')->constrained('enrollments')->cascadeOnDelete();
            $table->foreignId('to_course_id')->constrained('courses');
            $table->text('reason');
            $table->string('status', 20)->default('academics')->index(); // academics, finance, admin, approved, rejected, cancelled

            $table->unsignedTinyInteger('start_term')->nullable();
            $table->text('academics_note')->nullable();
            $table->foreignId('academics_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('academics_at')->nullable();

            $table->decimal('amount_moved', 12, 2)->nullable();
            $table->boolean('clear_old_balance')->default(true);
            $table->text('finance_note')->nullable();
            $table->foreignId('finance_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finance_at')->nullable();

            $table->text('admin_note')->nullable();
            $table->foreignId('admin_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admin_at')->nullable();

            $table->string('rejected_at_stage', 20)->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('new_enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE enrollments MODIFY status ENUM('active','graduated','completed','dropped','suspended','deferred','pending','transferred') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        Schema::dropIfExists('course_change_requests');
        DB::statement("UPDATE enrollments SET status = 'dropped' WHERE status = 'transferred'");
        DB::statement("ALTER TABLE enrollments MODIFY status ENUM('active','graduated','completed','dropped','suspended','deferred','pending') NOT NULL DEFAULT 'active'");
    }
};
