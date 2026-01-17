<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('product_name');
            $table->string('slug')->unique();
            $table->foreignId('business_section_id')->constrained('business_sections')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->text('description')->nullable();

            // Product classification - SIMPLIFIED
            $table->enum('product_type', ['food', 'beverage', 'gift', 'raw_material', 'stationery', 'uniform', 'other']);
            $table->enum('unit', ['piece', 'plate', 'bowl', 'cup', 'bottle', 'packet', 'kg', 'gram', 'liter', 'dozen']);

            // Pricing
            $table->decimal('selling_price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();

            // Inventory tracking (for gift shop items)
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('min_stock_level', 12, 3)->default(0);
            $table->decimal('reorder_level', 12, 3)->default(0);
            $table->boolean('track_inventory')->default(true);

            // For cafeteria items only
            $table->boolean('is_production_item')->default(false);
            $table->json('recipe_details')->nullable(); // Only for production items

            // Shop assignment
            $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            // Images (optional)
            $table->string('image')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['business_section_id', 'is_active']);
            $table->index('product_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
