<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'course_id',
        'campus_id',
        'department_id',
        'academic_term_id',
        'enrollment_number',
        'legacy_enrollment_code',
        'intake_period',
        'intake_year',
        'study_mode',
        'student_type',
        'sponsorship_type',
        'expected_duration_months',
        'number_of_terms',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'completion_percentage',
        'total_course_fee',
        'amount_paid',
        'balance',
        'fee_structure_type',
        'fee_template_id',
        'requires_external_exam',
        'external_exam_body',
        'exam_registration_number',
        'exam_registration_date',
        'final_grade',
        'certificate_number',
        'certificate_issue_date',
        'class_award',
        'remarks',
        'is_active',
        'import_batch',
        'requires_fee_import',
        'enrollment_date',
        'deferred_to_period',
        'deferred_to_year',
        'defer_reason',
        'deferred_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'exam_registration_date' => 'date',
        'certificate_issue_date' => 'date',
        'enrollment_date' => 'date',
        'deferred_at' => 'datetime',
        'total_course_fee' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'requires_external_exam' => 'boolean',
        'is_active' => 'boolean',
        'requires_fee_import' => 'boolean',
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function feeTemplate()
    {
        return $this->belongsTo(CourseFeeTemplate::class, 'fee_template_id');
    }

    public function feeItems()
    {
        return $this->hasMany(EnrollmentFeeItem::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * ============ SCOPES ============
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('intake_year', date('Y'));
    }

    public function scopeByIntake($query, $year, $period = null)
    {
        $query = $query->where('intake_year', $year);
        if ($period) {
            $query->where('intake_period', $period);
        }
        return $query;
    }

    public function scopeRequiresExamRegistration($query)
    {
        return $query->where('requires_external_exam', true)
            ->whereNull('exam_registration_number');
    }

    public function scopeHasOutstandingBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    /**
     * ============ ACCESSORS ============
     */
    public function getFullNameAttribute()
    {
        return $this->student->full_name ?? 'N/A';
    }

    public function getCourseNameAttribute()
    {
        return $this->course->name ?? 'N/A';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'registered' => 'blue',
            'in_progress' => 'green',
            'completed' => 'purple',
            'dropped' => 'red',
            'discontinued' => 'red',
            'suspended' => 'yellow',
            'deferred' => 'orange',
            'transferred' => 'gray',
            default => 'gray'
        };
    }

    public function getProgressPercentageAttribute()
    {
        return $this->completion_percentage . '%';
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->balance <= 0) {
            return ['label' => 'Paid', 'color' => 'green'];
        } elseif ($this->amount_paid > 0) {
            return ['label' => 'Partial', 'color' => 'yellow'];
        } else {
            return ['label' => 'Unpaid', 'color' => 'red'];
        }
    }

    public function getExamBodyLabelAttribute()
    {
        return match($this->external_exam_body) {
            'nita' => 'NITA',
            'cdacc' => 'CDACC',
            'knec' => 'KNEC',
            default => $this->external_exam_body ? strtoupper($this->external_exam_body) : 'N/A'
        };
    }

    /**
     * ============ MUTATORS ============
     */
    public function setEnrollmentNumberAttribute($value)
    {
        $this->attributes['enrollment_number'] = $value;
    }

    /**
     * ============ METHODS ============
     */
    public function calculateBalance()
    {
        $this->balance = $this->total_course_fee - $this->amount_paid;
        return $this->balance;
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress' && $this->is_active;
    }

    public function isDeferred()
    {
        return $this->status === 'deferred';
    }

    public function requiresExamRegistration()
    {
        return $this->requires_external_exam && empty($this->exam_registration_number);
    }

    public function hasCertificate()
    {
        return !empty($this->certificate_number);
    }
public function calculateBalanceFromItems()
{
    $total = $this->feeItems()->sum('total_amount');
    $paid = $this->feeItems()->sum('amount_paid');
    $this->total_course_fee = $total;
    $this->amount_paid = $paid;
    $this->balance = $total - $paid;
    $this->save();

    return $this->balance;
}
    public function getDurationInMonths()
    {
        if ($this->expected_duration_months) {
            return $this->expected_duration_months;
        }
        if ($this->start_date && $this->expected_end_date) {
            return $this->start_date->diffInMonths($this->expected_end_date);
        }
        if ($this->number_of_terms) {
            return $this->number_of_terms * 3; // Assuming 3 months per term
        }
        return null;
    }

    public function getDeferredInfo()
    {
        if ($this->status === 'deferred' && $this->deferred_to_period && $this->deferred_to_year) {
            return "{$this->deferred_to_period} {$this->deferred_to_year}";
        }
        return null;
    }
}
