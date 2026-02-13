<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Campus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'website',
        'description',
        'google_map_link',
        'is_active',
        'opening_time',
        'closing_time',
        'timezone'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name when creating
        static::creating(function ($campus) {
            $campus->slug = $campus->slug ?? Str::slug($campus->name);
        });

        // Auto-update slug when name changes
        static::updating(function ($campus) {
            if ($campus->isDirty('name')) {
                $campus->slug = Str::slug($campus->name);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Scope active campuses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope inactive campuses.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
