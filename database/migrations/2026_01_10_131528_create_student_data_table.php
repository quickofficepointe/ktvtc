<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Link to application (if from online application)
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();

            // Link to user account (for portal access)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Student Identification
            $table->string('admission_number')->unique()->nullable();
            $table->string('student_number')->unique()->nullable();
            $table->string('registration_number')->unique()->nullable();

            // Personal Information (from application)
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('id_number')->unique();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->default('single');

            // Contact Information
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Kenya');

            // Next of Kin/Emergency Contact
            $table->string('next_of_kin_name')->nullable();
            $table->string('next_of_kin_relationship')->nullable();
            $table->string('next_of_kin_phone')->nullable();
            $table->text('next_of_kin_address')->nullable();

            // Education Background (from application)
            $table->string('education_level')->nullable();
            $table->string('school_name')->nullable();
            $table->integer('graduation_year')->nullable();
            $table->string('mean_grade')->nullable();

            // Medical Information
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->string('blood_group')->nullable();

            // Documents (copied from application)
            $table->string('id_document')->nullable();
            $table->string('education_certificates')->nullable();
            $table->string('passport_photo')->nullable();
            $table->string('birth_certificate')->nullable();

            // Status Tracking
            $table->enum('status', [
                'prospective',    // Applied but not admitted
                'admitted',       // Given admission number
                'registered',     // Completed registration
                'active',         // Currently studying
                'suspended',      // Temporarily inactive
                'deferred',       // Postponed studies
                'transferred',    // Transferred out
                'withdrawn',      // Voluntarily left
                'graduated',      // Completed studies
                'alumni',         // Alumni status
                'expelled'        // Forcibly removed
            ])->default('prospective');

            $table->date('admission_date')->nullable();
            $table->date('registration_date')->nullable();
            $table->date('graduation_date')->nullable();

            // System Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['admission_number', 'status']);
            $table->index(['student_number', 'status']);
            $table->index('email');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
