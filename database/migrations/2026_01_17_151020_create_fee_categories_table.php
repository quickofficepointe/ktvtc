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
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();

            // ========== BASIC INFO ==========
            $table->string('name'); // Tuition, Registration, Examination, etc.
            $table->string('code')->unique(); // TUITION, REGISTRATION, EXAMINATION
            $table->text('description')->nullable();

            // ========== CATEGORY PROPERTIES ==========
            $table->enum('frequency', ['once', 'per_term', 'per_year', 'per_month', 'per_course'])->default('once');
            $table->boolean('is_refundable')->default(false); // Caution fee is refundable
            $table->boolean('is_mandatory')->default(false); // Must be paid by all students
            $table->boolean('is_taxable')->default(false); // For future VAT/tax considerations
            $table->boolean('is_active')->default(true);

            // ========== DISPLAY & ORGANIZATION ==========
            $table->integer('sort_order')->default(0);
            $table->string('icon')->nullable(); // For UI display
            $table->string('color')->nullable()->default('#3B82F6'); // For UI

            // ========== SUGGESTED ITEMS (For admin convenience) ==========
            $table->json('suggested_items')->nullable();
            // Example for REGISTRATION: ["ID Fee", "Medical Fee", "Caution Fee", "Sports Fee"]

            // ========== CAMPUS SPECIFIC (Optional for future) ==========
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            // NULL = Global category (all campuses)
            // Specific campus_id = Campus-specific category

            // ========== AUDIT TRAIL ==========
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ========== INDEXES ==========
            $table->index('code');
            $table->index('campus_id');
            $table->index('is_active');
            $table->index('sort_order');
            $table->index('frequency');
            $table->index(['campus_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_categories');
    }
};
