<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardTransaction extends Model
{
    protected $fillable = [
        'card_account_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'reference',
        'description',
        'sale_id',
        'funding_request_id',
        'fee_payment_id',
        'ipn_transaction_id',
        'mpesa_receipt',
        'checkout_request_id',
        'metadata',
        'location',
        'device_id',
        'ip_address',
        'processed_at',
        'processed_by',
        'failure_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the card account
     */
    public function cardAccount(): BelongsTo
    {
        return $this->belongsTo(CardAccount::class);
    }

    /**
     * Get the high school student via card account
     */
    public function student()
    {
        return $this->hasOneThrough(
            HighSchoolStudent::class,
            CardAccount::class,
            'id',
            'id',
            'card_account_id',
            'high_school_student_id'
        );
    }

    /**
     * Get the sale
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the funding request
     */
    public function fundingRequest(): BelongsTo
    {
        return $this->belongsTo(CardFundingRequest::class);
    }

    /**
     * Get the fee payment
     */
    public function feePayment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class);
    }

    /**
     * Get the processor
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Generate reference
     */
    public static function generateReference(string $prefix): string
    {
        $date = date('Ymd');
        $random = str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        return $prefix . $date . $random;
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for purchases
     */
    public function scopePurchases($query)
    {
        return $query->where('transaction_type', 'purchase');
    }

    /**
     * Scope for funding
     */
    public function scopeFunding($query)
    {
        return $query->where('transaction_type', 'funding');
    }
}
