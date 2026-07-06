<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardFundingRequest extends Model
{
    protected $fillable = [
        'card_account_id',
        'amount',
        'parent_phone',
        'parent_name',
        'student_name',
        'student_admission',
        'status',
        'parent_response',
        'parent_response_at',
        'checkout_request_id',
        'kcb_invoice_number',
        'mpesa_receipt',
        'ipn_transaction_id',
        'retry_count',
        'last_retry_at',
        'max_retries',
        'sms_sent',
        'sms_sent_at',
        'parent_notified',
        'completed_at',
        'completed_by',
        'failure_reason',
        'failure_code',
        'metadata',
        'expires_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'parent_response_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'sms_sent' => 'boolean',
        'parent_notified' => 'boolean',
    ];

    /**
     * Get the card account
     */
    public function cardAccount(): BelongsTo
    {
        return $this->belongsTo(CardAccount::class);
    }

    /**
     * Get the high school student (via card account)
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
     * Get the completer
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the transaction
     */
    public function transaction()
    {
        return $this->hasOne(CardTransaction::class, 'funding_request_id');
    }

    /**
     * Check if request can be retried
     */
    public function canRetry(): bool
    {
        return $this->status === 'failed' &&
               $this->retry_count < $this->max_retries &&
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Increment retry count
     */
    public function incrementRetry(): void
    {
        $this->retry_count++;
        $this->last_retry_at = now();
        $this->save();
    }
}
