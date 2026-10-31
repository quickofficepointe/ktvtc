<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Members of the public can pre-order: they sign up with their phone number
 * (verified by SMS), so they have no user/student record behind the account.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pre_order_accounts MODIFY user_type ENUM('staff','college_student','high_school_student','public') NOT NULL");
        DB::statement('ALTER TABLE pre_order_accounts MODIFY user_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::table('pre_order_accounts')->where('user_type', 'public')->delete();
        DB::statement('ALTER TABLE pre_order_accounts MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement("ALTER TABLE pre_order_accounts MODIFY user_type ENUM('staff','college_student','high_school_student') NOT NULL");
    }
};
