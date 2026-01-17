<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('goods_received_notes')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();

            // Quantities
            $table->decimal('quantity_received', 10, 3);
            $table->decimal('quantity_accepted', 10, 3);
            $table->decimal('quantity_rejected', 10, 3)->default(0);
            $table->string('unit');

            // Pricing
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_value', 10, 2);

            // Batch information
            $table->string('batch_number')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Quality
            $table->enum('condition', ['good', 'damaged', 'expired', 'wrong_item'])->default('good');
            $table->text('quality_notes')->nullable();

            // Storage location
            $table->string('storage_location')->nullable();
            $table->string('shelf_number')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('grn_id');
            $table->index('product_id');
            $table->index('purchase_order_item_id');
            $table->index('batch_number');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};
