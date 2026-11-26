<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * kcb_buni_transactions.application_id holds a course application id (KES 500 fee) or an
 * event application id (event fees), told apart by transaction_type. The foreign key to
 * event_applications made the KES 500 prompt fail for course applications whose id has no
 * event application, and would delete payment records if an event application was deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::selectOne("SELECT COUNT(*) n FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kcb_buni_transactions'
            AND CONSTRAINT_NAME = 'kcb_buni_transactions_application_id_foreign' AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->n;
        if ($exists) {
            Schema::table('kcb_buni_transactions', function (Blueprint $table) {
                $table->dropForeign('kcb_buni_transactions_application_id_foreign');
            });
        }
    }

    public function down(): void
    {
        // Not restored: the key pointed at the wrong table for course applications
    }
};
