<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_payments', 'kcb_transaction_id')) {
                $table->string('kcb_transaction_id', 100)->nullable()->after('transaction_code');
                $table->index('kcb_transaction_id');
            }

            if (!Schema::hasColumn('fee_payments', 'bill_reference_number')) {
                $table->string('bill_reference_number', 50)->nullable()->after('kcb_transaction_id');
                $table->index('bill_reference_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn(['kcb_transaction_id', 'bill_reference_number']);
        });
    }
};
