<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A student registered for term N of their course enrollment.
 * How much of fee_amount is paid is worked out by TermLedger from the
 * enrollment's payments, oldest term first.
 */
class TermRegistration extends Model
{
    public const TYPE_SELF   = 'self';
    public const TYPE_STAFF  = 'staff';
    public const TYPE_LEGACY = 'legacy';

    protected $fillable = [
        'enrollment_id', 'student_id', 'course_id', 'term_number', 'starts_on', 'ends_on',
        'fee_amount', 'fee_items', 'bills_enrollment', 'course_term_fee_id',
        'registration_type', 'status', 'registered_by', 'override_reason', 'exam_card_downloaded_at',
    ];

    protected $casts = [
        'starts_on'               => 'date',
        'ends_on'                 => 'date',
        'fee_amount'              => 'decimal:2',
        'fee_items'               => 'array',
        'bills_enrollment'        => 'boolean',
        'exam_card_downloaded_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'registered');
    }
}
