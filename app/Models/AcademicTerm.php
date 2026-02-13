<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    /**
     * ============ RELATIONSHIPS ============
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * ============ SCOPES ============
     */
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
        return $query->where('campus_id', $campusId)
            ->orWhereNull('campus_id'); // Include global terms
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
     * ============ ACCESSORS ============
     */
    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->academic_year . ')';
    }

    public function getDateRangeAttribute()
    {
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

    /**
     * ============ METHODS ============
     */
    public function isCurrent()
    {
        return $this->is_current;
    }

    public function isRegistrationOpen()
    {
        return $this->is_registration_open;
    }

    public function isFeeGenerationLocked()
    {
        return $this->is_fee_generation_locked;
    }

    public function getLateRegistrationFeeAmount()
    {
        if ($this->late_registration_fee > 0) {
            return $this->late_registration_fee;
        }
        return 0;
    }

    public function getLatePaymentFeeAmount($balance)
    {
        if ($this->late_payment_percentage > 0) {
            return ($balance * $this->late_payment_percentage) / 100;
        }
        return $this->late_payment_fee;
    }
}
