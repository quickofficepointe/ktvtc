<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Library lending that actually works end to end:
 * - students are members automatically (members.student_id; email optional for them)
 * - loans record who issued and who received the book back
 * - copies can be 'missing' (counted on paper, not yet found in a stock-take)
 * - a returned copy can be held for the next reader on the waiting list
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->unique()->after('member_id');
            $table->string('email')->nullable()->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('issued_by')->nullable()->after('notes');
            $table->unsignedBigInteger('returned_by')->nullable()->after('issued_by');
        });

        DB::statement("ALTER TABLE items MODIFY status ENUM('available','borrowed','reserved','maintenance','lost','missing') NOT NULL DEFAULT 'available'");

        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('held_item_id')->nullable()->after('queue_position');
            $table->dateTime('held_until')->nullable()->after('held_item_id');
            $table->dateTime('fulfilled_at')->nullable()->after('held_until');
            $table->dateTime('cancelled_at')->nullable()->after('fulfilled_at');
            $table->unsignedBigInteger('created_by')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['held_item_id', 'held_until', 'fulfilled_at', 'cancelled_at', 'created_by']);
        });
        DB::statement("UPDATE items SET status = 'lost' WHERE status = 'missing'");
        DB::statement("ALTER TABLE items MODIFY status ENUM('available','borrowed','reserved','maintenance','lost') NOT NULL DEFAULT 'available'");
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['issued_by', 'returned_by']);
        });
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};
