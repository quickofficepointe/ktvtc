<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->nullable(); // e.g., CSC101

            // Course Duration & Timing - ADD THIS
            $table->string('duration')->nullable(); // e.g., "3 months", "6 weeks"
            $table->string('total_hours')->nullable(); // e.g., "120 hours"
            $table->string('schedule')->nullable(); // e.g., "Mon-Wed-Fri, 6-8 PM"

            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('fees_breakdown')->nullable(); // structured JSON for tuition, registration, etc.
   $table->string('delivery_mode')->nullable(); // Keep as string for comma-separated values

            // FIX THIS TYPO: tlongTextext → longText
            $table->longText('what_you_will_learn')->nullable();

            $table->string('cover_image')->nullable();

            // Other useful additions:
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->boolean('featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
