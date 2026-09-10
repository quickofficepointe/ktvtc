<?php
// app/Models/HighSchoolKcbTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HighSchoolKcbTransaction extends Model
{
    protected $table = 'high_school_kcb_transactions';

    protected $fillable = [
        // From KCB IPN payload
        'transaction_reference',
        'request_id',
        'merchant_request_id',
        'checkout_request_id',
        'channel_code',
        'timestamp',
        'transaction_amount',
        'currency',
        'customer_reference',
        'customer_name',
        'customer_mobile_number',
        'balance',
        'narration',
        'till_number',
        'organization_short_code',

        // M-Pesa specific
        'mpesa_receipt_number',
        'amount',
        'phone_number',
        'invoice_number',

        // Card specific
        'card_account_id',
        'high_school_student_id',

        // Internal tracking
        'student_number',
        'transaction_type',
        'status',
        'result_code',
        'result_description',

        // Metadata
        'request_data',
        'callback_data',
        'ip_address',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'callback_data' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'transaction_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the card account
     */
    public function cardAccount(): BelongsTo
    {
        return $this->belongsTo(CardAccount::class);
    }

    /**
     * Get the high school student
     */
    public function highSchoolStudent(): BelongsTo
    {
        return $this->belongsTo(HighSchoolStudent::class);
    }

    /**
     * Get the high school student (alias for consistency)
     */
    public function student()
    {
        return $this->highSchoolStudent();
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['initiated', 'processing']);
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if transaction can be retried
     */
    public function canRetry(): bool
    {
        return $this->status === 'failed' && 
               $this->created_at->diffInHours(now()) < 24;
    }

    /**
     * Mark as processing
     */
    public function markProcessing(): void
    {
        $this->status = 'processing';
        $this->save();
    }

    /**
     * Mark as completed
     */
    public function markCompleted(string $mpesaReceipt): void
    {
        $this->status = 'completed';
        $this->mpesa_receipt_number = $mpesaReceipt;
        $this->result_code = 0;
        $this->result_description = 'Payment successful';
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Mark as failed
     */
    public function markFailed(int $resultCode, string $resultDescription): void
    {
        $this->status = 'failed';
        $this->result_code = $resultCode;
        $this->result_description = $resultDescription;
        $this->save();
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'green',
            'initiated', 'processing' => 'yellow',
            'failed' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'Completed',
            'initiated' => 'Pending',
            'processing' => 'Processing',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get funding type label
     */
    public function getFundingTypeLabelAttribute(): string
    {
        $data = $this->request_data ?? [];
        $type = $data['funding_type'] ?? 'T';
        return $type === 'T' ? 'Transport' : 'Canteen';
    }

    /**
     * Get student name
     */
    public function getStudentNameAttribute(): string
    {
        $data = $this->request_data ?? [];
        return $data['student_name'] ?? $this->customer_name ?? 'Unknown';
    }

    /**
     * Get student admission number
     */
    public function getStudentAdmissionAttribute(): string
    {
        $data = $this->request_data ?? [];
        return $data['admission_number'] ?? $this->customer_reference ?? 'N/A';
    }

    /**
     * Get card number
     */
    public function getCardNumberAttribute(): string
    {
        return $this->cardAccount ? $this->cardAccount->card_number : 'N/A';
    }

    /**
     * Format phone number for display
     */
    public function getFormattedPhoneAttribute(): string
    {
        $phone = $this->phone_number ?? '';
        if (strlen($phone) === 12 && strpos($phone, '254') === 0) {
            return '0' . substr($phone, 3);
        }
        return $phone;
    }
}