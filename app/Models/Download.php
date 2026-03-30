<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'download_count',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];
}
