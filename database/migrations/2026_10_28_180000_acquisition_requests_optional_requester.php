<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Book purchase requests are mostly raised by library staff, not a member, but
 * requested_by was a required member link (and the form never set it), so every
 * request failed to save. A member is now optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE acquisition_requests MODIFY requested_by BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Requests without a member would block the NOT NULL; leave them for staff to fix first.
        DB::statement('ALTER TABLE acquisition_requests MODIFY requested_by BIGINT UNSIGNED NOT NULL');
    }
};
