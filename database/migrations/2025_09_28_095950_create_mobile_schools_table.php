<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();  // for future distance calculations
            $table->decimal('longitude', 10, 7)->nullable(); // for future distance calculations
            $table->string('google_map_link')->nullable();   // Google Maps embed/link
            $table->string('coordinator_name')->nullable();
            $table->string('coordinator_email')->nullable();
            $table->string('coordinator_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('cover_image')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_schools');
    }
};
