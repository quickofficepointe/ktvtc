<?php
// app/Models/AcademicTerm.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicTerm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'campus_id',
        'name',
        'code',
        'short_code',
        'term_number',
        'academic_year',
        'academic_year_name',
        'start_date',
        'end_date',
        'fee_due_date',
        'registration_start_date',
        'registration_end_date',
        'late_registration_start_date',
        'late_registration_end_date',
        'exam_registration_start_date',
        'exam_registration_end_date',
        'exam_start_date',
        'exam_end_date',
        'is_active',
        'is_current',
        'is_registration_open',
        'is_fee_generation_locked',
        'allow_late_registration',
        'late_registration_fee',
        'late_payment_fee',
        'late_payment_percentage',
        'description',
        'notes',
        'sort_order',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fee_due_date' => 'date',
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'late_registration_start_date' => 'date',
        'late_registration_end_date' => 'date',
        'exam_registration_start_date' => 'date',
        'exam_registration_end_date' => 'date',
        'exam_start_date' => 'date',
        'exam_end_date' => 'date',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
        'is_registration_open' => 'boolean',
        'is_fee_generation_locked' => 'boolean',
        'allow_late_registration' => 'boolean',
        'late_registration_fee' => 'decimal:2',
        'late_payment_fee' => 'decimal:2',
        'late_payment_percentage' => 'integer',
        'academic_year' => 'integer',
        'term_number' => 'integer',
        'sort_order' => 'integer',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Enrollments that belong to this term.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'term_id');
    }

    /**
     * Fee structures that are scoped to this term.
     */
    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'term_id');
    }

    // ============================================================
    // WINDOW CHECKS — is self-enrollment open right now?
    // ============================================================

    /**
     * Is the current date inside the normal registration window?
     */
    public function isInRegistrationWindow(): bool
    {
        $today = now()->startOfDay();

        return $this->is_registration_open
            && $this->registration_start_date
            && $this->registration_end_date
            && $today->between($this->registration_start_date, $this->registration_end_date);
    }

    /**
     * Is the current date inside the late-registration window?
     */
    public function isInLateWindow(): bool
    {
        $today = now()->startOfDay();

        return $this->allow_late_registration
            && $this->late_registration_start_date
            && $this->late_registration_end_date
            && $today->between($this->late_registration_start_date, $this->late_registration_end_date);
    }

    /**
     * Can a student self-enroll right now?
     */
    public function isOpenForSelfEnrollment(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return $this->isInRegistrationWindow() || $this->isInLateWindow();
    }

    /**
     * Late fee that applies right now (null if not in late window).
     */
    public function currentLateFee(): ?float
    {
        return $this->isInLateWindow()
            ? (float) $this->late_registration_fee
            : null;
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeRegistrationOpen($query)
    {
        return $query->where('is_registration_open', true);
    }

    public function scopeByCampus($query, $campusId)
    {
        return $query->where(function ($q) use ($campusId) {
            $q->where('campus_id', $campusId)
              ->orWhereNull('campus_id');
        });
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('academic_year', 'desc')
            ->orderBy('term_number');
    }

    /**
     * Scope: terms currently open for student self-enrollment.
     */
    public function scopeOpenForEnrollment($q)
    {
        $today = now()->toDateString();

        return $q->where('is_active', true)
            ->where('is_registration_open', true)
            ->where(function ($sub) use ($today) {
                // Normal window
                $sub->where(function ($w) use ($today) {
                    $w->whereDate('registration_start_date', '<=', $today)
                      ->whereDate('registration_end_date', '>=', $today);
                })
                // Or late window
                ->orWhere(function ($w) use ($today) {
                    $w->where('allow_late_registration', true)
                      ->whereDate('late_registration_start_date', '<=', $today)
                      ->whereDate('late_registration_end_date', '>=', $today);
                });
            });
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->academic_year . ')';
    }

    public function getDateRangeAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return '—';
        }

        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getStatusColorAttribute()
    {
        if ($this->is_current) {
            return 'green';
        }
        if ($this->is_active) {
            return 'blue';
        }
        return 'gray';
    }

    public function getStatusLabelAttribute()
    {
        if ($this->is_current) {
            return 'Current';
        }
        if ($this->is_active) {
            return 'Active';
        }
        return 'Inactive';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: "Term {$this->term_number} {$this->academic_year}";
    }

    // ============================================================
    // METHODS
    // ============================================================

    public function isCurrent(): bool
    {
        return (bool) $this->is_current;
    }

    public function isRegistrationOpen(): bool
    {
        return (bool) $this->is_registration_open;
    }

    public function isFeeGenerationLocked(): bool
    {
        return (bool) $this->is_fee_generation_locked;
    }

    public function getLateRegistrationFeeAmount(): float
    {
        return $this->late_registration_fee > 0
            ? (float) $this->late_registration_fee
            : 0.0;
    }

    public function getLatePaymentFeeAmount(float $balance): float
    {
        if ($this->late_payment_percentage > 0) {
            return ($balance * $this->late_payment_percentage) / 100;
        }

        return (float) $this->late_payment_fee;
    }
}