<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('high_school_students', function (Blueprint $table) {
            $table->id();

            // Core student info
            $table->string('admission_number', 50)->unique();
            $table->string('full_name', 255);
            $table->string('class', 20);
            $table->string('profile_picture', 255)->nullable();

            // Parent contact (only what's needed for SMS)
            $table->string('parent_phone', 20)->nullable();
            $table->string('parent_name', 255)->nullable();

            // Status
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active');

            $table->timestamps();

            // Indexes
            $table->index(['admission_number']);
            $table->index(['class']);
            $table->index(['status']);
            $table->index(['parent_phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('high_school_students');
    }
};
