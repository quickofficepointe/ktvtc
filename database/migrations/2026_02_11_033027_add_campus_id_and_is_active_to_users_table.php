<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add soft deletes column if it doesn't exist
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }

            // Add is_approved column first (if it doesn't exist)
            if (!Schema::hasColumn('users', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('profile_picture');
            }

            // Add is_active column after is_approved
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_approved');
            }

            // Add campus_id foreign key after role
            if (!Schema::hasColumn('users', 'campus_id')) {
                $table->foreignId('campus_id')->nullable()->after('role')
                      ->constrained('campuses')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('users', 'campus_id')) {
                $table->dropForeign(['campus_id']);
            }

            // Drop columns in reverse order
            $columnsToDrop = ['campus_id', 'is_active', 'is_approved', 'deleted_at'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
