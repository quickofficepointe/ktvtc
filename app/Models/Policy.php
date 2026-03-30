<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent',
    ];
}

