<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Transport fare handed to a high-school student in cash.
 * Each one deducts amount + Safaricom withdrawal fee from the transport wallet.
 */
class FareDisbursement extends Model
{
    protected $fillable = [
        'card_account_id',
        'high_school_student_id',
        'card_transaction_id',
        'amount',
        'withdrawal_fee',
        'total_deducted',
        'given_on',
        'method',
        'batch_reference',
        'notes',
        'is_backdated',
        'status',
        'reversal_reason',
        'recorded_by',
        'reversed_by',
        'reversed_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'withdrawal_fee' => 'decimal:2',
        'total_deducted' => 'decimal:2',
        'given_on'       => 'date',
        'is_backdated'   => 'boolean',
        'reversed_at'    => 'datetime',
    ];

    public function cardAccount()
    {
        return $this->belongsTo(CardAccount::class);
    }

    public function student()
    {
        return $this->belongsTo(HighSchoolStudent::class, 'high_school_student_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeGiven($query)
    {
        return $query->where('status', 'given');
    }
}
