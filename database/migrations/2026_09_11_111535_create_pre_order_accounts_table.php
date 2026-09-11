<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_order_accounts', function (Blueprint $table) {
            $table->id();

            // Who is this account
            $table->enum('user_type', ['staff', 'college_student', 'high_school_student']);
            $table->unsignedBigInteger('user_id')->index();

            // What they type in to log in
            //   - staff           -> users.email
            //   - college_student -> students.student_number
            //   - hs_student      -> high_school_students.admission_number
            $table->string('identifier', 150)->unique();

            // Display name (denormalized for fast rendering without joins)
            $table->string('display_name', 200)->nullable();

            // Auth
            $table->string('pin_hash');             // bcrypt of 4-digit PIN
            $table->string('phone', 20)->nullable();// cached from source table for SMS reset
            $table->timestamp('pin_set_at')->nullable();
            $table->timestamp('last_login_at')->nullable();

            // PIN reset flow
            $table->string('reset_code_hash', 100)->nullable();
            $table->timestamp('reset_code_expires_at')->nullable();
            $table->unsignedTinyInteger('reset_attempts')->default(0);

            // Verification flow (first-visit)
            $table->string('verification_code_hash', 100)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false); // true after first-visit SMS verification

            $table->timestamps();

            // Indexes
            $table->index(['user_type', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_order_accounts');
    }
};
