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
        Schema::table('users', function (Blueprint $table) {
            // Add username column for student number login
            $table->string('username')->unique()->nullable()->after('email');

            // Add student_id column to link to students table
            $table->foreignId('student_id')->nullable()->unique()->after('id');

            // Add last login tracking
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('last_login_ip')->nullable()->after('last_login_at');

            // Add indexes for better performance
            $table->index('username');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['student_id']);

            // Drop columns
            $table->dropColumn(['username', 'student_id', 'last_login_at', 'last_login_ip']);
        });
    }
};
