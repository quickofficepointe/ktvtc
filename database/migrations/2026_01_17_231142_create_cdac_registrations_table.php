<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cdacc_registrations', function (Blueprint $table) {
            $table->id();

            // ==================== LINKS ====================
            $table->foreignId('registration_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_structure_id')->nullable()->constrained()->onDelete('set null');

            // ==================== CDACC OFFICIAL DETAILS ====================
            $table->string('cdacc_number')->unique()->nullable();
            $table->string('cdacc_index_number')->unique()->nullable();
            $table->string('cdacc_learner_id')->unique()->nullable();
            $table->string('cdacc_batch_number')->nullable();

            // ==================== CDACC PROGRAM DETAILS ====================
            $table->string('cdacc_program_code');
            $table->string('cdacc_program_name');
            $table->string('cdacc_qualification_title');
            $table->enum('cdacc_qualification_level', ['artisan', 'certificate', 'diploma', 'higher_diploma']);
            $table->string('cdacc_trade_area');
            $table->string('cdacc_occupation')->nullable();

            // ==================== REGISTRATION DATES ====================
            $table->date('cdacc_registration_date');
            $table->date('cdacc_registration_expiry');
            $table->date('cdacc_examination_date')->nullable();
            $table->date('cdacc_certification_date')->nullable();

            // ==================== CDACC CENTER DETAILS ====================
            $table->string('cdacc_center_number');
            $table->string('cdacc_center_name');
            $table->string('cdacc_assessor_number')->nullable();
            $table->string('cdacc_moderator_number')->nullable();

            // ==================== MODULES/UNITS REGISTRATION ====================
            $table->json('registered_modules')->nullable();
            $table->integer('total_modules')->default(0);
            $table->integer('core_modules')->default(0);
            $table->integer('elective_modules')->default(0);

            // ==================== CDACC FEES ====================
            $table->decimal('cdacc_registration_fee', 10, 2)->default(0);
            $table->decimal('cdacc_examination_fee', 10, 2)->default(0);
            $table->decimal('cdacc_certification_fee', 10, 2)->default(0);
            $table->decimal('cdacc_moderation_fee', 10, 2)->default(0);
            $table->decimal('cdacc_total_fee', 10, 2)->default(0);

            $table->enum('cdacc_fee_status', ['pending', 'partial', 'paid', 'waived'])->default('pending');
            $table->date('cdacc_fee_payment_date')->nullable();
            $table->string('cdacc_payment_reference')->nullable();

            // ==================== ASSESSMENT DETAILS ====================
            $table->enum('assessment_type', ['cba', 'written', 'practical', 'oral', 'portfolio'])->default('cba');
            $table->json('assessment_components')->nullable();
            $table->string('assessment_venue')->nullable();

            // ==================== STATUS TRACKING ====================
            $table->enum('cdacc_status', [
                'pending', 'submitted', 'approved', 'registered', 'active',
                'under_assessment', 'completed', 'certified', 'suspended',
                'withdrawn', 'expired'
            ])->default('pending');

            $table->enum('certification_status', [
                'not_applicable', 'pending', 'eligible', 'awarded', 'withheld', 'revoked'
            ])->default('not_applicable');

            // ==================== INTEGRATION TRACKING ====================
            $table->timestamp('submitted_to_cdacc_at')->nullable();
            $table->timestamp('approved_by_cdacc_at')->nullable();
            $table->timestamp('last_sync_with_cdacc_at')->nullable();

            $table->string('cdacc_api_reference')->nullable();
            $table->json('cdacc_api_response')->nullable();
            $table->enum('sync_status', ['pending', 'success', 'failed', 'retry'])->default('pending');
            $table->text('sync_notes')->nullable();

            // ==================== DOCUMENTS ====================
            $table->string('cdacc_reg_form_path')->nullable();
            $table->string('cdacc_adm_letter_path')->nullable();
            $table->string('cdacc_exam_card_path')->nullable();
            $table->string('cdacc_certificate_path')->nullable();
            $table->string('cdacc_transcript_path')->nullable();

            // ==================== RESULTS TRACKING ====================
            $table->json('module_results')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->string('overall_grade')->nullable();
            $table->string('competency_level')->nullable();

            // ==================== ADMINISTRATIVE ====================
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ==================== INDEXES - FIXED ====================
            $table->index('cdacc_number');
            $table->index('cdacc_index_number');
            $table->index('cdacc_learner_id');
            $table->index('cdacc_center_number');
            $table->index('cdacc_registration_date');
            $table->index('sync_status');
            $table->index(['cdacc_program_code', 'cdacc_status']);
            $table->index(['student_id', 'certification_status']);

            // ❗ THIS WAS THE PROBLEM - REPLACED WITH SHORT CUSTOM NAME
            $table->index(['cdacc_center_number', 'cdacc_registration_date'], 'cdacc_center_reg_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cdacc_registrations');
    }
};
