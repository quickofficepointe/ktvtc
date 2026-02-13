<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'location',
        'event_start_date',
        'event_end_date',
        'registration_start_date',
        'registration_end_date',
        'event_type',
        'department',
        'target_audience',
        'is_paid',
        'price',
        'early_bird_price',
        'early_bird_end_date',
        'max_attendees',
        'registered_attendees',
        'cover_image',
        'banner_image',
        'is_active',
        'is_published',
        'is_featured',
        'published_at',
        'view_count',
        'sort_order',
        'organizer_name',
        'organizer_email',
        'organizer_phone',
        'organizer_website',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'event_start_date' => 'datetime',
        'event_end_date' => 'datetime',
        'registration_start_date' => 'datetime',
        'registration_end_date' => 'datetime',
        'early_bird_end_date' => 'datetime',
        'published_at' => 'datetime',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'early_bird_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);

                $originalSlug = $event->slug;
                $count = 1;
                while (static::where('slug', $event->slug)->exists()) {
                    $event->slug = $originalSlug . '-' . $count++;
                }
            }
        });

        static::updating(function ($event) {
            if ($event->isDirty('title') && !$event->isDirty('slug')) {
                $event->slug = Str::slug($event->title);

                $originalSlug = $event->slug;
                $count = 1;
                while (static::where('slug', $event->slug)->where('id', '!=', $event->id)->exists()) {
                    $event->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('event_start_date', '>=', now())->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_published', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeBootcamps($query)
    {
        return $query->where('event_type', 'bootcamp');
    }

    public function scopeTrips($query)
    {
        return $query->where('event_type', 'trip');
    }

    // Helper Methods
    public function isUpcoming()
    {
        return $this->event_start_date->isFuture();
    }

    public function isRegistrationOpen()
    {
        $now = now();
        $registrationStart = $this->registration_start_date ?? $this->created_at;
        $registrationEnd = $this->registration_end_date ?? $this->event_start_date;

        return $now->between($registrationStart, $registrationEnd);
    }

    public function isEarlyBirdActive()
    {
        return $this->early_bird_end_date && now()->lte($this->early_bird_end_date);
    }

    public function getCurrentPrice()
    {
        if ($this->isEarlyBirdActive() && $this->early_bird_price) {
            return $this->early_bird_price;
        }
        return $this->price;
    }

    public function isFree()
    {
        return !$this->is_paid || $this->getCurrentPrice() == 0;
    }

    public function hasSpotsAvailable()
    {
        return is_null($this->max_attendees) || $this->registered_attendees < $this->max_attendees;
    }

    public function getSpotsRemaining()
    {
        if (is_null($this->max_attendees)) {
            return 'Unlimited';
        }
        return $this->max_attendees - $this->registered_attendees;
    }

    public function getDuration()
    {
        return $this->event_start_date->diffForHumans($this->event_end_date, true);
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
