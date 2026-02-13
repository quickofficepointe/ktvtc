<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weeding_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->date('last_borrowed_date')->nullable();
            $table->integer('days_since_last_borrow')->default(0);
            $table->integer('total_borrows')->default(0);
            $table->string('condition')->default('poor');
            $table->text('review_notes')->nullable();
            $table->date('review_date')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weeding_candidates');
    }
};
