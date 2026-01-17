<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cafeteria_daily_productions', function (Blueprint $table) {
            $table->id();
            $table->date('production_date');
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();

            // Production summary (calculated from items)
            $table->integer('total_items_produced')->default(0);
            $table->integer('total_items_sold')->default(0);
            $table->integer('total_items_wasted')->default(0);
            $table->decimal('total_raw_material_cost', 10, 2)->default(0);
            $table->decimal('total_production_cost', 10, 2)->default(0);
            $table->decimal('total_sales_value', 10, 2)->default(0);

            // Status
            $table->enum('status', ['draft', 'in_progress', 'completed', 'verified'])->default('draft');

            // Verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('challenges')->nullable();
            $table->text('suggestions')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // One production record per shop per day
            $table->unique(['production_date', 'shop_id']);

            // Indexes for fast queries
            $table->index('production_date');
            $table->index(['shop_id', 'production_date']);
        });

        // DAILY PRODUCTION ITEMS (What was produced)
        Schema::create('daily_production_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_production_id')->constrained('cafeteria_daily_productions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Production quantities
            $table->decimal('planned_quantity', 10, 3)->default(0);
            $table->decimal('actual_quantity', 10, 3)->default(0);
            $table->decimal('quantity_sold', 10, 3)->default(0);
            $table->decimal('quantity_wasted', 10, 3)->default(0);
            $table->decimal('remaining_quantity', 10, 3)->storedAs('actual_quantity - quantity_sold - quantity_wasted');

            // Cost calculation
            $table->decimal('unit_production_cost', 10, 2)->nullable();
            $table->decimal('total_production_cost', 10, 2)->nullable();
            $table->decimal('unit_selling_price', 10, 2);
            $table->decimal('total_sales_value', 10, 2)->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // One item per product per daily production
            $table->unique(['daily_production_id', 'product_id']);

            // Indexes
            $table->index('product_id');
        });

        // DAILY RAW MATERIAL USAGE (What raw materials were used)
        Schema::create('daily_raw_material_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_production_id')->constrained('cafeteria_daily_productions')->cascadeOnDelete();
            $table->foreignId('raw_material_product_id')->constrained('products')->cascadeOnDelete()->comment('The product that is a raw material');
            $table->foreignId('produced_product_id')->nullable()->constrained('products')->nullOnDelete()->comment('Which product this raw material was used for');

            // Usage details
            $table->decimal('quantity_used', 10, 3);
            $table->string('unit');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);

            // Recipe reference (optional)
            $table->foreignId('recipe_id')->nullable()->constrained('recipes')->nullOnDelete();

            // Notes
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            // Indexes
            $table->index(['daily_production_id', 'raw_material_product_id']);
            $table->index('produced_product_id');
        });

        // RECIPES TABLE (For production items - optional but useful)
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete()->comment('The final product');
            $table->string('recipe_name');
            $table->decimal('batch_size', 10, 3)->default(1)->comment('How many final products from this recipe');
            $table->string('batch_unit')->default('piece');
            $table->text('instructions')->nullable();
            $table->decimal('preparation_time_minutes', 8, 2)->nullable();
            $table->decimal('cooking_time_minutes', 8, 2)->nullable();
            $table->integer('servings')->nullable();
            $table->boolean('is_active')->default(true);

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        // RECIPE INGREDIENTS (What goes into each recipe)
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('raw_material_product_id')->constrained('products')->cascadeOnDelete()->comment('The raw material product');

            // Quantity required per batch
            $table->decimal('quantity_required', 10, 3);
            $table->string('unit');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();

            // Optional instructions for this ingredient
            $table->text('preparation_notes')->nullable();

            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // One ingredient per recipe
            $table->unique(['recipe_id', 'raw_material_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('daily_raw_material_usage');
        Schema::dropIfExists('daily_production_items');
        Schema::dropIfExists('cafeteria_daily_productions');
    }
};
