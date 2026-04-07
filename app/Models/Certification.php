<?php
// app/Models/Certification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'issuing_body',
        'logo_path',
        'certificate_number',
        'issue_date',
        'expiry_date',
        'description',
        'is_active',
        'display_order',
        'certification_type', // 'accreditation', 'examination_body', 'professional_body'
        'website',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'display_order' => 'integer',
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

    // Accessor for logo URL
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }

    // Check if certification is valid
    public function getIsValidAttribute()
    {
        if (!$this->expiry_date) return true;
        return $this->expiry_date >= now();
    }

    // Scope for active certifications
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope by type
    public function scopeOfType($query, $type)
    {
        return $query->where('certification_type', $type);
    }
}
