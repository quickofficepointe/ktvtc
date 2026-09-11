<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();

            // Links — all nullable so a student can enter a self-found placement
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('attachment_applications')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('academic_terms')->nullOnDelete();

            // Company snapshot (always stored, whether registered or self-found)
            $table->string('company_name');
            $table->string('company_sector')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->boolean('company_registered')->default(false);  // true if company_id is set

            // On-site supervisor
            $table->string('supervisor_name');
            $table->string('supervisor_position')->nullable();
            $table->string('supervisor_phone');
            $table->string('supervisor_email')->nullable();

            // Period
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_weeks')->nullable();

            // Work description
            $table->text('job_description')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->text('student_notes')->nullable();

            // Supporting documents
            $table->string('acceptance_letter_path')->nullable();
            $table->string('placement_letter_path')->nullable();

            // Workflow
            $table->enum('status', [
                'draft',
                'submitted',
                'needs_revision',
                'active',
                'completed',
                'graded',
                'rejected',
            ])->default('draft');

            // Review by ILO
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_remarks')->nullable();

            // Completion & grading
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->string('grade')->nullable();
            $table->text('grader_remarks')->nullable();

            // ILO visit tracking
            $table->date('last_visit_date')->nullable();
            $table->text('last_visit_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'term_id']);
            $table->index(['student_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};