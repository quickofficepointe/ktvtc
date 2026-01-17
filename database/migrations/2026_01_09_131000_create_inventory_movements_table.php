<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number')->unique();

            // What moved
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            // Movement type
            $table->enum('movement_type', [
                'purchase',           // Buying from supplier
                'sale',               // Selling to customer
                'transfer_in',        // Transfer from another shop
                'transfer_out',       // Transfer to another shop
                'adjustment_in',      // Manual adjustment (add stock)
                'adjustment_out',     // Manual adjustment (remove stock)
                'production_in',      // Production output (food made)
                'production_usage',   // Raw material used in production
                'wastage',            // Items wasted/expired
                'damaged',            // Items damaged
                'return_in',          // Customer return
                'return_out',         // Return to supplier
                'opening_stock',      // Initial stock
                'physical_count',     // Physical stock count adjustment
                'reserved',           // Reserved for order
                'unreserved'          // Reservation cancelled
            ]);

            // Quantities
            $table->decimal('quantity', 10, 3);
            $table->string('unit');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();

            // Stock levels before and after
            $table->decimal('previous_stock', 10, 3);
            $table->decimal('new_stock', 10, 3);

            // Reference documents
            $table->string('reference_number')->nullable(); // Invoice, PO, GRN, Sale No, etc.
            $table->string('reference_type')->nullable(); // purchase_order, grn, sale, transfer, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the reference document

            // For transfers
            $table->foreignId('from_shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->foreignId('to_shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->string('transfer_reason')->nullable();

            // For adjustments/wastage
            $table->text('reason')->nullable();
            $table->enum('adjustment_category', ['stock_take', 'damage', 'theft', 'expiry', 'error', 'other'])->nullable();

            // Batch information (for tracking expiry)
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            // Approved by (for adjustments/wastage)
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Recorded by
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('movement_date')->useCurrent();

            // Notes
            $table->text('notes')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['product_id', 'shop_id']);
            $table->index('movement_date');
            $table->index('movement_type');
            $table->index('reference_number');
            $table->index(['reference_type', 'reference_id']);
            $table->index(['shop_id', 'product_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
