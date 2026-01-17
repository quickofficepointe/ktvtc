<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'book_id',
        'branch_id',
        'reservation_date',
        'expiry_date',
        'status',
        'queue_position',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->expiry_date >= now();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date < now();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                     ->where('status', 'active');
    }
}
