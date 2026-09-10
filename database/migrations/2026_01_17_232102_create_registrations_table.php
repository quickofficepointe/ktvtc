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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('campus_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_structure_id')->nullable()->constrained()->onDelete('set null');

            // Registration details
            $table->string('registration_number')->unique();
            $table->string('student_number')->nullable()->unique();
            $table->string('official_email')->nullable();
            $table->string('academic_year');
            $table->string('intake_month');
            $table->date('start_date');
            $table->date('expected_completion_date');
            $table->date('actual_completion_date')->nullable();
            $table->integer('total_course_months');
            $table->integer('current_month')->default(1);
            $table->enum('study_mode', ['full_time', 'part_time', 'online'])->default('full_time');

            // Fee details
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->decimal('tuition_per_month', 10, 2)->default(0);
            $table->decimal('caution_money', 10, 2)->default(0);
            $table->decimal('cdacc_registration_fee', 10, 2)->default(0);
            $table->decimal('cdacc_examination_fee', 10, 2)->default(0);
            $table->decimal('total_course_fee', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->enum('payment_plan', ['monthly', 'quarterly', 'semester', 'annual', 'custom'])->default('monthly');
            $table->json('monthly_payments')->nullable();

            // CDACC details
            $table->string('cdacc_index_number')->nullable();
            $table->string('cdacc_registration_number')->nullable();
            $table->date('cdacc_registration_date')->nullable();
            $table->enum('cdacc_status', ['pending', 'registered', 'active', 'completed'])->default('pending');
            $table->boolean('cdacc_fee_paid')->default(false);

            // Status
            $table->enum('status', [
                'pending', 'provisional', 'registered', 'active',
                'behind_payment', 'completed', 'suspended', 'withdrawn'
            ])->default('pending');

            // Requirements and documents
            $table->json('requirements_checklist')->nullable();
            $table->json('documents_submitted')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('academic_advisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('registration_date');
            $table->integer('monthly_due_day')->default(5);

            // Document paths
            $table->string('admission_letter_path')->nullable();
            $table->string('fee_structure_path')->nullable();
            $table->string('cdacc_registration_form_path')->nullable();
            $table->string('student_id_card_path')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('registration_number');
            $table->index('student_number');
            $table->index('student_id');
            $table->index('course_id');
            $table->index('campus_id');
            $table->index('status');
            $table->index(['academic_year', 'intake_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
