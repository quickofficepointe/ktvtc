<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'book_id',
        'start_date',
        'end_date',
        'pages_read',
        'reading_status',
        'rating',
        'review',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pages_read' => 'integer',
        'rating' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function getReadingProgressAttribute(): float
    {
        if (!$this->book->page_count) {
            return 0;
        }

        return ($this->pages_read / $this->book->page_count) * 100;
    }

    public function getReadingDurationAttribute(): int
    {
        if (!$this->start_date) {
            return 0;
        }

        $endDate = $this->end_date ?? now();
        return $endDate->diffInDays($this->start_date);
    }

    public function scopeCompleted($query)
    {
        return $query->where('reading_status', 'completed');
    }

    public function scopeCurrentlyReading($query)
    {
        return $query->where('reading_status', 'reading');
    }
}
