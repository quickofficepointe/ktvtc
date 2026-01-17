<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover_image',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Accessor for full image URL
    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'department_id');
    }
}
