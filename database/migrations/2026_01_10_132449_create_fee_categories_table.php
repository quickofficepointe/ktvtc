<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Tuition", "Registration", "Examination"
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('intake_id')->nullable()->constrained('course_intakes')->nullOnDelete();
            $table->foreignId('fee_category_id')->constrained('fee_categories')->cascadeOnDelete();
            $table->string('name'); // e.g., "First Year Tuition Fee"
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('frequency', ['one_time', 'per_semester', 'per_year', 'per_month', 'per_unit']);
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'intake_id', 'fee_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('fee_categories');
    }
};
