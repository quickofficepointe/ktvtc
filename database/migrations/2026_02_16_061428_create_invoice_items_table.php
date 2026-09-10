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
       // 15_invoice_items.php (NEW - Links invoices to enrollment_fee_items)
// 15_invoice_items.php (NEW - Links invoices to enrollment_fee_items)
Schema::create('invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
    $table->foreignId('enrollment_fee_item_id')->constrained()->onDelete('cascade');
    $table->string('description');
    $table->integer('quantity')->default(1);
    $table->decimal('unit_price', 12, 2);
    $table->decimal('discount', 12, 2)->default(0);
    $table->decimal('tax', 12, 2)->default(0);
    $table->decimal('total', 12, 2);
    $table->timestamps();

    $table->index('invoice_id');
    $table->index('enrollment_fee_item_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
