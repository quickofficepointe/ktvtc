<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pre_order_id')
                  ->constrained('pre_orders')
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();

            // Snapshot fields (so historical orders survive product edits)
            $table->string('product_name', 255);
            $table->string('product_code', 100)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 12, 2);

            $table->timestamps();

            $table->index('pre_order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_order_items');
    }
};
