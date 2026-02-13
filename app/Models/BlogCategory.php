<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_image',
        'meta_description',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    // Auto-generate slug from name
    public static function booted()
    {
        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class, 'category_id');
    }
}
