<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('high_school_student_id'); // CHANGED

            // Card Identifiers
            $table->string('account_number', 50)->unique();
            $table->string('card_number', 50)->unique();
            $table->string('qr_code', 255)->nullable();
            $table->string('qr_token', 100)->nullable();
            $table->timestamp('qr_generated_at')->nullable();

            // Balance
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('total_funded', 15, 2)->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable();

            // Limits
            $table->decimal('daily_limit', 15, 2)->default(500);
            $table->decimal('per_transaction_limit', 15, 2)->default(300);
            $table->decimal('low_balance_threshold', 15, 2)->default(100);
            $table->decimal('minimum_balance', 15, 2)->default(0);

            // Daily tracking
            $table->decimal('today_spent', 15, 2)->default(0);
            $table->integer('today_transactions')->default(0);
            $table->timestamp('today_first_used_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('last_funded_at')->nullable();

            // Student snapshot
            $table->string('student_name')->nullable();
            $table->string('student_photo')->nullable();
            $table->string('student_class')->nullable();
            $table->string('student_admission_number')->nullable();

            // Metadata
            $table->timestamp('issued_at')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign keys - CHANGED
            $table->foreign('high_school_student_id')
                  ->references('id')
                  ->on('high_school_students')
                  ->onDelete('cascade');

            $table->foreign('blocked_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['high_school_student_id']); // CHANGED
            $table->index(['account_number']);
            $table->index(['card_number']);
            $table->index(['is_active', 'is_locked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_accounts');
    }
};
