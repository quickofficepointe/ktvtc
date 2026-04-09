<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookDonation extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'country',
        'book_title',
        'author',
        'isbn',
        'quantity',
        'condition',
        'additional_info',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];
}
