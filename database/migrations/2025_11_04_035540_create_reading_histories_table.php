<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained();
            $table->foreignId('book_id')->constrained();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('pages_read')->default(0);
            $table->enum('reading_status', ['reading', 'completed', 'on_hold', 'dropped'])->default('reading');
            $table->integer('rating')->nullable();
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'book_id']);
            $table->index('reading_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_histories');
    }
};
