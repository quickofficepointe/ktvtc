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
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // ========== INSTITUTION & LINKS ==========
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->onDelete('set null');
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('set null');

            // ========== STUDENT IDENTIFICATION ==========
            $table->string('student_number', 50)->nullable()->unique();
            $table->string('legacy_student_code', 50)->nullable()->index(); // Excel: "SHEP/261/2022"
            $table->string('legacy_code', 50)->nullable()->index(); // Alternative Excel codes

            // ========== PERSONAL INFORMATION ==========
            $table->string('title', 10)->nullable(); // Mr., Ms., Mrs.
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('id_number', 30)->nullable();
            $table->enum('id_type', ['id', 'birth_certificate', 'passport'])->nullable()->default('id');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('marital_status', 20)->nullable();

            // ========== CONTACT INFORMATION ==========
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('county', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 50)->nullable()->default('Kenya');

            // ========== NEXT OF KIN ==========
            $table->string('next_of_kin_name', 150)->nullable();
            $table->string('next_of_kin_phone', 20)->nullable();
            $table->string('next_of_kin_relationship', 50)->nullable();
            $table->text('next_of_kin_address')->nullable();
            $table->string('next_of_kin_email', 150)->nullable();
            $table->string('next_of_kin_id_number', 30)->nullable();

            // ========== EMERGENCY CONTACT ==========
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable();
            $table->string('emergency_contact_phone_alt', 20)->nullable();

            // ========== EDUCATION BACKGROUND ==========
            $table->string('education_level', 100)->nullable();
            $table->string('school_name', 200)->nullable();
            $table->year('graduation_year')->nullable();
            $table->string('mean_grade', 10)->nullable();
            $table->string('kcse_index_number', 30)->nullable();

            // ========== MEDICAL & SPECIAL NEEDS ==========
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->text('special_needs')->nullable();
            $table->string('disability_type', 50)->nullable();

            // ========== DOCUMENTS ==========
            $table->string('id_document_path')->nullable();
            $table->string('passport_photo_path')->nullable();
            $table->string('education_certificates_path')->nullable();
            $table->json('other_documents')->nullable();

            // ========== ADDITIONAL INFO ==========
            $table->string('tshirt_size', 10)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('student_category', ['regular', 'alumnus', 'staff_child', 'sponsored', 'scholarship'])->default('regular');

            // ========== STATUS ==========
            $table->enum('status', [
                'active',           // Currently enrolled
                'inactive',         // Not currently enrolled
                'graduated',        // Completed studies
                'dropped',          // Left without completion
                'suspended',        // Temporarily suspended
                'alumnus',          // Alumni
                'prospective',      // Applied but not enrolled
                'historical'        // Imported from Excel
            ])->default('historical'); // Default to historical for imports

            $table->enum('registration_type', ['excel_import', 'online_application', 'manual_entry'])->default('excel_import');

            // ========== IMPORT METADATA ==========
            $table->string('import_batch', 50)->nullable(); // Track which Excel import
            $table->text('import_notes')->nullable(); // Any import issues
            $table->boolean('requires_cleanup')->default(false); // Flag for data review

            // ========== TIMESTAMPS ==========
            $table->date('registration_date')->nullable();
            $table->date('last_activity_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES (REMOVED DUPLICATES) ==========
            $table->index(['first_name', 'last_name']);
            $table->index('id_number');
            $table->index('phone');
            $table->index('email');
            $table->index('student_number');
            $table->index('status');
            $table->index('campus_id');
            // REMOVED: $table->index('legacy_student_code'); // Already defined above
            $table->index('requires_cleanup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
