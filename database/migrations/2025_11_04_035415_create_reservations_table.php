<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained();
            $table->foreignId('book_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->date('reservation_date');
            $table->date('expiry_date');
            $table->enum('status', ['active', 'fulfilled', 'cancelled', 'expired'])->default('active');
            $table->integer('queue_position')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
