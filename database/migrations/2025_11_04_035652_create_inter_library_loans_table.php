<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inter_library_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requesting_branch_id')->constrained('branches');
            $table->foreignId('lending_branch_id')->constrained('branches');
            $table->foreignId('book_id')->constrained();
            $table->foreignId('member_id')->constrained();
            $table->date('request_date');
            $table->date('approval_date')->nullable();
            $table->date('shipping_date')->nullable();
            $table->date('received_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('return_date')->nullable();
            $table->enum('status', ['requested', 'approved', 'shipped', 'received', 'returned', 'cancelled'])->default('requested');
            $table->text('notes')->nullable();
            $table->decimal('shipping_cost', 8, 2)->default(0);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inter_library_loans');
    }
};
