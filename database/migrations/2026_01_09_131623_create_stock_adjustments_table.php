<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('business_section_id')->constrained('business_sections')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            // Adjustment details
            $table->date('adjustment_date');
            $table->enum('adjustment_type', ['physical_count', 'damage', 'theft', 'expiry', 'error', 'transfer', 'other']);
            $table->enum('adjustment_category', ['addition', 'deduction', 'correction'])->default('correction');
            $table->text('reason');

            // Totals
            $table->integer('total_items')->default(0);
            $table->decimal('total_quantity_adjusted', 12, 3)->default(0);
            $table->decimal('total_value_adjusted', 12, 2)->default(0);

            // Approval
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'processed'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Processing
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();

            // Notes
            $table->text('notes')->nullable();

            // Personnel
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('adjustment_number');
            $table->index('adjustment_date');
            $table->index('adjustment_type');
            $table->index('status');
            $table->index(['shop_id', 'adjustment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
