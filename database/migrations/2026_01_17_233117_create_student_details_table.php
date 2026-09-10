<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();

            // ==================== LINK ====================
            $table->foreignId('student_id')->unique()->constrained('users')->onDelete('cascade');

            // ==================== PERSONAL IDENTIFICATION ====================
            $table->string('national_id_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('birth_certificate_number')->nullable();
            $table->string('kra_pin')->nullable();
            $table->string('nssf_number')->nullable();
            $table->string('nhif_number')->nullable();

            // ==================== CONTACT DETAILS (UPDATABLE) ====================
            $table->string('personal_email')->nullable();
            $table->string('personal_phone')->nullable();
            $table->string('alternative_phone')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('town')->nullable();
            $table->string('county')->nullable();

            // ==================== NEXT OF KIN (VERIFIED) ====================
            $table->string('next_of_kin_name');
            $table->string('next_of_kin_relationship');
            $table->string('next_of_kin_phone');
            $table->string('next_of_kin_email')->nullable();
            $table->text('next_of_kin_address');
            $table->string('next_of_kin_id_number')->nullable();
            $table->string('next_of_kin_occupation')->nullable();

            // ==================== EMERGENCY CONTACT (SECOND CONTACT) ====================
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_alternate_phone')->nullable();
            $table->text('emergency_contact_address')->nullable();

            // ==================== EDUCATION BACKGROUND (PERMANENT) ====================
            $table->string('secondary_school_name');
            $table->year('secondary_school_completion_year');
            $table->string('kcse_index_number')->nullable();
            $table->string('kcse_mean_grade')->nullable();
            $table->text('secondary_school_address')->nullable();

            $table->string('primary_school_name')->nullable();
            $table->year('primary_school_completion_year')->nullable();
            $table->string('knec_index_primary')->nullable();
            $table->text('primary_school_address')->nullable();

            // ==================== PREVIOUS EDUCATION (FOR TRANSFERS) ====================
            $table->string('previous_institution')->nullable();
            $table->string('previous_course')->nullable();
            $table->year('previous_start_year')->nullable();
            $table->year('previous_end_year')->nullable();
            $table->string('previous_qualification')->nullable();
            $table->string('transfer_reason')->nullable();
            $table->string('previous_transcript_path')->nullable();

            // ==================== EMPLOYMENT DETAILS (CAN CHANGE) ====================
            $table->boolean('is_employed')->default(false);
            $table->string('employer_name')->nullable();
            $table->string('employer_address')->nullable();
            $table->string('employer_phone')->nullable();
            $table->string('job_title')->nullable();
            $table->string('employment_duration')->nullable();
            $table->string('employer_email')->nullable();

            // ==================== MEDICAL INFORMATION (UPDATABLE) ====================
            $table->string('blood_group')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_illnesses')->nullable();
            $table->text('disabilities')->nullable();
            $table->text('special_needs')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_phone')->nullable();
            $table->string('doctor_address')->nullable();
            $table->string('medical_insurance_provider')->nullable();
            $table->string('medical_insurance_number')->nullable();

            // ==================== SPONSORSHIP DETAILS (CAN CHANGE) ====================
            $table->enum('sponsorship_type', [
                'self',
                'parent',
                'spouse',
                'employer',
                'government',
                'county',
                'helb',
                'scholarship',
                'bursary',
                'other'
            ])->default('self');

            $table->string('sponsor_name')->nullable();
            $table->string('sponsor_id_number')->nullable();
            $table->string('sponsor_phone')->nullable();
            $table->string('sponsor_email')->nullable();
            $table->text('sponsor_address')->nullable();
            $table->string('sponsor_relationship')->nullable();
            $table->string('sponsor_occupation')->nullable();
            $table->string('sponsorship_letter_path')->nullable();

            // ==================== HELB/STUDENT LOAN (APPLIED LATER) ====================
            $table->boolean('has_helb_loan')->default(false);
            $table->string('helb_loan_number')->nullable();
            $table->string('helb_disbursement_code')->nullable();
            $table->decimal('helb_loan_amount', 10, 2)->nullable();
            $table->string('helb_bank_name')->nullable();
            $table->string('helb_bank_account')->nullable();
            $table->string('helb_application_date')->nullable();
            $table->string('helb_approval_date')->nullable();
            $table->string('helb_letter_path')->nullable();

            // ==================== BANK DETAILS (FOR REFUNDS) ====================
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();

            // ==================== DOCUMENTS UPLOADED (TRACKING) ====================
            $table->json('documents_uploaded')->nullable();

            // ==================== WORKSHOP/TECHNICAL DETAILS (TVET SPECIFIC) ====================
            $table->date('workshop_safety_training_date')->nullable();
            $table->string('workshop_safety_certificate_path')->nullable();
            $table->string('tool_kit_issued')->nullable();
            $table->date('tool_kit_issue_date')->nullable();
            $table->string('tool_kit_return_date')->nullable();
            $table->string('protective_clothing_issued')->nullable();
            $table->string('protective_clothing_size')->nullable();
            $table->string('workshop_access_level')->nullable();

            // ==================== INDUSTRIAL ATTACHMENT (TVET REQUIREMENT) ====================
            $table->string('industrial_attachment_company')->nullable();
            $table->string('industrial_attachment_supervisor')->nullable();
            $table->string('industrial_attachment_phone')->nullable();
            $table->string('industrial_attachment_email')->nullable();
            $table->date('industrial_attachment_start_date')->nullable();
            $table->date('industrial_attachment_end_date')->nullable();
            $table->text('industrial_attachment_address')->nullable();
            $table->string('industrial_attachment_report_path')->nullable();
            $table->string('industrial_attachment_supervisor_report_path')->nullable();
            $table->enum('industrial_attachment_status', ['pending', 'ongoing', 'completed', 'extended'])->nullable();

            // ==================== EXTRACURRICULAR ACTIVITIES ====================
            $table->json('hobbies_interests')->nullable();
            $table->json('sports_activities')->nullable();
            $table->json('clubs_societies')->nullable();
            $table->json('leadership_positions')->nullable();
            $table->json('awards_achievements')->nullable();

            // ==================== ACADEMIC PROGRESS (UPDATED REGULARLY) ====================
            $table->decimal('cumulative_gpa', 3, 2)->nullable();
            $table->string('current_class_position')->nullable();
            $table->integer('attendance_percentage')->nullable();
            $table->text('academic_notes')->nullable();
            $table->json('disciplinary_records')->nullable();

            // ==================== ALUMNI INFORMATION (POST-GRADUATION) ====================
            $table->string('employment_status_after_graduation')->nullable();
            $table->string('employer_after_graduation')->nullable();
            $table->string('job_title_after_graduation')->nullable();
            $table->decimal('starting_salary', 10, 2)->nullable();
            $table->string('graduation_year')->nullable();
            $table->boolean('is_alumni')->default(false);
            $table->date('alumni_membership_date')->nullable();

            // ==================== ADMINISTRATIVE ====================
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_updated_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ==================== INDEXES ====================
            $table->index('national_id_number');
            $table->index('kcse_index_number');
            $table->index('helb_loan_number');
            $table->index('next_of_kin_phone');
            $table->index('emergency_contact_phone');
            $table->index('sponsorship_type');
            $table->index(['secondary_school_name', 'secondary_school_completion_year']);
            $table->index('is_alumni');
            $table->index('industrial_attachment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_details');
    }
};
