<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('business_section_id')->constrained('business_sections')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            // Delivery details
            $table->date('delivery_date');
            $table->timestamp('received_date')->useCurrent();
            $table->string('delivery_note_number')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();

            // Totals
            $table->integer('total_items')->default(0);
            $table->decimal('total_quantity', 12, 3)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);

            // Quality check
            $table->enum('quality_status', ['pending', 'passed', 'failed', 'partial'])->default('pending');
            $table->text('quality_notes')->nullable();
            $table->foreignId('quality_checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('quality_checked_at')->nullable();

            // Receipt status
            $table->enum('status', ['draft', 'pending', 'partially_received', 'completed', 'rejected'])->default('draft');

            // Personnel
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('grn_number');
            $table->index('purchase_order_id');
            $table->index('supplier_id');
            $table->index('delivery_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_received_notes');
    }
};
