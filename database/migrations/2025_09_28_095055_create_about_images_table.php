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
        Schema::create('about_images', function (Blueprint $table) {
            $table->id();
    $table->foreignId('about_page_id')->constrained('about_pages')->cascadeOnDelete();
    $table->string('image_path');
    $table->string('caption')->nullable();
    $table->integer('order')->default(0);
  $table->boolean('is_active')->default(true);
    // 🔐 Audit & Tracking
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->string('ip_address')->nullable();
    $table->string('user_agent')->nullable();

    $table->softDeletes();
    $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_images');
    }
};
