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
        Schema::create('event_applications', function (Blueprint $table) {
            $table->id();

            // Event relationship
            $table->foreignId('event_id')->constrained()->onDelete('cascade');

            // Parent/Guardian information
            $table->string('parent_name');
            $table->string('parent_contact');
            $table->string('parent_email');
            $table->string('mpesa_reference_number');

            // Number of people (children/attendees)
            $table->integer('number_of_people');

            // Application metadata
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('application_status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        // Create a separate table for the attendee details (children/participants)
        Schema::create('event_application_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_application_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('school');
            $table->integer('age');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_application_attendees');
        Schema::dropIfExists('event_applications');
    }
};
