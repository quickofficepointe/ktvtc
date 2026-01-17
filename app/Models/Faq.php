<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'slug',
        'answer',
        'is_active',
        'position',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];
}
