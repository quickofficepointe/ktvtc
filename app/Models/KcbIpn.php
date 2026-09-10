<?php
// app/Models/KcbIpn.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KcbIpn extends Model
{
    use HasFactory;

    protected $table = 'kcb_ipns';

    protected $fillable = [
        // Header fields
        'message_id',
        'originator_conversation_id',
        'channel_code',
        'timestamp',

        // Primary Data
        'business_key',
        'business_key_type',
        'debit_msisdn',
        'transaction_amount',
        'transaction_date',
        'transaction_id',
        'first_name',
        'middle_name',
        'last_name',
        'currency',
        'balance',
        'narration',
        'transaction_type',

        // Notification Data
        'invoice_number',
        'bill_reference_number',

        // Reconciliation fields
        'processed',
        'processed_at',
        'matched_record_type',
        'matched_record_id',
        'reconciliation_notes',
        'raw_payload'
    ];

    protected $casts = [
        'transaction_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'transaction_date' => 'datetime',
        'timestamp' => 'datetime',
        'raw_payload' => 'array'
    ];

    /**
     * Mark as processed and linked to a record
     */
    public function markProcessed($recordType, $recordId, $notes = null)
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'matched_record_type' => $recordType,
            'matched_record_id' => $recordId,
            'reconciliation_notes' => $notes
        ]);
    }
}
