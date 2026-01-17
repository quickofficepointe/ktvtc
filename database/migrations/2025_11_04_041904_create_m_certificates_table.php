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
        Schema::create('m_certificates', function (Blueprint $table) {
            $table->id('certificate_id');

            // Foreign keys
            $table->foreignId('template_id')
                  ->constrained('m_certificate_templates', 'template_id')
                  ->onDelete('cascade');

            $table->foreignId('student_id')
                  ->constrained('m_students', 'student_id')
                  ->onDelete('cascade');

            $table->foreignId('enrollment_id')
                  ->constrained('m_enrollments', 'enrollment_id')
                  ->onDelete('cascade');

            $table->foreignId('course_id')
                  ->constrained('m_courses', 'course_id')
                  ->onDelete('cascade');

            // Certificate identification
            $table->string('certificate_number')->unique();
            $table->string('serial_number')->unique()->nullable();

            // Certificate content (dynamic fields)
            $table->json('certificate_data');
            // Example:
            // {
            //     'student_name': 'John Doe',
            //     'course_name': 'Web Development Bootcamp',
            //     'completion_date': '2024-01-15',
            //     'grade_achieved': 'A',
            //     'duration': '3 months',
            //     'issue_date': '2024-01-20'
            // }

            // File information
            $table->string('generated_pdf_path'); // Path to generated PDF
            $table->string('file_size')->nullable();
            $table->string('file_hash')->nullable(); // For verification

            // Dates
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->dateTime('generated_at');

            // Status and verification
            $table->enum('status', ['draft', 'generated', 'issued', 'revoked', 'expired'])->default('draft');
            $table->boolean('is_verified')->default(true);
            $table->string('verification_url')->nullable(); // Unique URL for verification
            $table->string('qr_code_data')->nullable(); // Data for QR code

            // Issuance details
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('issuance_remarks')->nullable();

            // Revocation details (if applicable)
            $table->boolean('is_revoked')->default(false);
            $table->date('revoked_date')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revocation_reason')->nullable();

            // Digital signature
            $table->string('digital_signature')->nullable();
            $table->string('signature_timestamp')->nullable();

            // Access and sharing
            $table->boolean('allow_download')->default(true);
            $table->boolean('allow_sharing')->default(true);
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Ensure unique certificate per enrollment
            $table->unique(['enrollment_id', 'template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_certificates');
    }
};
