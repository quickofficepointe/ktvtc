<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'transaction_id',
        'payment_method',
        'amount',
        'payment_type',
        'reference_number',
        'notes',
        'payment_date',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopeFines($query)
    {
        return $query->where('payment_type', 'fine');
    }

    public function scopeMembership($query)
    {
        return $query->where('payment_type', 'membership');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }
}
