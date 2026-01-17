<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'desktop_image',
        'mobile_image',
        'text',
        'button_text',
        'button_link',
        'is_active',
        'is_published',
        'published_at',
        'order',
        'created_by',
        'updated_by',
        'approved_by',
        // Remove ip_address and user_agent if they don't exist in migration
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Relationships
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

    // Accessors for image URLs
    public function getDesktopImageUrlAttribute()
    {
        return $this->desktop_image ? asset('storage/' . $this->desktop_image) : null;
    }

    public function getMobileImageUrlAttribute()
    {
        return $this->mobile_image ? asset('storage/' . $this->mobile_image) : null;
    }
}
