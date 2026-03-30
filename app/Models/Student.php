<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        // Institution & Links
        'campus_id',
        'application_id',

        // Student Identification
        'student_number',
        'legacy_student_code',
        'legacy_code',

        // Personal Information
        'title',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'id_number',
        'id_type',
        'date_of_birth',
        'gender',
        'marital_status',

        // Contact Information
        'address',
        'city',
        'county',
        'postal_code',
        'country',

        // Next of Kin
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_relationship',
        'next_of_kin_address',
        'next_of_kin_email',
        'next_of_kin_id_number',

        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'emergency_contact_phone_alt',

        // Education Background
        'education_level',
        'school_name',
        'graduation_year',
        'mean_grade',
        'kcse_index_number',

        // Medical & Special Needs
        'medical_conditions',
        'allergies',
        'blood_group',
        'special_needs',
        'disability_type',

        // Documents
        'id_document_path',
        'passport_photo_path',
        'education_certificates_path',
        'other_documents',

        // Additional Info
        'tshirt_size',
        'remarks',
        'student_category',

        // Status
        'status',
        'registration_type',

        // Import Metadata
        'import_batch',
        'import_notes',
        'requires_cleanup',

        // Timestamps
        'registration_date',
        'last_activity_date',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registration_date' => 'date',
        'last_activity_date' => 'date',
        'graduation_year' => 'integer',
        'other_documents' => 'array',
        'requires_cleanup' => 'boolean',
    ];

    /**
     * Get the campus that the student belongs to.
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the application that the student came from.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the enrollments for the student.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the active enrollment for the student.
     */
    public function activeEnrollment()
    {
        return $this->hasOne(Enrollment::class)->where('status', 'active');
    }

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    /**
     * Get the formatted name with title.
     */
    public function getFormattedNameAttribute()
    {
        $name = $this->full_name;
        return $this->title ? "{$this->title} {$name}" : $name;
    }

    /**
     * Scope a query to only include active students.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include students needing cleanup.
     */
    public function scopeNeedsCleanup($query)
    {
        return $query->where('requires_cleanup', true);
    }

    /**
     * Scope a query to filter by campus.
     */
    public function scopeForCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }
}
