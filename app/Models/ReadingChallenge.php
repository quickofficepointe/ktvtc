<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'year',
        'target_books',
        'completed_books',
        'is_completed',
    ];

    protected $casts = [
        'year' => 'integer',
        'target_books' => 'integer',
        'completed_books' => 'integer',
        'is_completed' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_books === 0) {
            return 0;
        }

        return ($this->completed_books / $this->target_books) * 100;
    }

    public function getRemainingBooksAttribute(): int
    {
        return max(0, $this->target_books - $this->completed_books);
    }

    public function updateProgress(): void
    {
        $completedBooks = $this->member->readingHistories()
            ->whereYear('end_date', $this->year)
            ->completed()
            ->count();

        $this->completed_books = $completedBooks;
        $this->is_completed = $completedBooks >= $this->target_books;
        $this->save();
    }

    public function scopeForCurrentYear($query)
    {
        return $query->where('year', now()->year);
    }
}
