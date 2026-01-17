<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'slug',
        'code',
        'duration',
        'total_hours',
        'schedule',
        'description',
        'requirements',
        'fees_breakdown',
        'delivery_mode',
        'what_you_will_learn',
        'cover_image',
        'level',
        'featured',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // FIXED: Remove the hardcoded where clause
    public function intakes()
    {
        return $this->hasMany(CourseIntakes::class);
    }

    /**
     * Get the cover image URL
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }

        return null;
    }

    /**
     * Scope active courses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope featured courses
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        return $this->duration ?: 'Not specified';
    }

    /**
     * Get formatted level
     */
    public function getFormattedLevelAttribute()
    {
        return ucfirst($this->level);
    }

    /**
     * Get active intakes (convenience method)
     */
    public function activeIntakes()
    {
        return $this->intakes()->active();
    }
}
