<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits each high-school card into a Transport wallet and a Canteen wallet,
 * and adds the records needed to reconcile transport fare given in cash.
 *
 * card_accounts.balance stays as transport + canteen so existing screens keep working.
 * Existing balances are split by `php artisan cards:split-wallets` (not here).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_accounts', function (Blueprint $table) {
            $table->decimal('transport_balance', 12, 2)->default(0)->after('balance');
            $table->decimal('canteen_balance', 12, 2)->default(0)->after('transport_balance');
            $table->timestamp('wallets_split_at')->nullable()->after('canteen_balance');
        });

        Schema::table('card_transactions', function (Blueprint $table) {
            $table->string('wallet', 10)->nullable()->after('transaction_type');
            $table->decimal('fee_amount', 10, 2)->default(0)->after('amount');
            $table->index(['wallet', 'transaction_type', 'created_at'], 'idx_card_tx_wallet_type_date');
        });

        DB::statement("ALTER TABLE card_transactions MODIFY transaction_type
            ENUM('funding','purchase','refund','reversal','adjustment','fee_charge','disbursement','transfer') NOT NULL");

        // Safaricom withdrawal charge per amount band; finance can edit these
        Schema::create('withdrawal_fee_tiers', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_amount', 12, 2);
            $table->decimal('max_amount', 12, 2);
            $table->decimal('fee', 10, 2);
            $table->timestamps();
        });

        $now = now();
        DB::table('withdrawal_fee_tiers')->insert(array_map(fn ($t) => [
            'min_amount' => $t[0], 'max_amount' => $t[1], 'fee' => $t[2], 'created_at' => $now, 'updated_at' => $now,
        ], [
            // Safaricom's published M-Pesa agent-withdrawal tariff (college decision 2026-10-28).
            // Finance can edit these under Finance > Transport Fare > Withdrawal charges.
            [50, 100, 11],
            [101, 500, 29],
            [501, 1000, 29],
            [1001, 1500, 29],
            [1501, 2500, 29],
            [2501, 3500, 52],
            [3501, 5000, 69],
            [5001, 7500, 87],
            [7501, 10000, 115],
            [10001, 15000, 167],
            [15001, 20000, 185],
            [20001, 35000, 197],
            [35001, 50000, 278],
        ]));

        // Transport fare handed to a student in cash, deducted from their transport wallet
        Schema::create('fare_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_account_id')->constrained('card_accounts')->cascadeOnDelete();
            $table->foreignId('high_school_student_id')->constrained('high_school_students')->cascadeOnDelete();
            $table->foreignId('card_transaction_id')->nullable()->constrained('card_transactions')->nullOnDelete();
            $table->decimal('amount', 12, 2);                    // cash handed to the student
            $table->decimal('withdrawal_fee', 10, 2)->default(0); // Safaricom charge for that amount
            $table->decimal('total_deducted', 12, 2);             // amount + fee, taken from transport wallet
            $table->date('given_on');
            $table->string('method', 20)->default('cash');
            $table->string('batch_reference', 40)->nullable();   // groups a class handed fare together
            $table->string('notes')->nullable();
            $table->boolean('is_backdated')->default(false);     // recorded after the fact (reconciliation)
            $table->string('status', 15)->default('given');      // given | reversed
            $table->string('reversal_reason')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            $table->index(['given_on', 'status']);
            $table->index('batch_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_disbursements');
        Schema::dropIfExists('withdrawal_fee_tiers');

        DB::statement("ALTER TABLE card_transactions MODIFY transaction_type
            ENUM('funding','purchase','refund','reversal','adjustment','fee_charge') NOT NULL");

        Schema::table('card_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_card_tx_wallet_type_date');
            $table->dropColumn(['wallet', 'fee_amount']);
        });

        Schema::table('card_accounts', function (Blueprint $table) {
            $table->dropColumn(['transport_balance', 'canteen_balance', 'wallets_split_at']);
        });
    }
};
