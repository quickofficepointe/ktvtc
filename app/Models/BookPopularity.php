<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookPopularity extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'borrow_count',
        'reservation_count',
        'view_count',
        'popularity_score',
    ];

    protected $casts = [
        'borrow_count' => 'integer',
        'reservation_count' => 'integer',
        'view_count' => 'integer',
        'popularity_score' => 'decimal:2',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function calculatePopularityScore(): void
    {
        $borrowWeight = 0.5;
        $reservationWeight = 0.3;
        $viewWeight = 0.2;

        $this->popularity_score =
            ($this->borrow_count * $borrowWeight) +
            ($this->reservation_count * $reservationWeight) +
            ($this->view_count * $viewWeight);

        $this->save();
    }

    public function incrementBorrowCount(): void
    {
        $this->increment('borrow_count');
        $this->calculatePopularityScore();
    }

    public function incrementReservationCount(): void
    {
        $this->increment('reservation_count');
        $this->calculatePopularityScore();
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
        $this->calculatePopularityScore();
    }

    public function scopeMostPopular($query, $limit = 10)
    {
        return $query->orderBy('popularity_score', 'desc')
                     ->limit($limit);
    }
}
