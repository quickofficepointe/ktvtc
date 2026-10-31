<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One student's evaluation of one unit's trainer in one term.
 * student_id exists only to track completion; never display it next to answers.
 */
class TrainerEvaluation extends Model
{
    protected $fillable = ['term_registration_id', 'course_unit_id', 'trainer_id', 'student_id', 'term_number'];

    protected $hidden = ['student_id', 'term_registration_id'];

    public function answers()
    {
        return $this->hasMany(TrainerEvaluationAnswer::class);
    }

    public function unit()
    {
        return $this->belongsTo(CourseUnit::class, 'course_unit_id');
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }
}
