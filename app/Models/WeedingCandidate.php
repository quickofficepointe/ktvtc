<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeedingCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'branch_id',
        'reason',
        'status',
        'last_borrowed_date',
        'days_since_last_borrow',
        'total_borrows',
        'condition',
        'review_notes',
        'review_date',
        'reviewed_by',
    ];

    protected $casts = [
        'last_borrowed_date' => 'date',
        'days_since_last_borrow' => 'integer',
        'total_borrows' => 'integer',
        'review_date' => 'date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getCanBeProcessedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function calculateUsageMetrics(): void
    {
        $lastTransaction = $this->book->transactions()
            ->orderBy('borrow_date', 'desc')
            ->first();

        $this->last_borrowed_date = $lastTransaction?->borrow_date;
        $this->days_since_last_borrow = $lastTransaction ?
            now()->diffInDays($lastTransaction->borrow_date) : 999;
        $this->total_borrows = $this->book->transactions()->count();

        $this->save();
    }
}
