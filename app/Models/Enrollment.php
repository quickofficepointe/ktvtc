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

        // DENORMALIZED STUDENT DATA (for speed)
        'student_name',
        'student_number',

        // COURSE INFO
        'course_name',
        'course_code',

        // FINANCIAL - THE ONLY NUMBERS YOU NEED
        'total_fees',
        'amount_paid',

        // DATES
        'intake_year',
        'intake_month', // 'January', 'May', 'September'
        'enrollment_date',
        'start_date',
        'expected_end_date',
        'actual_end_date',

        // STATUS
        'status', // 'active', 'graduated', 'dropped', 'completed', 'pending'

        // STUDY INFO
        'study_mode', // 'full_time', 'part_time', 'evening', 'weekend', 'online'
        'student_type', // 'new', 'continuing', 'alumnus', 'transfer'
        'sponsorship_type', // 'self', 'sponsored', 'government', 'scholarship', 'company'

        // EXTERNAL EXAM (OPTIONAL)
        'requires_external_exam',
        'exam_body', // 'KNEC', 'NITA', 'CDACC'

        // IMPORT TRACKING
        'legacy_code', // Original 'HDBT/021/2021'
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
}
