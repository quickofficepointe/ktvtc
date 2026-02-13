<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_popularities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained();
            $table->integer('borrow_count')->default(0);
            $table->integer('reservation_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->decimal('popularity_score', 5, 2)->default(0);
            $table->timestamps();

            $table->index('popularity_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_popularities');
    }
};
