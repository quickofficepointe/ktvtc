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
        Schema::create('m_students', function (Blueprint $table) {
            $table->id('student_id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->unique()->nullable();
            $table->string('phone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();

            // Foreign key to mobile_schools table
            $table->foreignId('mobile_school_id')
                  ->nullable()
                  ->constrained('mobile_schools')
                  ->onDelete('set null');

            // Student status and information
            $table->string('student_code')->unique()->nullable();
            $table->date('enrollment_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('profile_image')->nullable();

            // Guardian/Parent information
            $table->string('guardian_name', 150)->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_email', 150)->nullable();
            $table->text('guardian_address')->nullable();

            // Tracking fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_students');
    }
};
