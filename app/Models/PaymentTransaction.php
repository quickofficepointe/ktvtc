<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'sale_id',
        'transaction_number',
        'payment_method',
        'amount',
        'currency',
        'mpesa_receipt',
        'phone_number',
        'transaction_id',
        'card_last_four',
        'card_type',
        'card_authorization_code',
        'bank_name',
        'bank_reference',
        'transfer_date',
        'status',
        'completed_at',
        'checkout_request_id',
        'merchant_request_id',
        'kcb_response',
        'callback_received_at',
        'recorded_by',
        'reconciled_by',
        'reconciled_at',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
        'completed_at' => 'datetime',
        'kcb_response' => 'array',
        'callback_received_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'transfer_date' => 'date',
    ];

    // Relationships
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reconciler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
