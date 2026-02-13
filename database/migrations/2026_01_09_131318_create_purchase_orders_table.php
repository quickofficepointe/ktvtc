<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('business_section_id')->constrained('business_sections')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            // Order details
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->text('delivery_address')->nullable();
            $table->enum('delivery_method', ['pickup', 'supplier_delivery', 'courier'])->default('supplier_delivery');

            // Totals
            $table->integer('total_items')->default(0);
            $table->decimal('total_quantity', 12, 3)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('delivery_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            // Status
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'ordered', 'partial', 'received', 'cancelled', 'closed'])->default('draft');

            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();

            // Created by
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('po_number');
            $table->index('status');
            $table->index(['supplier_id', 'order_date']);
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
