<?php
// database/migrations/2026_09_11_000002_add_trainer_approval_to_attachments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignId('trainer_approved_by')
                  ->nullable()
                  ->after('status')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('trainer_approved_at')
                  ->nullable()
                  ->after('trainer_approved_by');

            $table->text('trainer_remarks')
                  ->nullable()
                  ->after('trainer_approved_at');

            $table->foreignId('trainer_course_id')
                  ->nullable()
                  ->after('trainer_remarks')
                  ->constrained('courses')
                  ->nullOnDelete();

            $table->index(
                ['trainer_approved_by', 'trainer_approved_at'],
                'attachments_trainer_approved_idx'
            );
        });

        // Widen the ENUM to include 'trainer_approved' (MySQL / MariaDB only)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE attachments
                MODIFY COLUMN status ENUM(
                    'draft',
                    'submitted',
                    'needs_revision',
                    'trainer_approved',
                    'active',
                    'completed',
                    'graded',
                    'rejected'
                ) NOT NULL DEFAULT 'draft'
            ");
        }
    }

    public function down(): void
    {
        // First push any trainer_approved rows back to submitted,
        // otherwise narrowing the enum will fail or truncate data.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::table('attachments')
                ->where('status', 'trainer_approved')
                ->update(['status' => 'submitted']);

            DB::statement("
                ALTER TABLE attachments
                MODIFY COLUMN status ENUM(
                    'draft',
                    'submitted',
                    'needs_revision',
                    'active',
                    'completed',
                    'graded',
                    'rejected'
                ) NOT NULL DEFAULT 'draft'
            ");
        }

        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['trainer_approved_by']);
            $table->dropForeign(['trainer_course_id']);
            $table->dropIndex('attachments_trainer_approved_idx');

            $table->dropColumn([
                'trainer_approved_by',
                'trainer_approved_at',
                'trainer_remarks',
                'trainer_course_id',
            ]);
        });
    }
};