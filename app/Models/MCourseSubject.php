<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MCourseSubject extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'm_course_subjects';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'course_subject_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'subject_id',
        'semester',
        'year',
        'is_compulsory',
        'credit_hours',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_compulsory' => 'boolean',
        'credit_hours' => 'integer',
        'semester' => 'integer',
        'year' => 'integer',
        'sort_order' => 'integer',
    ];
}
