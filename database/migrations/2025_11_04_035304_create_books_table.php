<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn')->unique()->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('publisher')->nullable();
            $table->string('edition')->nullable();
            $table->string('language')->default('English');
            $table->integer('page_count')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->foreignId('category_id')->constrained('book_categories');
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->boolean('is_available')->default(true);
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index('title');
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
