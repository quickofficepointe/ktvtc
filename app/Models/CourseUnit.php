<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A unit (CBET unit of competency, or college module) taught in term N of a course.
 */
class CourseUnit extends Model
{
    public const TYPES = ['basic' => 'Basic', 'common' => 'Common', 'core' => 'Core', 'other' => 'Other'];

    protected $fillable = ['course_id', 'term_number', 'code', 'title', 'unit_type', 'trainer_id', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function results()
    {
        return $this->hasMany(UnitResult::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getLabelAttribute(): string
    {
        return trim(($this->code ? $this->code . ' ' : '') . $this->title);
    }
}
