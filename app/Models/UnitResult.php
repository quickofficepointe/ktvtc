<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A student's result in one unit, one attempt (attempt 2+ are re-sits).
 * draft -> submitted (by trainer) -> published (by academic admin; students see it).
 */
class UnitResult extends Model
{
    public const DRAFT     = 'draft';
    public const SUBMITTED = 'submitted';
    public const PUBLISHED = 'published';

    protected $fillable = [
        'course_unit_id', 'term_registration_id', 'enrollment_id', 'student_id', 'attempt', 'trainer_id',
        'score_a', 'score_b', 'total', 'grade', 'passed', 'status', 'remarks', 'returned_reason',
        'entered_by', 'submitted_at', 'published_by', 'published_at',
    ];

    protected $casts = [
        'score_a'      => 'decimal:2',
        'score_b'      => 'decimal:2',
        'total'        => 'decimal:2',
        'passed'       => 'boolean',
        'submitted_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(CourseUnit::class, 'course_unit_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function termRegistration()
    {
        return $this->belongsTo(TermRegistration::class);
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::PUBLISHED);
    }

    public function isEditableByTrainer(): bool
    {
        return $this->status === self::DRAFT;
    }
}
