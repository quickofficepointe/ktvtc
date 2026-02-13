<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();

            // Sale source
            $table->foreignId('business_section_id')->constrained('business_sections')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->enum('sale_type', ['pos', 'online', 'mobile', 'preorder', 'delivery']);
            $table->enum('channel', ['cafeteria', 'gift_shop', 'student_store', 'website', 'mobile_app']);
 $table->string('kcb_invoice_number')->nullable();
            // Customer information
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
$table->enum('customer_type', ['student', 'staff', 'visitor', 'online_customer', 'walk_in'])->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();

            // Order information
            $table->integer('total_items')->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            // Payment information
            $table->enum('payment_method', ['mpesa', 'cash', 'card', 'bank_transfer', 'credit', 'wallet', 'multiple'])->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->string('mpesa_receipt')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('bank_reference')->nullable();

            // KCB MPesa Integration
            $table->string('checkout_request_id')->nullable();
            $table->string('merchant_request_id')->nullable();
            $table->json('kcb_response')->nullable();
            $table->timestamp('payment_requested_at')->nullable();
            $table->timestamp('payment_confirmed_at')->nullable();

            // Order status (for online/delivery)
            $table->enum('order_status', [
                'pending',          // Order placed but not confirmed
                'confirmed',        // Order confirmed
                'preparing',        // Being prepared (cafeteria)
                'ready',           // Ready for pickup/delivery
                'out_for_delivery', // Being delivered
                'delivered',       // Delivered to customer
                'picked_up',       // Customer picked up
                'cancelled',       // Order cancelled
                'on_hold'          // Order on hold
            ])->default('pending');

            // Delivery information (for online orders)
            $table->string('delivery_address')->nullable();
            $table->string('delivery_instructions')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->enum('delivery_status', ['pending', 'assigned', 'picked', 'on_way', 'delivered'])->nullable();
            $table->foreignId('delivery_person_id')->nullable()->constrained('users')->nullOnDelete();

            // Processing information
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete()->comment('For POS sales');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete()->comment('Who prepared/processed');
            $table->timestamp('processed_at')->nullable();

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->timestamp('sale_date')->useCurrent();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['shop_id', 'sale_date']);
            $table->index('invoice_number');
            $table->index('payment_status');
            $table->index('order_status');
            $table->index('customer_id');
            $table->index('checkout_request_id');
            $table->index(['business_section_id', 'sale_date']);
        });

        // SALE ITEMS TABLE
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Product snapshot at time of sale
            $table->string('product_name');
            $table->string('product_code');
            $table->text('description')->nullable();
            $table->string('unit');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('quantity', 10, 3);
            $table->decimal('total_price', 10, 2);

            // Discounts
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2);

            // For cafeteria items
            $table->boolean('is_production_item')->default(false);
            $table->json('customizations')->nullable()->comment('For food items: extra sauce, no onions, etc.');

            // Notes for this item
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('sale_id');
            $table->index('product_id');
        });

        // PAYMENT TRANSACTIONS TABLE (For multiple payment methods)


        // ORDER STATUS HISTORY (Track order status changes)
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 'ready',
                'out_for_delivery', 'delivered', 'picked_up',
                'cancelled', 'on_hold'
            ]);
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Indexes
            $table->index('sale_id');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
