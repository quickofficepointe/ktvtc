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
        'held_item_id',
        'held_until',
        'fulfilled_at',
        'cancelled_at',
        'created_by',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'expiry_date' => 'date',
        'held_until' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    /** The copy put aside for this reader once one came back. */
    public function heldItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'held_item_id');
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
