<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentContact extends Model
{
    protected $fillable = [
        'high_school_student_id',  // CHANGED from student_id
        'contact_type',
        'name',
        'phone',
        'email',
        'relationship',
        'is_primary',
        'receive_alerts',
        'receive_low_balance',
        'receive_daily_summary',
        'receive_funding_updates',
        'last_contacted_at',
        'total_sms_sent',
        'total_sms_failed',
        'is_verified',
        'verified_at',
        'verified_by',
        'notes'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'receive_alerts' => 'boolean',
        'receive_low_balance' => 'boolean',
        'receive_daily_summary' => 'boolean',
        'receive_funding_updates' => 'boolean',
        'is_verified' => 'boolean',
        'last_contacted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the high school student
     */
    public function student(): BelongsTo  // CHANGED from student to highSchoolStudent
    {
        return $this->belongsTo(HighSchoolStudent::class, 'high_school_student_id');
    }

    /**
     * Get the verifier
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope for primary contacts
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for contacts that receive alerts
     */
    public function scopeReceivesAlerts($query)
    {
        return $query->where('receive_alerts', true);
    }
}
