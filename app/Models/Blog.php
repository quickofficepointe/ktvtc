<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'cover_image',
        'meta_title',
        'meta_description',
        'is_active',
        'is_published',
        'featured', // Add featured field
        'published_at',
        'created_by',
        'updated_by',
        'approved_by',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'featured' => 'boolean', // Add featured cast
        'published_at' => 'datetime',
    ];

    public static function booted()
    {
        static::creating(function ($blog) {
            $blog->slug = Str::slug($blog->title);
        });
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
