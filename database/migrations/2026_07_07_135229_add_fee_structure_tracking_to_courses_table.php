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
        Schema::table('courses', function (Blueprint $table) {
            // Fee structure versioning
            $table->string('fee_version', 20)->nullable()
                ->after('fees_breakdown')
                ->default('v1.0')
                ->comment('Current fee structure version');

            // Who modified the fee structure
            $table->foreignId('fee_modified_by')->nullable()
                ->after('fee_version')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who last modified fee structure');

            // When fee structure was modified
            $table->timestamp('fee_modified_at')->nullable()
                ->after('fee_modified_by')
                ->comment('When fee structure was last modified');

            // Reason for modification
            $table->text('fee_modification_reason')->nullable()
                ->after('fee_modified_at')
                ->comment('Reason for fee structure modification');

            // Previous fee structure (for rollback/audit)
            $table->json('previous_fee_structure')->nullable()
                ->after('fee_modification_reason')
                ->comment('Previous fee structure before modification');

            // Approval tracking
            $table->foreignId('fee_modification_approved_by')->nullable()
                ->after('previous_fee_structure')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Who approved the fee structure change');

            $table->timestamp('fee_modification_approved_at')->nullable()
                ->after('fee_modification_approved_by')
                ->comment('When fee structure was approved');

            // Indexes for performance
            $table->index('fee_version', 'idx_courses_fee_version');
            $table->index(['fee_modified_by', 'fee_modified_at'], 'idx_courses_fee_modifications');
            $table->index('fee_modification_approved_by', 'idx_courses_fee_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['fee_modified_by']);
            $table->dropForeign(['fee_modification_approved_by']);

            // Drop indexes
            $table->dropIndex('idx_courses_fee_version');
            $table->dropIndex('idx_courses_fee_modifications');
            $table->dropIndex('idx_courses_fee_approved_by');

            // Drop columns
            $table->dropColumn([
                'fee_version',
                'fee_modified_by',
                'fee_modified_at',
                'fee_modification_reason',
                'previous_fee_structure',
                'fee_modification_approved_by',
                'fee_modification_approved_at',
            ]);
        });
    }
};
