<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Student-portal quiz: one row per attempt, so students can see their scores over time
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('quiz', 50)->default('portal-basics');
            $table->unsignedTinyInteger('score');
            $table->unsignedTinyInteger('total');
            $table->json('answers');
            $table->timestamps();
            $table->index(['user_id', 'quiz']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_quiz_attempts');
    }
};
