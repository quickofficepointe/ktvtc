<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->string('image_path'); // image file path
            $table->string('caption')->nullable(); // optional caption per image
            $table->integer('order')->default(0); // ordering if needed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
$table->string('ip_address')->nullable();
$table->string('user_agent')->nullable();
  $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};
