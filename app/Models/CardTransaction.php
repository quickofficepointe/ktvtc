<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardTransaction extends Model
{
    protected $fillable = [
        'card_account_id',
        'transaction_type',
        'wallet',
        'amount',
        'fee_amount',
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
        'fee_amount' => 'decimal:2',
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
     * Generate a unique reference, e.g. CAF20261028-7K2Q9XWD.
     * (Five random digits per day used to collide on busy days and fail the insert.)
     */
    public static function generateReference(string $prefix): string
    {
        do {
            $reference = $prefix . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (static::where('reference', $reference)->exists());

        return $reference;
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
