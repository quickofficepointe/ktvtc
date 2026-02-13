<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterLibraryLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'requesting_branch_id',
        'lending_branch_id',
        'book_id',
        'member_id',
        'request_date',
        'approval_date',
        'shipping_date',
        'received_date',
        'due_date',
        'return_date',
        'status',
        'notes',
        'shipping_cost',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approval_date' => 'date',
        'shipping_date' => 'date',
        'received_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'shipping_cost' => 'decimal:2',
    ];

    public function requestingBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'requesting_branch_id');
    }

    public function lendingBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'lending_branch_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function getLoanDurationAttribute(): int
    {
        if (!$this->received_date || !$this->return_date) {
            return 0;
        }

        return $this->return_date->diffInDays($this->received_date);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date < now() &&
               !in_array($this->status, ['returned', 'cancelled']);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'shipped', 'received']);
    }
}
