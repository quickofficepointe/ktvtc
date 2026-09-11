<?php
// database/migrations/2026_09_11_000003_add_trainer_fields_to_attachments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->boolean('is_eligible_for_attachment')
                  ->default(false)
                  ->after('trainer_course_id');

            $table->timestamp('eligibility_checked_at')
                  ->nullable()
                  ->after('is_eligible_for_attachment');

            $table->foreignId('eligibility_checked_by')
                  ->nullable()
                  ->after('eligibility_checked_at')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->text('eligibility_notes')
                  ->nullable()
                  ->after('eligibility_checked_by');

            $table->timestamp('last_trainer_visit_at')
                  ->nullable()
                  ->after('eligibility_notes');

            $table->unsignedInteger('trainer_visit_count')
                  ->default(0)
                  ->after('last_trainer_visit_at');
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['eligibility_checked_by']);

            $table->dropColumn([
                'is_eligible_for_attachment',
                'eligibility_checked_at',
                'eligibility_checked_by',
                'eligibility_notes',
                'last_trainer_visit_at',
                'trainer_visit_count',
            ]);
        });
    }
};