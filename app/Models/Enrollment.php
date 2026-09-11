<?php
// app/Models/Enrollment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // LINKS
        'student_id',
        'course_id',
        'campus_id',
        'term_id',

        // SELF-ENROLLMENT TRACKING
        'is_self_enrolled',
        'enrolled_by_user_id',
        'self_enrolled_at',

        // DENORMALIZED STUDENT DATA
        'student_name',
        'student_number',

        // COURSE INFO
        'course_name',
        'course_code',
        'department',

        // INTAKE INFO
        'intake_year',
        'intake_month',
        'enrollment_date',

        // STUDY MODE
        'study_mode',

        // STUDENT TYPE
        'student_type',

        // SPONSORSHIP
        'sponsorship_type',

        // DURATION
        'duration_months',
        'start_date',
        'expected_end_date',
        'actual_end_date',

        // FINANCIAL
        'total_fees',
        'amount_paid',
        'balance',

        // FEE LOCK FIELDS
        'fee_locked',
        'fee_locked_at',
        'fee_locked_by',
        'fee_snapshot',
        'fee_version_at_enrollment',
        'original_fees',
        'fees_modified_by',
        'fees_modified_at',
        'fee_modification_reason',

        // STATUS
        'status',

        // EXTERNAL EXAM
        'requires_external_exam',
        'exam_body',

        // IMPORT TRACKING
        'legacy_code',
        'import_batch',
        'needs_review',

        // NOTES
        'remarks',
        'is_active',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'total_fees' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'requires_external_exam' => 'boolean',
        'is_active' => 'boolean',
        'needs_review' => 'boolean',
        'is_self_enrolled' => 'boolean',
        'self_enrolled_at' => 'datetime',
        'intake_year' => 'integer',
        'duration_months' => 'integer',

        // FEE LOCK CASTS
        'fee_locked' => 'boolean',
        'fee_locked_at' => 'datetime',
        'fee_snapshot' => 'array',
        'original_fees' => 'decimal:2',
        'fees_modified_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function enrolledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function examRegistrations(): HasMany
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function feeLockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fee_locked_by');
    }

    public function feesModifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fees_modified_by');
    }

    // ============================================================
    // BOOT — auto-lock fees and auto-calculate balance
    // ============================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enrollment) {
            // Lock fees on creation
            $enrollment->fee_locked = true;
            $enrollment->fee_locked_at = now();

            // Only set fee_locked_by if we have an authenticated user
            if (auth()->check()) {
                $enrollment->fee_locked_by = auth()->id();
            }

            // Store fee snapshot if course is available
            if ($enrollment->course_id) {
                $course = Course::find($enrollment->course_id);
                if ($course) {
                    $enrollment->fee_snapshot = [
                        'total_fees' => $enrollment->total_fees,
                        'course_name' => $course->name,
                        'course_code' => $course->code,
                        'fee_structure' => $course->fee_breakdown ?? [],
                        'fee_version' => $course->fee_version ?? 'v1.0',
                        'term_id' => $enrollment->term_id,
                        'created_at' => now()->toDateTimeString(),
                        'created_by' => auth()->user()?->name ?? 'System',
                    ];
                    $enrollment->fee_version_at_enrollment = $course->fee_version ?? 'v1.0';
                }
            }
        });

        static::updating(function ($enrollment) {
            if ($enrollment->isDirty('total_fees') && $enrollment->fee_locked) {
                throw new \Exception('Fees are locked for this enrollment and cannot be changed.');
            }
        });

        static::saving(function ($model) {
            $model->balance = $model->total_fees - $model->amount_paid;
        });
    }

    // ============================================================
    // FEE LOCK METHODS
    // ============================================================

    public function isFeeLocked(): bool
    {
        return $this->fee_locked === true;
    }

    public function lockFees(?string $reason = null): void
    {
        $this->fee_locked = true;
        $this->fee_locked_at = now();
        $this->fee_locked_by = auth()->id();

        if (!$this->fee_snapshot && $this->course_id) {
            $course = Course::find($this->course_id);
            if ($course) {
                $this->fee_snapshot = [
                    'total_fees' => $this->total_fees,
                    'course_name' => $course->name,
                    'course_code' => $course->code,
                    'fee_structure' => $course->fee_breakdown ?? [],
                    'fee_version' => $course->fee_version ?? 'v1.0',
                    'term_id' => $this->term_id,
                    'locked_at' => now()->toDateTimeString(),
                    'locked_by' => auth()->user()?->name ?? 'System',
                    'reason' => $reason,
                ];
            }
        }

        $this->save();

        \Log::info('Fees locked for enrollment', [
            'enrollment_id' => $this->id,
            'student' => $this->student_name,
            'locked_by' => auth()->id(),
            'reason' => $reason,
        ]);
    }

    public function unlockFees(string $reason): void
    {
        \Log::warning('Fees unlocked for enrollment', [
            'enrollment_id' => $this->id,
            'student' => $this->student_name,
            'reason' => $reason,
            'admin_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);

        $this->fee_locked = false;
        $this->fee_locked_at = null;
        $this->fee_locked_by = null;

        if ($this->fee_snapshot) {
            $snapshot = $this->fee_snapshot;
            $snapshot['unlocked_at'] = now()->toDateTimeString();
            $snapshot['unlocked_by'] = auth()->user()?->name ?? 'Unknown';
            $snapshot['unlock_reason'] = $reason;
            $this->fee_snapshot = $snapshot;
        }

        $this->save();
    }

    public function isProtectedFromFeeChanges(): bool
    {
        return $this->fee_locked
            || in_array($this->status, ['completed', 'graduated'])
            || $this->payments()->where('status', 'completed')->exists();
    }

    public function canModifyFees(): bool
    {
        if ($this->fee_locked) {
            return false;
        }

        if ($this->payments()->where('status', 'completed')->exists()) {
            return false;
        }

        if (in_array($this->status, ['completed', 'graduated'])) {
            return false;
        }

        return true;
    }

    public function updateFeesWithAudit(float $newTotal, string $reason): self
    {
        if (!$this->canModifyFees()) {
            throw new \Exception('Fees cannot be modified for this enrollment.');
        }

        $oldTotal = $this->total_fees;

        if (!$this->original_fees || $this->original_fees == 0) {
            $this->original_fees = $oldTotal;
        }

        $this->total_fees = $newTotal;
        $this->fees_modified_at = now();
        $this->fees_modified_by = auth()->id();
        $this->fee_modification_reason = $reason;

        if ($this->fee_snapshot) {
            $snapshot = $this->fee_snapshot;
            $snapshot['modified_at'] = now()->toDateTimeString();
            $snapshot['modified_by'] = auth()->user()?->name ?? 'Unknown';
            $snapshot['old_total'] = $oldTotal;
            $snapshot['new_total'] = $newTotal;
            $snapshot['modification_reason'] = $reason;
            $this->fee_snapshot = $snapshot;
        }

        $this->save();

        \Log::info('Fees updated for enrollment', [
            'enrollment_id' => $this->id,
            'student' => $this->student_name,
            'old_amount' => $oldTotal,
            'new_amount' => $newTotal,
            'reason' => $reason,
            'changed_by' => auth()->id(),
        ]);

        return $this;
    }

    public function getFeeBreakdownAttribute(): array
    {
        if ($this->fee_snapshot) {
            return $this->fee_snapshot;
        }

        if ($this->course_id) {
            $course = Course::find($this->course_id);
            if ($course) {
                return [
                    'total_fees' => $this->total_fees,
                    'course_name' => $course->name,
                    'course_code' => $course->code,
                    'fee_structure' => $course->fee_breakdown ?? [],
                ];
            }
        }

        return ['total_fees' => $this->total_fees];
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    public function getPaymentProgressAttribute(): int
    {
        if ($this->total_fees <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->amount_paid / $this->total_fees) * 100));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'graduated' => 'purple',
            'completed' => 'blue',
            'dropped' => 'red',
            'suspended' => 'yellow',
            'pending' => 'gray',
            default => 'gray',
        };
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeHasBalance($query)
    {
        return $query->whereRaw('total_fees > amount_paid');
    }

    public function scopeByIntake($query, $year, $month = null)
    {
        $query->where('intake_year', $year);
        if ($month) {
            $query->where('intake_month', $month);
        }
        return $query;
    }

    public function scopeByExamBody($query, $examBody)
    {
        return $query->where('exam_body', $examBody);
    }

    public function scopeNeedsExamRegistration($query)
    {
        return $query->where('requires_external_exam', true)
            ->whereDoesntHave('examRegistrations', function ($q) {
                $q->whereIn('status', ['registered', 'submitted', 'completed']);
            });
    }

    public function scopeFeeLocked($query)
    {
        return $query->where('fee_locked', true);
    }

    public function scopeFeeUnlocked($query)
    {
        return $query->where('fee_locked', false);
    }

    public function scopeModifiableFees($query)
    {
        return $query->where('fee_locked', false)
            ->whereNotIn('status', ['completed', 'graduated'])
            ->whereDoesntHave('payments', function ($q) {
                $q->where('status', 'completed');
            });
    }

    public function scopeSelfEnrolled($query)
    {
        return $query->where('is_self_enrolled', true);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    /**
 * Can the student download the exam card for this enrollment?
 * Requires full payment of the term.
 */
public function examCardUnlocked(): bool
{
    return $this->getIsFullyPaidAttribute();
}
public function scopeForCourses($query, array $courseIds)
{
    if (empty($courseIds)) {
        return $query->whereRaw('1 = 0'); // never match
    }

    return $query->whereIn('course_id', $courseIds);
}
public function scopeFullyPaid($query)
{
    return $query->whereRaw('total_fees <= amount_paid');
}
    // ============================================================
    // SELF-ENROLLMENT ELIGIBILITY (50% RULE)
    // ============================================================

    /**
     * Minimum percentage of fees that must be paid before a student
     * can self-enroll for the next term.
     */
    public const SELF_ENROLL_THRESHOLD = 50;

    /**
     * Percentage of fees paid so far (0–100, one decimal).
     */
    public function getPercentagePaidAttribute(): float
    {
        $total = (float) $this->total_fees;

        if ($total <= 0) {
            return 0.0;
        }

        $paid = (float) $this->amount_paid;

        return round(min(100, ($paid / $total) * 100), 1);
    }

    /**
     * Does this enrollment meet the 50% threshold for self-enrollment?
     */
    public function meetsSelfEnrollThreshold(): bool
    {
        return $this->percentage_paid >= self::SELF_ENROLL_THRESHOLD;
    }

    /**
     * Amount (KES) still required to reach the 50% threshold.
     * Returns 0 if already at or above the threshold.
     */
    public function getAmountToThresholdAttribute(): float
    {
        $total     = (float) $this->total_fees;
        $paid      = (float) $this->amount_paid;
        $threshold = $total * (self::SELF_ENROLL_THRESHOLD / 100);

        return max(0, round($threshold - $paid, 2));
    }
    // ============================================================
    // STUDENT NUMBER HELPER
    // ============================================================

    public function updateStudentNumberWithCourse()
    {
        if (!$this->student || !$this->course) {
            return null;
        }

        $student = $this->student;
        $courseCode = $this->course->code ?? 'STU';
        $year = $this->intake_year ?? date('Y');
        $baseNumber = $student->student_number;

        if (strpos($baseNumber, '/') !== false) {
            $parts = explode('/', $baseNumber);
            $baseNumber = $parts[1] ?? $parts[0];
        }

        $newStudentNumber = strtoupper($courseCode) . '/' . $baseNumber . '/' . $year;

        $student->student_number = $newStudentNumber;
        $student->save();

        $this->student_number = $newStudentNumber;
        $this->save();

        return $newStudentNumber;
    }
}