<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttachmentApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'opportunity_id',
        'student_id',
        'cv_path',
        'cover_letter_path',
        'student_notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_remarks',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ============================================================
    // STATUS HELPERS
    // ============================================================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isShortlisted(): bool
    {
        return $this->status === 'shortlisted';
    }

    public function isWithdrawn(): bool
    {
        return $this->status === 'withdrawn';
    }

    public function canBeReviewed(): bool
    {
        return in_array($this->status, ['pending', 'shortlisted'], true);
    }

    // ============================================================
    // WORKFLOW
    // ============================================================

    public function markShortlisted(?string $remarks = null): self
    {
        $this->status = 'shortlisted';
        $this->review_remarks = $remarks ?? $this->review_remarks;
        $this->reviewed_by = auth()->id();
        $this->reviewed_at = now();
        $this->save();
        return $this;
    }

    public function markApproved(?string $remarks = null): self
    {
        $this->status = 'approved';
        $this->review_remarks = $remarks ?? $this->review_remarks;
        $this->reviewed_by = auth()->id();
        $this->reviewed_at = now();
        $this->save();
        return $this;
    }

    public function markRejected(?string $remarks = null): self
    {
        $this->status = 'rejected';
        $this->review_remarks = $remarks ?? $this->review_remarks;
        $this->reviewed_by = auth()->id();
        $this->reviewed_at = now();
        $this->save();
        return $this;
    }

    public function withdraw(): self
    {
        $this->status = 'withdrawn';
        $this->save();
        return $this;
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'shortlisted' => 'Shortlisted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'shortlisted' => 'blue',
            'approved' => 'green',
            'rejected' => 'red',
            'withdrawn' => 'gray',
            default => 'gray',
        };
    }
}