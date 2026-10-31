<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fee reminders by SMS.
 * - fee_reminders: every reminder sent (who, which balance, which phones), so finance sees
 *   when a family was last reminded and bulk sends skip anyone reminded this week.
 * - enrollments.balance_confirmed_*: old whole-course balances were typed in by hand (some
 *   wrongly), so they are only reminded about once accounts has checked and confirmed them.
 *   Balances billed per term by the new system don't need confirming.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('enrollment_id')->index();
            $table->decimal('balance', 12, 2);
            $table->json('phones');
            $table->text('message');
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamps();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->timestamp('balance_confirmed_at')->nullable()->after('balance');
            $table->unsignedBigInteger('balance_confirmed_by')->nullable()->after('balance_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['balance_confirmed_at', 'balance_confirmed_by']);
        });
        Schema::dropIfExists('fee_reminders');
    }
};
