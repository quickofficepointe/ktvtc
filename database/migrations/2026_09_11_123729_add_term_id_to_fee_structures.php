<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->foreignId('term_id')
                ->nullable()
                ->after('campus_id')
                ->constrained('academic_terms')
                ->nullOnDelete()
                ->comment('Academic term this fee applies to. NULL = legacy/all-terms.');

            $table->index(['course_id', 'term_id', 'campus_id'], 'idx_fs_course_term_campus');
            $table->index(['term_id', 'is_active'], 'idx_fs_term_active');
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropIndex('idx_fs_course_term_campus');
            $table->dropIndex('idx_fs_term_active');
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
        });
    }
};