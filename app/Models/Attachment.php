<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'application_id',
        'opportunity_id',
        'company_id',
        'term_id',

        'company_name',
        'company_sector',
        'company_address',
        'company_phone',
        'company_email',
        'company_registered',

        'supervisor_name',
        'supervisor_position',
        'supervisor_phone',
        'supervisor_email',

        'start_date',
        'end_date',
        'duration_weeks',

        'job_description',
        'learning_objectives',
        'student_notes',

        'acceptance_letter_path',
        'placement_letter_path',

        'status',

        // ILO review
        'reviewed_by',
        'reviewed_at',
        'review_remarks',

        // ILO grading
        'graded_by',
        'graded_at',
        'grade',
        'grader_remarks',

        // ILO visit tracking
        'last_visit_date',
        'last_visit_notes',

        // Trainer approval
        'trainer_approved_by',
        'trainer_approved_at',
        'trainer_remarks',
        'trainer_course_id',

        // Trainer eligibility + visits
        'is_eligible_for_attachment',
        'eligibility_checked_at',
        'eligibility_checked_by',
        'eligibility_notes',
        'last_trainer_visit_at',
        'trainer_visit_count',
    ];

    protected $casts = [
        'start_date'                 => 'date',
        'end_date'                   => 'date',
        'reviewed_at'                => 'datetime',
        'graded_at'                  => 'datetime',
        'last_visit_date'            => 'date',
        'duration_weeks'             => 'integer',
        'company_registered'         => 'boolean',

        // Trainer casts
        'trainer_approved_at'        => 'datetime',
        'eligibility_checked_at'     => 'datetime',
        'last_trainer_visit_at'      => 'datetime',
        'is_eligible_for_attachment' => 'boolean',
        'trainer_visit_count'        => 'integer',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(AttachmentApplication::class, 'application_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // ---- Trainer relations -------------------------------------

    public function trainerApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_approved_by');
    }

    public function trainerApprovedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'trainer_course_id');
    }

    public function eligibilityCheckedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eligibility_checked_by');
    }

    // ============================================================
    // BOOT — auto-compute duration & registered flag
    // ============================================================

    protected static function booted(): void
    {
        static::saving(function (Attachment $attachment) {
            if ($attachment->start_date && $attachment->end_date) {
                $attachment->duration_weeks = (int) $attachment->start_date
                    ->diffInWeeks($attachment->end_date);
            }

            $attachment->company_registered = !is_null($attachment->company_id);
        });
    }

    // ============================================================
    // STATUS HELPERS
    // ============================================================

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isTrainerApproved(): bool
    {
        return $this->status === 'trainer_approved';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'graded'], true);
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function needsRevision(): bool
    {
        return $this->status === 'needs_revision';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isEligibleForAttachment(): bool
    {
        return (bool) $this->is_eligible_for_attachment;
    }

    // ---- Gate predicates ---------------------------------------

    public function canBeEditedByStudent(): bool
    {
        return in_array($this->status, ['draft', 'needs_revision'], true);
    }

    public function canBeReviewedByIlo(): bool
    {
        return in_array($this->status, ['submitted', 'trainer_approved'], true);
    }

    /**
     * ILO can activate a submission that has been trainer-approved
     * (or, if you want trainer sign-off to be optional, a plain submission).
     */
    public function canBeActivated(): bool
    {
        return in_array($this->status, ['submitted', 'trainer_approved'], true);
    }

    /**
     * Trainer can approve only while still in 'submitted'.
     * Once trainer-approved, further trainer action is blocked.
     */
    public function canBeApprovedByTrainer(): bool
    {
        return $this->status === 'submitted';
    }

    public function canBeCompleted(): bool
    {
        return $this->status === 'active';
    }

    public function canBeGraded(): bool
    {
        return in_array($this->status, ['completed', 'active'], true);
    }

    // ============================================================
    // WORKFLOW ACTIONS — ILO side
    // ============================================================

    public function submit(): self
    {
        $this->status = 'submitted';
        $this->save();
        return $this;
    }

    public function approveAndActivate(?string $remarks = null): self
    {
        $this->status         = 'active';
        $this->reviewed_by    = auth()->id();
        $this->reviewed_at    = now();
        $this->review_remarks = $remarks ?? $this->review_remarks;
        $this->save();
        return $this;
    }

    public function requestRevision(string $remarks): self
    {
        $this->status         = 'needs_revision';
        $this->reviewed_by    = auth()->id();
        $this->reviewed_at    = now();
        $this->review_remarks = $remarks;
        $this->save();
        return $this;
    }

    public function reject(string $remarks): self
    {
        $this->status         = 'rejected';
        $this->reviewed_by    = auth()->id();
        $this->reviewed_at    = now();
        $this->review_remarks = $remarks;
        $this->save();
        return $this;
    }

    public function markCompleted(?string $remarks = null): self
    {
        $this->status = 'completed';
        if ($remarks) {
            $this->review_remarks = $remarks;
        }
        $this->save();
        return $this;
    }

    public function grade(string $grade, ?string $remarks = null): self
    {
        $this->status         = 'graded';
        $this->grade          = $grade;
        $this->grader_remarks = $remarks;
        $this->graded_by      = auth()->id();
        $this->graded_at      = now();
        $this->save();
        return $this;
    }

    public function logVisit(string $notes, ?string $date = null): self
    {
        $this->last_visit_date  = $date ?? now()->toDateString();
        $this->last_visit_notes = $notes;
        $this->save();
        return $this;
    }

    // ============================================================
    // WORKFLOW ACTIONS — Trainer side
    // ============================================================

    /**
     * Trainer approves → status becomes 'trainer_approved'.
     * Does NOT activate; ILO does that final step.
     */
    public function approveByTrainer(User $trainer, int $courseId, ?string $remarks = null): self
    {
        if (!$this->canBeApprovedByTrainer()) {
            throw new \RuntimeException(
                'Attachment cannot be approved by trainer from status: ' . $this->status
            );
        }

        $this->status              = 'trainer_approved';
        $this->trainer_approved_by = $trainer->id;
        $this->trainer_approved_at = now();
        $this->trainer_remarks     = $remarks;
        $this->trainer_course_id   = $courseId;

        $this->save();

        \Log::info('Attachment approved by trainer', [
            'attachment_id' => $this->id,
            'student_id'    => $this->student_id,
            'trainer_id'    => $trainer->id,
            'course_id'     => $courseId,
            'status'        => $this->status,
        ]);

        return $this;
    }

    /**
     * Trainer sends back for revision.
     */
    public function requestRevisionByTrainer(User $trainer, string $remarks): self
    {
        if (!$this->canBeApprovedByTrainer()) {
            throw new \RuntimeException(
                'Attachment cannot be sent for revision from status: ' . $this->status
            );
        }

        $this->status              = 'needs_revision';
        $this->trainer_approved_by = $trainer->id;
        $this->trainer_approved_at = now();
        $this->trainer_remarks     = $remarks;

        $this->save();

        return $this;
    }

    /**
     * Toggle or set eligibility for attachment.
     */
    public function markEligible(User $trainer, bool $eligible, ?string $notes = null): self
    {
        $this->is_eligible_for_attachment = $eligible;
        $this->eligibility_checked_at     = now();
        $this->eligibility_checked_by     = $trainer->id;
        $this->eligibility_notes          = $notes;

        $this->save();

        return $this;
    }

    /**
     * Trainer logs an on-site visit.
     */
    public function logTrainerVisit(?string $notes = null): self
    {
        $this->last_trainer_visit_at = now();
        $this->trainer_visit_count   = ($this->trainer_visit_count ?? 0) + 1;

        if ($notes) {
            $this->trainer_remarks = trim(
                ($this->trainer_remarks ?? '')
                . "\n[" . now()->toDateTimeString() . "] " . $notes
            );
        }

        $this->save();

        return $this;
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Draft',
            'submitted'        => 'Submitted — awaiting trainer review',
            'trainer_approved' => 'Trainer approved — awaiting ILO activation',
            'needs_revision'   => 'Needs Revision',
            'active'           => 'Active',
            'completed'        => 'Completed',
            'graded'           => 'Graded',
            'rejected'         => 'Rejected',
            default            => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'gray',
            'submitted'        => 'blue',
            'trainer_approved' => 'indigo',
            'needs_revision'   => 'amber',
            'active'           => 'green',
            'completed'        => 'purple',
            'graded'           => 'emerald',
            'rejected'         => 'red',
            default            => 'gray',
        };
    }

    /**
     * 0–100 progress through the attachment window.
     */
    public function getProgressPercentageAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);

        if ($totalDays <= 0) {
            return 100;
        }

        $elapsed = $this->start_date->diffInDays(now());

        return (int) min(100, max(0, round(($elapsed / $totalDays) * 100)));
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }
        return max(0, (int) now()->diffInDays($this->end_date, false));
    }

    public function getDaysElapsedAttribute(): int
    {
        if (!$this->start_date) {
            return 0;
        }
        return max(0, (int) $this->start_date->diffInDays(now()));
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeTrainerApproved($query)
    {
        return $query->where('status', 'trainer_approved');
    }

    public function scopePendingTrainerApproval($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['completed', 'graded']);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeSelfFound($query)
    {
        return $query->whereNull('company_id');
    }

    public function scopeRegistered($query)
    {
        return $query->whereNotNull('company_id');
    }

    /**
     * Attachments whose student is enrolled in any of the given courses.
     */
    public function scopeForCourses($query, array $courseIds)
    {
        if (empty($courseIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('student.enrollments', function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds);
        });
    }

    /**
     * Attachments that a given trainer can currently act on:
     * still 'submitted' AND student enrolled in one of their courses.
     */
    public function scopeApprovableByTrainer($query, User $trainer)
    {
        return $query->where('status', 'submitted')
                     ->forCourses($trainer->trainerCourseIds());
    }
}