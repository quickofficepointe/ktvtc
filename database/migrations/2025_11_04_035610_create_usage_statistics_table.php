<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('stat_date');
            $table->foreignId('branch_id')->constrained();
            $table->integer('total_borrows')->default(0);
            $table->integer('total_returns')->default(0);
            $table->integer('total_reservations')->default(0);
            $table->integer('new_members')->default(0);
            $table->integer('active_members')->default(0);
            $table->decimal('total_fines', 8, 2)->default(0);
            $table->decimal('collected_fines', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['stat_date', 'branch_id']);
            $table->index('stat_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_statistics');
    }
};
