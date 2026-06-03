<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KcbTransaction extends Model
{
    protected $table = 'kcb_transactions';

    protected $fillable = [
        'transaction_reference',
        'request_id',
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
        'student_number',
        'student_id',
        'fee_payment_id',
        'raw_payload',
        'ip_address',
        'processed_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'processed_at' => 'datetime',
        'transaction_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feePayment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class);
    }
}
