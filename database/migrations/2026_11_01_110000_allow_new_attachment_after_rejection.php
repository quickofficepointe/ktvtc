<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A student whose placement was rejected may declare another in the same term.
 * "One open placement at a time" is enforced by Attachment::studentHasOpen();
 * the student_id+status index still serves the student_id foreign key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->unique(['student_id', 'term_id']);
        });
    }
};
