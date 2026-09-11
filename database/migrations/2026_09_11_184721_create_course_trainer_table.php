<?php
// database/migrations/2026_09_11_000001_create_course_trainer_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_trainer', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // lead | assistant | co-trainer
            $table->string('role', 30)->default('trainer');

            $table->boolean('is_active')->default(true);

            $table->foreignId('assigned_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('assigned_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_trainer');
    }
};