<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Product snapshot
            $table->string('product_code');
            $table->string('product_name');
            $table->string('unit');

            // Order quantities
            $table->decimal('quantity_ordered', 10, 3);
            $table->decimal('quantity_received', 10, 3)->default(0);
            $table->decimal('quantity_pending', 10, 3)->storedAs('quantity_ordered - quantity_received');

            // Pricing
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);

            // Taxes
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);

            // Received status
            $table->boolean('is_fully_received')->default(false);

            // Notes
            $table->text('notes')->nullable();
            $table->string('specifications')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('purchase_order_id');
            $table->index('product_id');
            $table->index('is_fully_received');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
