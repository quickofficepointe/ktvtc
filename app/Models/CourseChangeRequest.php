<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's request to move to another course. Approved in order by
 * academics, finance and admissions; see CourseChangeService.
 */
class CourseChangeRequest extends Model
{
    public const STAGE_ACADEMICS = 'academics';
    public const STAGE_FINANCE   = 'finance';
    public const STAGE_ADMIN     = 'admin';
    public const APPROVED        = 'approved';
    public const REJECTED        = 'rejected';
    public const CANCELLED       = 'cancelled';

    public const PENDING = [self::STAGE_ACADEMICS, self::STAGE_FINANCE, self::STAGE_ADMIN];

    /** Which role approves each stage. */
    public const STAGE_ROLES = [
        self::STAGE_ACADEMICS => User::ROLE_ACADEMICS,
        self::STAGE_FINANCE   => 7,
        self::STAGE_ADMIN     => 2,
    ];

    public const STAGE_NAMES = [
        self::STAGE_ACADEMICS => 'Academics',
        self::STAGE_FINANCE   => 'Finance',
        self::STAGE_ADMIN     => 'Admissions',
    ];

    protected $fillable = [
        'student_id', 'from_enrollment_id', 'to_course_id', 'reason', 'status',
        'start_term', 'academics_note', 'academics_by', 'academics_at',
        'amount_moved', 'clear_old_balance', 'finance_note', 'finance_by', 'finance_at',
        'admin_note', 'admin_by', 'admin_at', 'rejected_at_stage', 'rejection_reason', 'new_enrollment_id',
    ];

    protected $casts = [
        'academics_at'      => 'datetime',
        'finance_at'        => 'datetime',
        'admin_at'          => 'datetime',
        'amount_moved'      => 'decimal:2',
        'clear_old_balance' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromEnrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'from_enrollment_id');
    }

    public function toCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'to_course_id');
    }

    public function newEnrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'new_enrollment_id');
    }

    public function academicsUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'academics_by');
    }

    public function financeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_by');
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_by');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', self::PENDING);
    }

    public function isPending(): bool
    {
        return in_array($this->status, self::PENDING, true);
    }

    /** Can this user act on the request at its current stage? */
    public function awaits(User $user): bool
    {
        return $this->isPending() && (int) $user->role === self::STAGE_ROLES[$this->status];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::APPROVED  => 'Approved',
            self::REJECTED  => 'Not approved (' . (self::STAGE_NAMES[$this->rejected_at_stage] ?? '') . ')',
            self::CANCELLED => 'Withdrawn',
            default         => 'Waiting for ' . self::STAGE_NAMES[$this->status],
        };
    }
}
