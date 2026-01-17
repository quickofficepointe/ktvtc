<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained();
            $table->integer('year');
            $table->integer('target_books');
            $table->integer('completed_books')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['member_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_challenges');
    }
};
