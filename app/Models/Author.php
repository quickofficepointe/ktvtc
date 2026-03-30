<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'bio',
        'nationality',
        'birth_date',
        'death_date',
        'photo',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_authors')
                    ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getTotalBooksAttribute(): int
    {
        return $this->books()->count();
    }
}
