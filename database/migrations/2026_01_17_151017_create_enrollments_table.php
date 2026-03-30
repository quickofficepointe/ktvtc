<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== SIMPLIFIED ENROLLMENTS TABLE ==========
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            // ========== LINKS (KEEP ONLY WHAT YOU NEED) ==========
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();

            // ========== DENORMALIZED STUDENT DATA (FOR SPEED) ==========
            $table->string('student_name');
            $table->string('student_number', 50)->nullable();

            // ========== COURSE INFO ==========
            $table->string('course_name');
            $table->string('course_code', 50)->nullable();
            $table->string('department', 100)->nullable();

            // ========== INTAKE INFO ==========
            $table->year('intake_year');
            $table->string('intake_month', 20)->nullable(); // 'January', 'May', 'September'
            $table->date('enrollment_date')->nullable();

            // ========== STUDY MODE (KEEP THIS) ==========
            $table->enum('study_mode', ['full_time', 'part_time', 'evening', 'weekend', 'online'])
                  ->default('full_time');

            // ========== STUDENT TYPE (KEEP THIS) ==========
            $table->enum('student_type', ['new', 'continuing', 'alumnus', 'transfer'])
                  ->default('new');

            // ========== SPONSORSHIP (KEEP THIS) ==========
            $table->enum('sponsorship_type', ['self', 'sponsored', 'government', 'scholarship', 'company'])
                  ->default('self');

            // ========== DURATION (KEEP SIMPLE) ==========
            $table->integer('duration_months')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();

            // ========== FINANCIAL - SIMPLIFIED! ==========
            $table->decimal('total_fees', 12, 2)->default(0);      // Total they must pay
            $table->decimal('amount_paid', 12, 2)->default(0);     // Running total paid
            $table->decimal('balance', 12, 2)->default(0);         // total_fees - amount_paid (calculated field)

            // ========== STATUS ==========
            $table->enum('status', [
                'active', 'graduated', 'completed', 'dropped',
                'suspended', 'deferred', 'pending'
            ])->default('active');

            // ========== EXTERNAL EXAM (OPTIONAL) ==========
            $table->boolean('requires_external_exam')->default(false);
            $table->string('exam_body', 50)->nullable(); // 'KNEC', 'NITA', 'CDACC'

            // ========== IMPORT TRACKING (FOR YOUR CSV DATA) ==========
            $table->string('legacy_code', 100)->nullable();
            $table->string('import_batch', 50)->nullable();
            $table->boolean('needs_review')->default(false);

            // ========== NOTES ==========
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);

            // ========== AUDIT ==========
            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES ==========
            $table->index('student_id');
            $table->index('course_id');
            $table->index('campus_id');
            $table->index('status');
            $table->index('balance');              // Added index for balance column
            $table->index('intake_year');
            $table->index('is_active');
            $table->index('legacy_code');
            $table->index(['intake_year', 'intake_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
