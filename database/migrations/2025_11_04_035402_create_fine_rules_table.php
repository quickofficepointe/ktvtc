<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fine_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('fine_amount', 8, 2);
            $table->enum('calculation_type', ['daily', 'fixed', 'percentage'])->default('daily');
            $table->integer('grace_period_days')->default(0);
            $table->integer('max_fine_days')->nullable();
            $table->decimal('max_fine_amount', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_rules');
    }
};
