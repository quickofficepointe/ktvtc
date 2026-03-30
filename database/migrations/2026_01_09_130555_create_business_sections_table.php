<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_code')->unique();
            $table->string('section_name');
            $table->string('slug')->unique(); // For URL-friendly routes
            $table->text('description')->nullable();
            $table->string('section_type'); // Changed from ENUM to VARCHAR for flexibility
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes(); // Add soft deletes for archiving
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_sections');
    }
};
