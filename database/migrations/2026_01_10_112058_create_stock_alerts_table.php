<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->enum('alert_type', ['low_stock', 'out_of_stock', 'expiring_soon', 'overstock', 'slow_moving']);
            $table->decimal('current_stock', 10, 3);
            $table->decimal('threshold', 10, 3)->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'shop_id', 'is_resolved']);
            $table->index('alert_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
