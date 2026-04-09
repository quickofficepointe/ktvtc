<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active'
    ];

    /**
     * Get the books for this category.
     * Specify BOTH foreign key and local key
     */
   public function books()
{
    return $this->hasMany(Book::class, 'book_category_id');
}
}
