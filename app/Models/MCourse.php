<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MCourse extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'm_courses';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'course_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_name',
        'course_description',
        'course_code',
        'duration',
        'price',
        'is_active',
        'image_url',
        'category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get the category that owns the course.
     */
    public function category()
    {
        return $this->belongsTo(MCourseCategories::class, 'category_id', 'category_id');
    }
// Add to MCourse model
public function enrollments()
{
    return $this->hasMany(MEnrollment::class, 'course_id', 'course_id');
}

public function activeEnrollments()
{
    return $this->enrollments()->where('status', 'active');
}
    /**
     * Many-to-many relationship with subjects through pivot table.
     */
    public function subjects()
    {
        return $this->belongsToMany(MSubject::class, 'm_course_subjects', 'course_id', 'subject_id')
                    ->withPivot('semester', 'year', 'is_compulsory', 'credit_hours', 'sort_order')
                    ->withTimestamps();
    }
}
