<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EBookCategory extends Model
{
    use HasFactory;

    protected $table = 'e_book_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function eBooks(): HasMany
    {
        return $this->hasMany(EBook::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getEBooksCountAttribute(): int
    {
        return $this->eBooks()->count();
    }

    public function getActiveEBooksCountAttribute(): int
    {
        return $this->eBooks()->where('is_active', true)->count();
    }
}
