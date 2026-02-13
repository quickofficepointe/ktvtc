<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campus_id',
        'application_id',
        'student_number',
        'legacy_student_code',
        'legacy_code',
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
        'address',
        'city',
        'county',
        'postal_code',
        'country',
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_relationship',
        'next_of_kin_address',
        'next_of_kin_email',
        'next_of_kin_id_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'emergency_contact_phone_alt',
        'education_level',
        'school_name',
        'graduation_year',
        'mean_grade',
        'kcse_index_number',
        'medical_conditions',
        'allergies',
        'blood_group',
        'special_needs',
        'disability_type',
        'id_document_path',
        'passport_photo_path',
        'education_certificates_path',
        'other_documents',
        'tshirt_size',
        'remarks',
        'student_category',
        'status',
        'registration_type',
        'import_batch',
        'import_notes',
        'requires_cleanup',
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
     * Get the application that this student came from.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the registrations for this student.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the user account associated with this student.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'student_id');
    }

    /**
     * Scope a query to only include active students.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include students that need cleanup.
     */
    public function scopeNeedsCleanup($query)
    {
        return $query->where('requires_cleanup', true);
    }

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    /**
     * Get the status color attribute.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'graduated' => 'purple',
            'dropped' => 'red',
            'suspended' => 'yellow',
            'alumnus' => 'blue',
            'prospective' => 'amber',
            'historical' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get the registration type label.
     */
    public function getRegistrationTypeLabelAttribute()
    {
        return match($this->registration_type) {
            'excel_import' => 'Excel Import',
            'online_application' => 'Online Application',
            'manual_entry' => 'Manual Entry',
            default => ucfirst(str_replace('_', ' ', $this->registration_type))
        };
    }
}
