<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * kcb_ipns becomes the single ledger of every IPN KCB sends, for both tills.
 * Each row records where the money went (or why it is waiting for finance).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kcb_ipns', function (Blueprint $table) {
            $table->string('till_number', 20)->nullable()->after('business_key_type');
            $table->string('account_reference', 150)->nullable()->after('till_number');
            $table->string('route', 30)->nullable()->after('account_reference');
            $table->string('status', 20)->default('received')->after('route');
            $table->string('status_reason')->nullable()->after('status');
            $table->json('suggestions')->nullable()->after('status_reason');
            $table->string('source_ip', 45)->nullable()->after('suggestions');
            $table->string('signature_status', 20)->nullable()->after('source_ip');
            $table->boolean('is_historical')->default(false)->after('signature_status');
            $table->foreignId('resolved_by')->nullable()->after('is_historical')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');

            $table->index('till_number');
            $table->index('status');
        });

        // Rows stored by the old handler were only developer test notifications (TEST..., CURL-TEST..., KES 1 trials).
        // Close them so none sits as "received" forever; the old values stay on the row.
        DB::table('kcb_ipns')->where('status', 'received')->update([
            'status'        => 'ignored',
            'status_reason' => 'Stored before the payment router (test notification); not applied.',
            'is_historical' => true,
            'processed_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('kcb_ipns', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropIndex(['till_number']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'till_number', 'account_reference', 'route', 'status', 'status_reason',
                'suggestions', 'source_ip', 'signature_status', 'is_historical',
                'resolved_by', 'resolved_at',
            ]);
        });
    }
};
