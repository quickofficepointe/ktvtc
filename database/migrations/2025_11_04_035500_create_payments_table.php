<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained();
            $table->foreignId('transaction_id')->nullable()->constrained();
            $table->string('payment_method')->default('cash');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_type', ['fine', 'membership', 'donation', 'other']);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->date('payment_date');
            $table->string('received_by')->nullable();
            $table->timestamps();

            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
