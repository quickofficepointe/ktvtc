<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('membership_start_date');
            $table->date('membership_end_date');
            $table->enum('membership_type', ['regular', 'premium', 'student', 'faculty'])->default('regular');
            $table->boolean('is_active')->default(true);
            $table->foreignId('branch_id')->constrained();
            $table->decimal('outstanding_fines', 8, 2)->default(0);
            $table->integer('max_borrow_limit')->default(5);
            $table->timestamps();

            $table->index(['first_name', 'last_name']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
