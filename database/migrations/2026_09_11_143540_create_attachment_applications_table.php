<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachment_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            // Uploads
            $table->string('cv_path')->nullable();
            $table->string('cover_letter_path')->nullable();
            $table->text('student_notes')->nullable();

            // Workflow
            $table->enum('status', [
                'pending',
                'shortlisted',
                'approved',
                'rejected',
                'withdrawn',
            ])->default('pending');

            // Review
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['opportunity_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachment_applications');
    }
};