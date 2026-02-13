<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->longText('short_description')->nullable();
            $table->string('location')->nullable();

            // Date & Time
            $table->dateTime('event_start_date');
            $table->dateTime('event_end_date');
            $table->dateTime('registration_start_date')->nullable();
            $table->dateTime('registration_end_date')->nullable();

            // College-specific fields
            $table->string('event_type')->default('workshop'); // workshop, bootcamp, trip, mentorship, seminar, social
            $table->string('department')->nullable(); // CS, Engineering, Business, etc.
            $table->string('target_audience')->nullable(); // freshmen, seniors, all_students, faculty

            // Payment & Pricing
            $table->boolean('is_paid')->default(false);
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('early_bird_price', 10, 2)->nullable();
            $table->dateTime('early_bird_end_date')->nullable();

            // Capacity
            $table->integer('max_attendees')->nullable();
            $table->integer('registered_attendees')->default(0);

            // Media
            $table->string('cover_image')->nullable();
            $table->string('banner_image')->nullable();

            // Status & Metadata
            $table->boolean('is_active')->default(true);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('sort_order')->default(0);

            // Organizer Information
            $table->string('organizer_name')->nullable();
            $table->string('organizer_email')->nullable();
            $table->string('organizer_phone')->nullable();
            $table->string('organizer_website')->nullable();

            // Foreign Keys
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
