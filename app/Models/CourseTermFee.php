<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fee breakdown for term N of a course, for students whose intake is on or
 * after effective_from. A new version is added when fees change, so students
 * already admitted keep the fees they joined on.
 */
class CourseTermFee extends Model
{
    protected $fillable = ['course_id', 'term_number', 'effective_from', 'items', 'total', 'notes', 'created_by'];

    protected $casts = [
        'effective_from' => 'date',
        'items'          => 'array',
        'total'          => 'decimal:2',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(TermRegistration::class);
    }
}
