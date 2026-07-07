<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // LINKS
        'student_id',
        'course_id',
        'campus_id',

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

        // 🔒 FEE LOCK FIELDS (NEW)
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
        'requires_external_exam' => 'boolean',
        'is_active' => 'boolean',
        'needs_review' => 'boolean',
        'intake_year' => 'integer',
        'duration_months' => 'integer',

        // 🔒 NEW CASTS
        'fee_locked' => 'boolean',
        'fee_locked_at' => 'datetime',
        'fee_snapshot' => 'array',
        'original_fees' => 'decimal:2',
        'fees_modified_at' => 'datetime',
    ];

    /**
     * ============ RELATIONSHIPS ============
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    public function examRegistrations()
    {
        return $this->hasMany(ExamRegistration::class);
    }

    public function feeLockedBy()
    {
        return $this->belongsTo(User::class, 'fee_locked_by');
    }

    public function feesModifiedBy()
    {
        return $this->belongsTo(User::class, 'fees_modified_by');
    }

    /**
     * ============ BOOT METHOD - AUTO LOCK FEES ============
     */
    protected static function boot()
    {
        parent::boot();

        // 🔒 Automatically lock fees when creating enrollment
        static::creating(function ($enrollment) {
            $enrollment->fee_locked = true;
            $enrollment->fee_locked_at = now();
            $enrollment->fee_locked_by = auth()->id();

            // Store the fee breakdown snapshot if course exists
            if ($enrollment->course_id) {
                $course = Course::find($enrollment->course_id);
                if ($course) {
                    $enrollment->fee_snapshot = [
                        'total_fees' => $enrollment->total_fees,
                        'course_name' => $course->name,
                        'course_code' => $course->code,
                        'fee_structure' => $course->fee_breakdown ?? [],
                        'fee_version' => $course->fee_version ?? 'v1.0',
                        'created_at' => now()->toDateTimeString(),
                        'created_by' => auth()->user()?->name ?? 'System',
                    ];
                    $enrollment->fee_version_at_enrollment = $course->fee_version ?? 'v1.0';
                }
            }
        });

        // 🔒 Prevent changing total_fees if fee is locked
        static::updating(function ($enrollment) {
            if ($enrollment->isDirty('total_fees') && $enrollment->fee_locked) {
                throw new \Exception('Fees are locked for this enrollment and cannot be changed.');
            }
        });

        // Auto-calculate balance
        static::saving(function ($model) {
            $model->balance = $model->total_fees - $model->amount_paid;
        });
    }

    /**
     * ============ FEE LOCK METHODS ============
     */

    /**
     * Check if fees are locked
     */
    public function isFeeLocked(): bool
    {
        return $this->fee_locked === true;
    }

    /**
     * Lock fees manually (admin only)
     */
    public function lockFees(?string $reason = null): void
    {
        $this->fee_locked = true;
        $this->fee_locked_at = now();
        $this->fee_locked_by = auth()->id();

        // Store snapshot if not already stored
        if (!$this->fee_snapshot && $this->course_id) {
            $course = Course::find($this->course_id);
            if ($course) {
                $this->fee_snapshot = [
                    'total_fees' => $this->total_fees,
                    'course_name' => $course->name,
                    'course_code' => $course->code,
                    'fee_structure' => $course->fee_breakdown ?? [],
                    'fee_version' => $course->fee_version ?? 'v1.0',
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
            'reason' => $reason
        ]);
    }

    /**
     * Unlock fees (ADMIN ONLY - with caution)
     */
    public function unlockFees(string $reason): void
    {
        // Log the unlock action
        \Log::warning('Fees unlocked for enrollment', [
            'enrollment_id' => $this->id,
            'student' => $this->student_name,
            'reason' => $reason,
            'admin_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        $this->fee_locked = false;
        $this->fee_locked_at = null;
        $this->fee_locked_by = null;

        // Update snapshot to reflect unlock
        if ($this->fee_snapshot) {
            $snapshot = $this->fee_snapshot;
            $snapshot['unlocked_at'] = now()->toDateTimeString();
            $snapshot['unlocked_by'] = auth()->user()?->name ?? 'Unknown';
            $snapshot['unlock_reason'] = $reason;
            $this->fee_snapshot = $snapshot;
        }

        $this->save();
    }

    /**
     * Check if enrollment is protected from fee changes
     */
    public function isProtectedFromFeeChanges(): bool
    {
        return $this->fee_locked ||
               in_array($this->status, ['completed', 'graduated']) ||
               $this->payments()->where('status', 'completed')->exists();
    }

    /**
     * Check if fees can be modified
     */
    public function canModifyFees(): bool
    {
        // If fee is locked, cannot modify
        if ($this->fee_locked) {
            return false;
        }

        // If any payment has been made, cannot modify
        if ($this->payments()->where('status', 'completed')->exists()) {
            return false;
        }

        // If enrollment is completed, cannot modify
        if (in_array($this->status, ['completed', 'graduated'])) {
            return false;
        }

        return true;
    }

    /**
     * Update fees with audit trail (ADMIN ONLY)
     */
    public function updateFeesWithAudit(float $newTotal, string $reason): self
    {
        if (!$this->canModifyFees()) {
            throw new \Exception('Fees cannot be modified for this enrollment.');
        }

        $oldTotal = $this->total_fees;

        // Store original fee if not already stored
        if (!$this->original_fees || $this->original_fees == 0) {
            $this->original_fees = $oldTotal;
        }

        $this->total_fees = $newTotal;
        $this->fees_modified_at = now();
        $this->fees_modified_by = auth()->id();
        $this->fee_modification_reason = $reason;

        // Update snapshot
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

        // Log the fee change
        \Log::info('Fees updated for enrollment', [
            'enrollment_id' => $this->id,
            'student' => $this->student_name,
            'old_amount' => $oldTotal,
            'new_amount' => $newTotal,
            'reason' => $reason,
            'changed_by' => auth()->id()
        ]);

        return $this;
    }

    /**
     * Get the fee breakdown (from snapshot or generate)
     */
    public function getFeeBreakdownAttribute(): array
    {
        if ($this->fee_snapshot) {
            return $this->fee_snapshot;
        }

        // Generate from course if available
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

    /**
     * ============ ACCESSORS ============
     */
    public function getBalanceAttribute()
    {
        return $this->total_fees - $this->amount_paid;
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->balance <= 0;
    }

    public function getPaymentProgressAttribute()
    {
        if ($this->total_fees <= 0) return 0;
        return min(100, round(($this->amount_paid / $this->total_fees) * 100));
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'graduated' => 'purple',
            'completed' => 'blue',
            'dropped' => 'red',
            'suspended' => 'yellow',
            'pending' => 'gray',
            default => 'gray'
        };
    }

    /**
     * ============ SCOPES ============
     */
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
        $query = $query->where('intake_year', $year);
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
            ->whereDoesntHave('examRegistrations', function($q) {
                $q->whereIn('status', ['registered', 'submitted', 'completed']);
            });
    }

    /**
     * Scope: Fee locked enrollments
     */
    public function scopeFeeLocked($query)
    {
        return $query->where('fee_locked', true);
    }

    /**
     * Scope: Fee unlocked enrollments
     */
    public function scopeFeeUnlocked($query)
    {
        return $query->where('fee_locked', false);
    }

    /**
     * Scope: Enrollments with modifiable fees
     */
    public function scopeModifiableFees($query)
    {
        return $query->where('fee_locked', false)
            ->whereNotIn('status', ['completed', 'graduated'])
            ->whereDoesntHave('payments', function($q) {
                $q->where('status', 'completed');
            });
    }

    /**
     * ============ STUDENT NUMBER METHOD ============
     */
    public function updateStudentNumberWithCourse()
    {
        if (!$this->student || !$this->course) {
            return;
        }

        $student = $this->student;
        $courseCode = $this->course->code ?? 'STU';
        $year = $this->intake_year ?? date('Y');
        $baseNumber = $student->student_number;

        // If student number is already formatted, extract the number part
        if (strpos($baseNumber, '/') !== false) {
            $parts = explode('/', $baseNumber);
            $baseNumber = $parts[1] ?? $parts[0];
        }

        // Generate new formatted student number
        $newStudentNumber = strtoupper($courseCode) . '/' . $baseNumber . '/' . $year;

        // Update student number
        $student->student_number = $newStudentNumber;
        $student->save();

        // Update enrollment student_number field too
        $this->student_number = $newStudentNumber;
        $this->save();

        return $newStudentNumber;
    }
}
