<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Stock levels
            $table->decimal('system_stock', 10, 3)->comment('Stock according to system before adjustment');
            $table->decimal('physical_stock', 10, 3)->comment('Actual counted stock');
            $table->decimal('adjustment_quantity', 10, 3)->comment('Difference (positive = add, negative = deduct)');
            $table->string('unit');

            // Value
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_value', 10, 2)->nullable();

            // Reason details
            $table->text('item_reason')->nullable();
            $table->json('details')->nullable(); // Additional details like expiry dates, batch numbers

            $table->timestamps();

            // Indexes
            $table->index('adjustment_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};
