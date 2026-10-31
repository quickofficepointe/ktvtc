<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Student accounts created by staff or on first login never got email_verified_at
 * (it is not fillable), so the 'verified' middleware locked those students out of
 * the portal. Accounts linked to a student record were made by the college, so they
 * count as verified. Self-registered accounts with no student record are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 5)
            ->whereNotNull('student_id')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Data fix; nothing to undo.
    }
};
