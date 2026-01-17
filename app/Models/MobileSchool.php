<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileSchool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'coordinates', // latitude,longitude stored as string
        'google_map_link',
        'coordinator',
        'created_by',
        'slug',
        'updated_by',
        'ip_address',
        'user_agent'
    ];
}
