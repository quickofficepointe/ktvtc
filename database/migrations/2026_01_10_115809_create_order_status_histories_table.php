<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();

            // Foreign key to sales table
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();

            // Status and notes
            $table->enum('status', [
                'pending', 'confirmed', 'preparing', 'ready',
                'out_for_delivery', 'delivered', 'picked_up',
                'cancelled', 'on_hold', 'completed'  // ✅ Added 'completed'
            ]);
            $table->text('notes')->nullable();

            // Who changed the status
         $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes for performance
            $table->index('sale_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
