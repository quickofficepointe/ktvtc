<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MExam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_exams';
    protected $primaryKey = 'exam_id';

    protected $fillable = [
        'exam_name', 'exam_code', 'description', 'subject_id', 'course_id',
        'exam_type', 'exam_category', 'exam_date', 'start_time', 'end_time',
        'duration_minutes', 'total_marks', 'passing_marks', 'weightage',
        'number_of_questions', 'question_types', 'sections', 'is_published',
        'is_active', 'allow_retake', 'max_attempts', 'venue', 'instructions',
        'materials_allowed', 'academic_year', 'semester', 'term', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'exam_date' => 'date',
        'total_marks' => 'decimal:2',
        'passing_marks' => 'decimal:2',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'allow_retake' => 'boolean',
        'question_types' => 'array',
        'sections' => 'array',
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(MSubject::class, 'subject_id', 'subject_id');
    }

    public function course()
    {
        return $this->belongsTo(MCourse::class, 'course_id', 'course_id');
    }

    public function results()
    {
        return $this->hasMany(MExamResult::class, 'exam_id', 'exam_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeType($query, $type)
    {
        return $query->where('exam_type', $type);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function isCAT1()
    {
        return $this->exam_type === 'cat1';
    }

    public function isMainExam()
    {
        return $this->exam_type === 'main_exam';
    }
}
