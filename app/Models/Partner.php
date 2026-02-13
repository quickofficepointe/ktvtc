<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_path',
        'website',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];
}
