<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_daily_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_account_id');

            // Date
            $table->date('usage_date');

            // Spending
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->decimal('average_spent', 15, 2)->default(0);

            // Details
            $table->timestamp('first_transaction_at')->nullable();
            $table->timestamp('last_transaction_at')->nullable();
            $table->decimal('max_single_transaction', 15, 2)->default(0);

            // Items
            $table->integer('total_items_purchased')->default(0);
            $table->string('most_purchased_item', 255)->nullable();

            // Meal breakdown
            $table->integer('breakfast_count')->default(0);
            $table->integer('lunch_count')->default(0);
            $table->integer('dinner_count')->default(0);
            $table->integer('snack_count')->default(0);

            // Funding
            $table->decimal('total_funded_today', 15, 2)->default(0);
            $table->integer('funding_count')->default(0);

            // Alerts
            $table->boolean('low_balance_alert_sent')->default(false);
            $table->timestamp('low_balance_alert_sent_at')->nullable();
            $table->boolean('daily_limit_alert_sent')->default(false);

            $table->timestamps();

            // Foreign keys
            $table->foreign('card_account_id')->references('id')->on('card_accounts')->onDelete('cascade');

            // Indexes
            $table->unique(['card_account_id', 'usage_date']);
            $table->index(['usage_date']);
            $table->index(['card_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_daily_usage');
    }
};
