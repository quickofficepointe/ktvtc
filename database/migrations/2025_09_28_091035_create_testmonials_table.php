<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // student/visitor name
            $table->string('image_path')->nullable(); // optional profile image
            $table->text('review'); // testimonial content
            $table->tinyInteger('rating')->unsigned()->default(5); // star rating 1–5
            $table->boolean('is_approved')->default(false); // must be approved by admin

            // tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
