<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MExamResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_exam_results';
    protected $primaryKey = 'result_id';

    protected $fillable = [
        'exam_id', 'student_id', 'enrollment_id', 'marks_obtained', 'total_marks',
        'percentage', 'grade', 'grade_point', 'remarks', 'status', 'attempt_number',
        'attempt_date', 'time_taken_minutes', 'section_marks', 'question_wise_marks',
        'graded_by', 'graded_at', 'grading_notes', 'is_absent', 'is_retake',
        'is_supplementary', 'absent_reason', 'class_rank', 'total_students',
        'class_average', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'class_average' => 'decimal:2',
        'is_absent' => 'boolean',
        'is_retake' => 'boolean',
        'is_supplementary' => 'boolean',
        'attempt_date' => 'datetime',
        'graded_at' => 'datetime',
        'section_marks' => 'array',
        'question_wise_marks' => 'array',
    ];

    // Relationships
    public function exam()
    {
        return $this->belongsTo(MExam::class, 'exam_id', 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(MStudent::class, 'student_id', 'student_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(MEnrollment::class, 'enrollment_id', 'enrollment_id');
    }

    // Scopes
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeAbsent($query)
    {
        return $query->where('is_absent', true);
    }

    // Helpers
    public function isPassed()
    {
        $passingMarks = $this->exam->passing_marks ?? ($this->total_marks * 0.4);
        return $this->marks_obtained >= $passingMarks;
    }

    public function getGradeColor()
    {
        $gradeColors = [
            'A' => 'success', 'B' => 'primary', 'C' => 'warning',
            'D' => 'orange', 'F' => 'danger'
        ];
        return $gradeColors[$this->grade] ?? 'secondary';
    }
}
