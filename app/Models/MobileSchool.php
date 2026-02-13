<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MobileSchool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'address',
        'latitude',
        'longitude',
        'google_map_link',
        'coordinator_name',
        'coordinator_email',
        'coordinator_phone',
        'cover_image',
        'thumbnail',
        'gallery_images',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'gallery_images' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->slug = Str::slug($model->name);

            // Make slug unique
            $originalSlug = $model->slug;
            $counter = 1;
            while (static::where('slug', $model->slug)->exists()) {
                $model->slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->slug = Str::slug($model->name);

                // Make slug unique
                $originalSlug = $model->slug;
                $counter = 1;
                while (static::where('slug', $model->slug)
                    ->where('id', '!=', $model->id)
                    ->exists()) {
                    $model->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
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
     * Get cover image URL.
     */
    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return asset('images/default-school.jpg');
        }

        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }

        return asset('storage/' . $this->cover_image);
    }

    /**
     * Get thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) {
            return $this->cover_image_url;
        }

        if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
            return $this->thumbnail;
        }

        return asset('storage/' . $this->thumbnail);
    }

    /**
     * Get gallery images URLs.
     */
    public function getGalleryImagesUrlsAttribute()
    {
        if (!$this->gallery_images) {
            return [];
        }

        return array_map(function ($image) {
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
            return asset('storage/' . $image);
        }, $this->gallery_images);
    }
}
