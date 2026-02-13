<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code');
            $table->string('category_name');
            $table->string('slug')->unique();
            $table->foreignId('business_section_id')->constrained('business_sections')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->foreignId('parent_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_section_id', 'category_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
