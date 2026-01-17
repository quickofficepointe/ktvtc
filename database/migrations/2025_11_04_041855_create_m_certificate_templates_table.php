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
        Schema::create('m_certificate_templates', function (Blueprint $table) {
            $table->id('template_id');

            // Basic template info
            $table->string('template_name', 255);
            $table->string('template_code', 100)->unique();
            $table->text('description')->nullable();

            // Template file and type
            $table->string('template_file'); // Path to PDF template file
            $table->enum('template_type', ['course_completion', 'achievement', 'participation', 'excellence', 'custom'])->default('course_completion');

            // Associated entities
            $table->foreignId('course_id')
                  ->nullable()
                  ->constrained('m_courses', 'course_id')
                  ->onDelete('cascade');

            $table->foreignId('mobile_school_id')
                  ->nullable()
                  ->constrained('mobile_schools')
                  ->onDelete('cascade');

            // Dynamic field configurations
            $table->json('dynamic_fields'); // Fields that will be filled dynamically
            // Example:
            // [
            //     {'field_name': 'student_name', 'x_position': 100, 'y_position': 200, 'font_size': 16, 'font_family': 'Arial'},
            //     {'field_name': 'course_name', 'x_position': 100, 'y_position': 250, 'font_size': 14, 'font_family': 'Arial'},
            //     {'field_name': 'completion_date', 'x_position': 100, 'y_position': 300, 'font_size': 12, 'font_family': 'Arial'},
            //     {'field_name': 'certificate_id', 'x_position': 100, 'y_position': 350, 'font_size': 10, 'font_family': 'Arial'}
            // ]

            // Layout and styling
            $table->json('layout_config')->nullable(); // Margins, page size, etc.
            $table->json('styling')->nullable(); // Colors, fonts, etc.

            // Security features
            $table->string('watermark_text')->nullable();
            $table->string('background_image')->nullable();
            $table->boolean('has_qr_code')->default(false);
            $table->string('qr_code_position')->nullable(); // 'bottom_right', 'bottom_left', etc.

            // Content configuration
            $table->text('signature_line1')->nullable(); // Principal/Dean name
            $table->string('signature_image1')->nullable(); // Principal/Dean signature
            $table->text('signature_line2')->nullable(); // Coordinator name
            $table->string('signature_image2')->nullable(); // Coordinator signature

            // Validity and settings
            $table->integer('validity_months')->nullable(); // Certificate validity period
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_generate')->default(false); // Auto-generate on course completion

            // Approval workflow
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('approver_role_id')->nullable()->constrained('roles');

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_certificate_templates');
    }
};
