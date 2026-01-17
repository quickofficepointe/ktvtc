<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->foreignId('book_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->enum('status', ['available', 'borrowed', 'reserved', 'maintenance', 'lost'])->default('available');
            $table->string('condition')->default('good');
            $table->text('notes')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_price', 8, 2)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('barcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
