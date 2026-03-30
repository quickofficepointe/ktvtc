<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExamRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'enrollment_id',

        // EXAM DETAILS (NO SEPARATE TABLES NEEDED)
        'exam_body', // 'KNEC', 'NITA', 'CDACC', 'TVETA', 'OTHER'
        'exam_type', // 'Certificate', 'Diploma', 'Artisan'
        'exam_code', // Optional code

        // REGISTRATION DETAILS
        'registration_number',
        'index_number',
        'registration_date',
        'exam_date',

        // RESULTS
        'result_date',
        'result', // 'Pass', 'Fail', 'Distinction'
        'grade', // 'A', 'B', 'C'
        'score',

        // CERTIFICATE
        'certificate_number',
        'certificate_issue_date',
        'certificate_path',

        // STATUS
        'status', // 'pending', 'registered', 'active', 'completed', 'failed'

        // METADATA
        'remarks',
        'registered_by',
        'verified_at',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'exam_date' => 'date',
        'result_date' => 'date',
        'certificate_issue_date' => 'date',
        'verified_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    /**
     * ============ RELATIONSHIPS ============
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function registrar()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * ============ ACCESSORS ============
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'registered' => 'green',
            'active' => 'blue',
            'completed' => 'purple',
            'failed' => 'red',
            default => 'gray'
        };
    }

    public function getExamBodyColorAttribute()
    {
        return match($this->exam_body) {
            'KNEC' => 'blue',
            'NITA' => 'green',
            'CDACC' => 'purple',
            'TVETA' => 'orange',
            default => 'gray'
        };
    }

    public function getIsPassedAttribute()
    {
        return $this->result === 'Pass' ||
               $this->result === 'Distinction' ||
               $this->grade === 'A' ||
               $this->grade === 'B' ||
               ($this->score && $this->score >= 50);
    }

    /**
     * ============ SCOPES ============
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByExamBody($query, $examBody)
    {
        return $query->where('exam_body', $examBody);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('exam_date', '>=', now())
                     ->whereIn('status', ['registered', 'active']);
    }
}
