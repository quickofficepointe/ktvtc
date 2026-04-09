<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('language')->default('English');
            $table->foreignId('category_id')->constrained('e_book_categories')->onDelete('restrict');
            $table->string('file_path');
            $table->string('cover_image')->nullable();
            $table->string('file_format')->default('PDF');
            $table->integer('file_size')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('title');
            $table->index('author');
            $table->index('category_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_books');
    }
};
