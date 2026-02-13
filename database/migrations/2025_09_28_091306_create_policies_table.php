<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g. Privacy Policy
            $table->string('slug')->unique(); // auto-generated from title
            $table->longText('content'); // rich text (Summernote)
            $table->boolean('is_active')->default(true);

            // Tracking fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
