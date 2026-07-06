<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('high_school_student_id'); // CHANGED

            // Contact Details
            $table->enum('contact_type', ['mother', 'father', 'guardian', 'emergency']);
            $table->string('name', 255);
            $table->string('phone', 20);
            $table->string('email', 255)->nullable();
            $table->string('relationship', 50)->nullable();

            // Flags
            $table->boolean('is_primary')->default(false);
            $table->boolean('receive_alerts')->default(true);
            $table->boolean('receive_low_balance')->default(true);
            $table->boolean('receive_daily_summary')->default(false);
            $table->boolean('receive_funding_updates')->default(true);

            // Communication Stats
            $table->timestamp('last_contacted_at')->nullable();
            $table->integer('total_sms_sent')->default(0);
            $table->integer('total_sms_failed')->default(0);

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            // Metadata
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys - CHANGED
            $table->foreign('high_school_student_id')
                  ->references('id')
                  ->on('high_school_students')
                  ->onDelete('cascade');

            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->unique(['high_school_student_id', 'phone']); // CHANGED
            $table->index(['high_school_student_id']); // CHANGED
            $table->index(['phone']);
            $table->index(['is_primary', 'receive_alerts']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_contacts');
    }
};
