<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_attendances';
    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'attendance_date', 'start_time', 'end_time', 'session_name',
        'attendable_type', 'attendable_id', 'subject_id', 'course_id',
        'mobile_school_id', 'venue', 'room', 'latitude', 'longitude',
        'recording_method', 'qr_code_data', 'is_geofenced', 'is_active',
        'is_locked', 'allow_late_marking', 'late_threshold_minutes',
        'total_expected', 'total_present', 'total_absent', 'total_late',
        'total_leave', 'topic_covered', 'remarks', 'metadata',
        'created_by', 'updated_by', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'is_geofenced' => 'boolean',
        'allow_late_marking' => 'boolean',
        'metadata' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Polymorphic relationship
    public function attendable()
    {
        return $this->morphTo();
    }

    // Relationships
    public function subject()
    {
        return $this->belongsTo(MSubject::class, 'subject_id', 'subject_id');
    }

    public function course()
    {
        return $this->belongsTo(MCourse::class, 'course_id', 'course_id');
    }

    public function mobileSchool()
    {
        return $this->belongsTo(MobileSchool::class, 'mobile_school_id');
    }

    public function records()
    {
        return $this->hasMany(MAttendanceRecord::class, 'attendance_id', 'attendance_id');
    }

    public function studentRecords()
    {
        return $this->records()->whereNotNull('student_id');
    }

    public function trainerRecords()
    {
        return $this->records()->whereNotNull('trainer_id');
    }

    // Scopes
    public function scopeDate($query, $date)
    {
        return $query->where('attendance_date', $date);
    }

    public function scopeType($query, $type)
    {
        return $query->where('attendable_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function isForWorkshop()
    {
        return $this->attendable_type === 'workshop';
    }

    public function isForExam()
    {
        return $this->attendable_type === 'exam';
    }

    public function calculateStatistics()
    {
        $this->total_present = $this->records()->where('status', 'present')->count();
        $this->total_absent = $this->records()->where('status', 'absent')->count();
        $this->total_late = $this->records()->where('status', 'late')->count();
        $this->total_leave = $this->records()->where('status', 'leave')->count();
        $this->save();
    }

    public function getAttendancePercentage()
    {
        if ($this->total_expected === 0) return 0;
        return ($this->total_present / $this->total_expected) * 100;
    }
}
