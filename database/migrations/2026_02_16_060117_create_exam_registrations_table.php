<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== SIMPLIFIED EXAM REGISTRATIONS ==========
        Schema::create('exam_registrations', function (Blueprint $table) {
            $table->id();

            // ========== LINKS (KEEP ONLY STUDENT - NO exam_type_id!) ==========
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();

            // ========== EXAM DETAILS (NO SEPARATE TABLES NEEDED) ==========
            $table->enum('exam_body', ['KNEC', 'NITA', 'CDACC', 'TVETA', 'OTHER'])->default('CDACC');
            $table->string('exam_type'); // "Certificate", "Diploma", "Artisan"
            $table->string('exam_code')->nullable(); // e.g., "CDACC/ICT/2024"

            // ========== REGISTRATION DETAILS ==========
            $table->string('registration_number')->nullable();
            $table->string('index_number')->nullable();
            $table->date('registration_date')->nullable();
            $table->date('exam_date')->nullable();

            // ========== RESULTS ==========
            $table->date('result_date')->nullable();
            $table->string('result')->nullable(); // "Pass", "Fail", "Distinction"
            $table->string('grade')->nullable(); // "A", "B", "C"
            $table->decimal('score', 5, 2)->nullable();

            // ========== CERTIFICATE ==========
            $table->string('certificate_number')->nullable();
            $table->date('certificate_issue_date')->nullable();
            $table->string('certificate_path')->nullable();

            // ========== STATUS (SIMPLIFIED) ==========
            $table->enum('status', [
                'pending', 'registered', 'active', 'completed', 'failed'
            ])->default('pending');

            // ========== METADATA ==========
            $table->text('remarks')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES ==========
            $table->index('student_id');
            $table->index('enrollment_id');
            $table->index('exam_body');
            $table->index('status');
            $table->index('exam_date');
            $table->index('registration_number');
            $table->index('certificate_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_registrations');
    }
};
