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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            // Campus Information
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->onDelete('set null');

            // Course Information
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('intake_period')->nullable();
            $table->enum('study_mode', ['full_time', 'part_time', 'evening', 'weekend', 'online'])->default('full_time');

            // ID Type Information
            $table->enum('id_type', ['id', 'birth_certificate'])->default('id');
            $table->string('id_number')->nullable();

            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Contact Information
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Kenya');

            // Education Background
            $table->string('education_level')->nullable();
            $table->string('school_name')->nullable();
            $table->year('graduation_year')->nullable();
            $table->string('mean_grade')->nullable();
            $table->enum('application_type', ['new', 'transfer', 'continuing'])->default('new');

            // Documents
            $table->string('id_document')->nullable();
            $table->string('education_certificates')->nullable();
            $table->string('passport_photo')->nullable();

            // Emergency Contact
            $table->text('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('emergency_contact_relationship')->nullable();

            // Special Needs
            $table->text('special_needs')->nullable();

            // Status and Tracking
            $table->enum('status', ['pending', 'under_review', 'accepted', 'rejected', 'waiting_list'])->default('pending');
            $table->string('application_number')->unique()->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Metadata
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
