<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->text('description');
            $table->string('level')->default('info'); // info, warning, error, critical
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->boolean('read')->default(false);
            $table->timestamps();

            $table->index(['action', 'level']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_logs');
    }
};
