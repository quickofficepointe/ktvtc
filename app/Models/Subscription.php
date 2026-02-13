<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];
}
