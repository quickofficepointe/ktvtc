<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();

            // Links
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('academic_terms')->nullOnDelete();

            // Capacity
            $table->integer('slots')->default(1);
            $table->integer('slots_taken')->default(0);

            // Application window
            $table->date('application_opens_at')->nullable();
            $table->date('application_closes_at')->nullable();

            // Attachment period
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Description
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->boolean('provides_transport')->default(false);

            // Workflow
            $table->enum('status', ['draft', 'open', 'closed', 'filled'])->default('draft');
            $table->boolean('is_active')->default(true);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'status', 'is_active']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};