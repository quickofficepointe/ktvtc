<?php
// app/Models/CourseIntakes.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CourseIntakes extends Model
{
    use HasFactory;

    protected $table = 'course_intakes';

    protected $fillable = [
        'course_id',
        'month',
        'year',
        'application_deadline',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'application_deadline' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the course that owns the intake.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Scope to get all intakes (no filtering)
     */
    public function scopeAllIntakes($query)
    {
        return $query;
    }

    /**
     * Scope to get only future intakes
     */
    public function scopeAvailable($query)
    {
        return $query->where(function($q) {
            $q->whereNull('application_deadline')
              ->orWhere('application_deadline', '>=', Carbon::now());
        });
    }

    /**
     * Get formatted display string
     */
    public function getDisplayAttribute()
    {
        return $this->month . ' ' . $this->year;
    }

    /**
     * Check if intake is in the past
     */
    public function getIsPastAttribute()
    {
        if (!$this->application_deadline) {
            return false;
        }
        return $this->application_deadline->isPast();
    }

    /**
     * Check if intake is in the future
     */
    public function getIsFutureAttribute()
    {
        if (!$this->application_deadline) {
            return true;
        }
        return $this->application_deadline->isFuture();
    }

    /**
     * Get status label HTML
     */
    public function getStatusLabelAttribute()
    {
        if ($this->is_past) {
            return '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">Past Intake</span>';
        }
        if ($this->is_future) {
            return '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Open</span>';
        }
        return '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Closing Soon</span>';
    }

    /**
     * Get plain status text
     */
    public function getStatusTextAttribute()
    {
        if ($this->is_past) {
            return 'Past';
        }
        if ($this->is_future) {
            return 'Open';
        }
        return 'Closing Soon';
    }

    /**
     * Check if intake is open for applications
     */
    public function getIsOpenAttribute()
    {
        return !$this->is_past;
    }
}
