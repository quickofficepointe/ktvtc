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
        Schema::create('m_courses', function (Blueprint $table) {
            $table->id('course_id');
            $table->string('course_name', 255);
            $table->text('course_description')->nullable();
            $table->string('course_code', 50)->unique()->nullable();
            $table->integer('duration')->nullable(); // in hours
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('image_url')->nullable();
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('m_course_categories', 'category_id')
                  ->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_courses');
    }
};
