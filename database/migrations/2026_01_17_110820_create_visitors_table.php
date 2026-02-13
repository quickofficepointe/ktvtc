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
       // database/migrations/xxxx_xx_xx_create_visitors_table.php
// In your migration file
Schema::create('visitors', function (Blueprint $table) {
    $table->id();
    $table->string('ip_address');
    $table->string('session_id')->nullable(); // Add this
    $table->string('user_agent')->nullable();
    $table->string('path')->nullable();
    $table->string('referrer')->nullable();
    $table->string('country')->nullable();
    $table->string('device_type')->nullable();
    $table->string('browser')->nullable();
    $table->timestamps();

    // Add indexes for better query performance
    $table->index(['created_at']);
    $table->index(['ip_address']);
    $table->index(['session_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
