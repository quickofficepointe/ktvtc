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
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->longText('our_story')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->longText('core_values')->nullable(); // JSON or formatted text list
            $table->string('banner_image')->nullable(); // hero/main image
            $table->string('video_url')->nullable(); // optional YouTube/Vimeo link

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
