<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            // Stock levels
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('reserved_stock', 10, 3)->default(0);
            $table->decimal('available_stock', 10, 3)->storedAs('current_stock - reserved_stock');

            // Stock value
            $table->decimal('average_unit_cost', 10, 2)->nullable();
            $table->decimal('stock_value', 10, 2)->storedAs('current_stock * average_unit_cost');

            // Stock metrics
            $table->integer('days_supply')->nullable()->comment('Estimated days of stock remaining');
            $table->decimal('monthly_usage', 10, 3)->default(0)->comment('Average monthly usage');
            $table->decimal('reorder_quantity', 10, 3)->nullable();

            // Last movement
            $table->timestamp('last_movement_at')->nullable();
            $table->foreignId('last_movement_id')->nullable()->constrained('inventory_movements')->nullOnDelete();

            // Stock dates
            $table->date('last_received_date')->nullable();
            $table->date('last_sold_date')->nullable();
            $table->date('last_adjusted_date')->nullable();

            // Stock alerts
            $table->boolean('low_stock_alert')->default(false);
            $table->boolean('out_of_stock_alert')->default(false);
            $table->timestamp('last_alert_sent_at')->nullable();

            // Batch/Expiry tracking
            $table->string('current_batch')->nullable();
            $table->date('earliest_expiry_date')->nullable();

            $table->timestamps();

            // Unique constraint
            $table->unique(['product_id', 'shop_id']);

            // Indexes
            $table->index(['shop_id', 'available_stock']);
            $table->index('low_stock_alert');
            $table->index('out_of_stock_alert');
            $table->index('earliest_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
