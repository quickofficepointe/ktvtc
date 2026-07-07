<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // ========== FEE LOCKING COLUMNS ==========

            // Boolean flag to indicate if fees are locked
            $table->boolean('fee_locked')->default(false)
                ->after('amount_paid')
                ->comment('Whether fees are locked and cannot be modified');

            // Timestamp of when fees were locked
            $table->timestamp('fee_locked_at')->nullable()
                ->after('fee_locked')
                ->comment('When the fees were locked');

            // JSON snapshot of fee breakdown at enrollment time
            $table->json('fee_snapshot')->nullable()
                ->after('fee_locked_at')
                ->comment('Full fee breakdown snapshot at enrollment');

            // Version tracking for fee structure
            $table->string('fee_version_at_enrollment', 50)->nullable()
                ->after('fee_snapshot')
                ->comment('Which version of fee structure was used');

            // Original fees (if modified later)
            $table->decimal('original_fees', 12, 2)->nullable()
                ->after('fee_version_at_enrollment')
                ->comment('Original fees before any modifications');

            // Who locked the fees (user_id reference)
            $table->foreignId('fee_locked_by')->nullable()
                ->after('fee_locked_at')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who locked the fees');

            // Who last modified the fees
            $table->foreignId('fees_modified_by')->nullable()
                ->after('original_fees')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who last modified the fees');

            // Timestamp of last fee modification
            $table->timestamp('fees_modified_at')->nullable()
                ->after('fees_modified_by')
                ->comment('When fees were last modified');

            // Reason for fee modification (if any)
            $table->text('fee_modification_reason')->nullable()
                ->after('fees_modified_at')
                ->comment('Reason for fee modification');

            // ========== ADD INDEXES FOR PERFORMANCE ==========
            $table->index('fee_locked', 'idx_enrollments_fee_locked');
            $table->index('fee_locked_at', 'idx_enrollments_fee_locked_at');
            $table->index(['fee_locked', 'status'], 'idx_enrollments_fee_locked_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['fee_locked_by']);
            $table->dropForeign(['fees_modified_by']);

            // Drop indexes
            $table->dropIndex('idx_enrollments_fee_locked');
            $table->dropIndex('idx_enrollments_fee_locked_at');
            $table->dropIndex('idx_enrollments_fee_locked_status');

            // Drop columns
            $table->dropColumn([
                'fee_locked',
                'fee_locked_at',
                'fee_locked_by',
                'fee_snapshot',
                'fee_version_at_enrollment',
                'original_fees',
                'fees_modified_by',
                'fees_modified_at',
                'fee_modification_reason',
            ]);
        });
    }
};
