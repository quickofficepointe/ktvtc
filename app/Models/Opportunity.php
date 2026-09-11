<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'course_id',
        'term_id',
        'slots',
        'slots_taken',
        'application_opens_at',
        'application_closes_at',
        'start_date',
        'end_date',
        'title',
        'description',
        'requirements',
        'location',
        'is_paid',
        'provides_transport',
        'status',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'application_opens_at' => 'date',
        'application_closes_at' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_paid' => 'boolean',
        'provides_transport' => 'boolean',
        'is_active' => 'boolean',
        'slots' => 'integer',
        'slots_taken' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Opportunity $opportunity) {
            if (auth()->check() && empty($opportunity->created_by)) {
                $opportunity->created_by = auth()->id();
            }
        });
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(AttachmentApplication::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============================================================
    // STATUS & SLOT HELPERS
    // ============================================================

    public function isOpen(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }
        if (!$this->is_active) {
            return false;
        }
        if ($this->application_closes_at && $this->application_closes_at->isPast()) {
            return false;
        }
        if ($this->slotsRemaining() <= 0) {
            return false;
        }
        return true;
    }

    public function isAcceptingApplications(): bool
    {
        return $this->isOpen();
    }

    public function slotsRemaining(): int
    {
        return max(0, (int) $this->slots - (int) $this->slots_taken);
    }

    public function isFull(): bool
    {
        return $this->slotsRemaining() <= 0;
    }

    public function publish(): self
    {
        $this->status = 'open';
        $this->is_active = true;
        $this->save();
        return $this;
    }

    public function close(): self
    {
        $this->status = 'closed';
        $this->save();
        return $this;
    }

    public function incrementSlotsTaken(int $by = 1): self
    {
        $this->slots_taken = min((int) $this->slots, (int) $this->slots_taken + $by);

        if ($this->slots_taken >= $this->slots) {
            $this->status = 'filled';
        }

        $this->save();
        return $this;
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')->where('is_active', true);
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeAvailable($query)
    {
        return $query->whereColumn('slots_taken', '<', 'slots')
                     ->where(function ($q) {
                         $q->whereNull('application_closes_at')
                           ->orWhere('application_closes_at', '>=', now()->toDateString());
                     });
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'open' => 'Open',
            'closed' => 'Closed',
            'filled' => 'Filled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'open' => 'green',
            'closed' => 'red',
            'filled' => 'blue',
            default => 'gray',
        };
    }
}