<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        // Course Information
        'course_id',
        'intake_period',
        'study_mode',

        // Personal Information
        'first_name',
        'last_name',
        'email',
        'phone',
        'id_number',
        'date_of_birth',
        'gender',

        // Contact Information
        'address',
        'city',
        'county',
        'postal_code',
        'country',

        // Education Background
        'education_level',
        'school_name',
        'graduation_year',
        'mean_grade',
        'application_type',

        // Documents
        'id_document',
        'education_certificates',
        'passport_photo',

        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',

        // Special Needs
        'special_needs',

        // Status and Tracking
        'status',
        'application_number',
        'submitted_at',

        // Metadata
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'submitted_at' => 'datetime',
        'graduation_year' => 'integer',
    ];

    /**
     * Get the course that the application belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
 public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'under_review' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'waiting_list' => 'secondary',
            default => 'secondary'
        };
    }
    /**
     * Scope a query to only include pending applications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include reviewed applications.
     */
    public function scopeReviewed($query)
    {
        return $query->whereIn('status', ['under_review', 'accepted', 'rejected', 'waiting_list']);
    }

    /**
     * Get the full name of the applicant.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Boot function for handling model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            // Generate application number if not set
            if (empty($application->application_number)) {
                $application->application_number = 'APP-' . date('Y') . '-' . strtoupper(uniqid());
            }

            // Set submitted_at timestamp
            if (empty($application->submitted_at)) {
                $application->submitted_at = now();
            }
        });
    }
}
