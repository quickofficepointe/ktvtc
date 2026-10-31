<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every change to what a student owes or has paid that is not a payment:
 * term discounts, waivers, bursaries and extra charges (fees_change), refunds
 * paid out of a credit and money moved on a course change (paid_change).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20); // discount, waiver, bursary, charge, refund, transfer_out, transfer_in, write_off
            $table->decimal('fees_change', 12, 2)->default(0); // + adds to what is owed, - reduces it
            $table->decimal('paid_change', 12, 2)->default(0); // + money brought in, - money taken out
            $table->string('reason', 500);
            $table->string('method', 20)->nullable();     // refunds: cash, mpesa, bank
            $table->string('reference', 100)->nullable(); // refunds: M-Pesa / bank reference
            $table->unsignedBigInteger('course_change_request_id')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['enrollment_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_adjustments');
    }
};
