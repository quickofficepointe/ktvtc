<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'isbn',
        'title',
        'description',
        'publication_year',
        'publisher',
        'edition',
        'language',
        'page_count',
        'cover_image',
        'price',
        'category_id',
        'total_copies',
        'available_copies',
        'is_available',
        'location',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'publication_year' => 'integer',
        'page_count' => 'integer',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_authors')
                    ->withTimestamps();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    // Correct relationship: Book has transactions through items
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Transaction::class,
            Item::class,
            'book_id',      // Foreign key on items table
            'item_id',      // Foreign key on transactions table
            'id',           // Local key on books table
            'id'            // Local key on items table
        );
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function popularity(): HasOne
    {
        return $this->hasOne(BookPopularity::class);
    }

    public function getAuthorsNamesAttribute(): string
    {
        return $this->authors->pluck('full_name')->implode(', ');
    }

    public function getBorrowedCopiesAttribute(): int
    {
        return $this->total_copies - $this->available_copies;
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                     ->where('available_copies', '>', 0);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
