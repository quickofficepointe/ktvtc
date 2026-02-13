<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MSubject extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'm_subjects';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'subject_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_name',
        'subject_code',
        'description',
        'course_id',
        'credit_hours',
        'duration_weeks',
        'price',
        'sort_order',
        'is_active',
        'is_core',
        'prerequisite_subject_id',
        'syllabus_file',
        'cover_image',
        'exam_weight',
        'assignment_weight',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_hours' => 'integer',
        'duration_weeks' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'exam_weight' => 'integer',
        'assignment_weight' => 'integer',
    ];

    /**
     * Get the course that this subject belongs to.
     */
    public function course()
    {
        return $this->belongsTo(MCourse::class, 'course_id', 'course_id');
    }

    /**
     * Many-to-many relationship with courses through pivot table.
     */
    public function courses()
    {
        return $this->belongsToMany(MCourse::class, 'm_course_subjects', 'subject_id', 'course_id')
                    ->withPivot('semester', 'year', 'is_compulsory', 'credit_hours', 'sort_order')
                    ->withTimestamps();
    }

    /**
     * Get the prerequisite subject.
     */
    public function prerequisite()
    {
        return $this->belongsTo(MSubject::class, 'prerequisite_subject_id', 'subject_id');
    }

    /**
     * Get subjects that require this subject as prerequisite.
     */
    public function requiredBy()
    {
        return $this->hasMany(MSubject::class, 'prerequisite_subject_id', 'subject_id');
    }

    /**
     * Scope for active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for core subjects.
     */
    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    /**
     * Scope for elective subjects.
     */
    public function scopeElective($query)
    {
        return $query->where('is_core', false);
    }

    /**
     * Scope for subjects by course.
     */
    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Accessor for total weight (should always be 100).
     */
    public function getTotalWeightAttribute()
    {
        return $this->exam_weight + $this->assignment_weight;
    }
}
