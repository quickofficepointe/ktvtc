<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_account_id');

            // Action
            $table->enum('action_type', [
                'create', 'fund', 'purchase', 'lock', 'unlock',
                'block', 'unblock', 'limit_change', 'balance_adjust',
                'refund', 'reversal', 'pin_reset', 'delete'
            ]);

            // Details
            $table->string('description', 255);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->json('metadata')->nullable();

            // User
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->enum('performed_by_type', ['system', 'admin', 'cashier', 'parent'])->default('system');

            // IP & Device
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_id', 100)->nullable();

            // Status
            $table->enum('status', ['success', 'failed', 'pending'])->default('success');
            $table->string('error_message', 255)->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('card_account_id')->references('id')->on('card_accounts')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['card_account_id']);
            $table->index(['action_type']);
            $table->index(['created_at']);
            $table->index(['performed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_audit_logs');
    }
};
