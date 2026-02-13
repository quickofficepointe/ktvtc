<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('shop_code')->unique();
            $table->string('shop_name');
            $table->string('slug')->unique(); // For URL-friendly routes
            $table->foreignId('business_section_id')->constrained('business_sections')->cascadeOnDelete();
            $table->string('location')->nullable();
            $table->string('branch')->nullable(); // Ngong, Town, etc.
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('opening_hours')->nullable();
            $table->boolean('is_active')->default(true);

            // Physical location details
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('room_number')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes(); // Add soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
