<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "2024/2025"
            $table->string('code')->unique(); // e.g., "AY2425"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name'); // e.g., "Semester 1", "Term 1"
            $table->string('code'); // e.g., "S1", "T1"
            $table->date('start_date');
            $table->date('end_date');
            $table->date('registration_deadline');
            $table->date('withdrawal_deadline');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['academic_year_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('academic_years');
    }
};
