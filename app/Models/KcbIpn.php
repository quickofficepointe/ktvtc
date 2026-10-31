<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ledger of every KCB IPN received, for both tills.
 * transaction_id is unique, so a retried IPN can never be processed twice.
 */
class KcbIpn extends Model
{
    use HasFactory;

    protected $table = 'kcb_ipns';

    // Where the IPN was sent
    public const ROUTE_SCHOOL_FEES  = 'school_fees';
    public const ROUTE_CARD_FUNDING = 'card_funding';
    public const ROUTE_STK_LINK     = 'stk_link';   // pays for a sale / pre-order / application / event
    public const ROUTE_UNKNOWN      = 'unknown';

    // What happened to it
    public const STATUS_RECEIVED    = 'received';
    public const STATUS_CREDITED    = 'credited';     // money applied to a fee account or card
    public const STATUS_LINKED      = 'linked';       // matched to an order paid via STK; no balance change
    public const STATUS_UNALLOCATED = 'unallocated';  // waiting for finance to assign
    public const STATUS_QUARANTINED = 'quarantined';  // failed source check; finance must review
    public const STATUS_IGNORED     = 'ignored';      // finance decided not to apply it
    public const STATUS_DUPLICATE   = 'duplicate';    // already applied by the old system

    protected $fillable = [
        'message_id',
        'originator_conversation_id',
        'channel_code',
        'timestamp',
        'business_key',
        'business_key_type',
        'till_number',
        'account_reference',
        'route',
        'status',
        'status_reason',
        'suggestions',
        'source_ip',
        'signature_status',
        'is_historical',
        'resolved_by',
        'resolved_at',
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
        'invoice_number',
        'bill_reference_number',
        'processed',
        'processed_at',
        'matched_record_type',
        'matched_record_id',
        'reconciliation_notes',
        'raw_payload',
    ];

    protected $casts = [
        'transaction_amount' => 'decimal:2',
        'processed'          => 'boolean',
        'is_historical'      => 'boolean',
        'transaction_date'   => 'datetime',
        'processed_at'       => 'datetime',
        'resolved_at'        => 'datetime',
        'suggestions'        => 'array',
        'raw_payload'        => 'array',
    ];

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeAwaitingFinance($query)
    {
        return $query->whereIn('status', [self::STATUS_UNALLOCATED, self::STATUS_QUARANTINED]);
    }

    public function needsAttention(): bool
    {
        return in_array($this->status, [self::STATUS_UNALLOCATED, self::STATUS_QUARANTINED], true);
    }

    public function getPayerNameAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name])));
    }

    /**
     * Record the outcome. A credited or linked IPN also counts as processed.
     */
    public function markOutcome(string $status, ?string $reason = null, ?string $recordType = null, $recordId = null): void
    {
        $this->forceFill([
            'status'              => $status,
            'status_reason'       => $reason,
            'matched_record_type' => $recordType ?? $this->matched_record_type,
            'matched_record_id'   => $recordId ?? $this->matched_record_id,
            'processed'           => in_array($status, [self::STATUS_CREDITED, self::STATUS_LINKED, self::STATUS_IGNORED, self::STATUS_DUPLICATE], true),
            'processed_at'        => now(),
        ])->save();
    }
}
