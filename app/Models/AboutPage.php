<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'story',
        'mission',
        'vision',
        'core_values',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    public function images()
    {
        return $this->hasMany(AboutImage::class, 'about_page_id');
    }
}
