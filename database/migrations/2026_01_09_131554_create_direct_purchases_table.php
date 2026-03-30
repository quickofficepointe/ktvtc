<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direct_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('business_section_id')->constrained('business_sections')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            // Purchase details
            $table->date('purchase_date');
            $table->string('supplier_name')->nullable(); // For cash purchases without supplier record
            $table->string('supplier_phone')->nullable();
            $table->text('delivery_details')->nullable();

            // Totals
            $table->integer('total_items')->default(0);
            $table->decimal('total_quantity', 12, 3)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);

            // Payment
            $table->enum('payment_method', ['cash', 'mpesa', 'bank_transfer', 'credit'])->default('cash');
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('paid');
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();

            // Items (stored as JSON for simplicity)
            $table->json('items');
            /*
            items format:
            [
                {
                    "product_id": 1,
                    "product_code": "CF-001",
                    "product_name": "Flour",
                    "quantity": 50,
                    "unit": "kg",
                    "unit_price": 120.00,
                    "total_price": 6000.00,
                    "batch_number": "BATCH-001",
                    "expiry_date": "2024-12-31"
                }
            ]
            */

            // Notes
            $table->text('notes')->nullable();

            // Personnel
            $table->foreignId('purchased_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('invoice_number');
            $table->index('purchase_date');
            $table->index('supplier_id');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direct_purchases');
    }
};
