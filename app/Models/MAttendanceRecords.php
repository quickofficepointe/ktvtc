<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MAttendanceRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'm_attendance_records';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'record_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'student_id',
        'trainer_id',
        'status',
        'check_in_time',
        'check_out_time',
        'duration_minutes',
        'is_late',
        'late_minutes',
        'late_reason',
        'leave_application_id',
        'absence_reason',
        'verification_method',
        'verified_by',
        'device_id',
        'location_coordinates',
        'signature_image',
        'photo_evidence',
        'verification_data',
        'marks_awarded',
        'performance_notes',
        'is_verified',
        'needs_review',
        'review_notes',
        'recorded_by',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'duration_minutes' => 'integer',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
        'marks_awarded' => 'decimal:2',
        'is_verified' => 'boolean',
        'needs_review' => 'boolean',
        'verification_data' => 'array',
        'location_coordinates' => 'array',
    ];

    /**
     * Get the attendance session that owns the record.
     */
    public function attendance()
    {
        return $this->belongsTo(MAttendance::class, 'attendance_id', 'attendance_id');
    }

    /**
     * Get the student that owns the attendance record.
     */
    public function student()
    {
        return $this->belongsTo(MStudent::class, 'student_id', 'student_id');
    }

    /**
     * Get the trainer that owns the attendance record.
     */
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    /**
     * Get the user who recorded this attendance.
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the user who verified this attendance.
     */
    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the leave application associated with this record.
     */
    public function leaveApplication()
    {
        return $this->belongsTo(LeaveApplication::class, 'leave_application_id');
    }

    /**
     * Scope for present records.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope for absent records.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope for late records.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope for student records.
     */
    public function scopeStudents($query)
    {
        return $query->whereNotNull('student_id');
    }

    /**
     * Scope for trainer records.
     */
    public function scopeTrainers($query)
    {
        return $query->whereNotNull('trainer_id');
    }

    /**
     * Scope for verified records.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for records needing review.
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('needs_review', true);
    }

    /**
     * Check if record is for a student.
     */
    public function isStudentRecord()
    {
        return !is_null($this->student_id);
    }

    /**
     * Check if record is for a trainer.
     */
    public function isTrainerRecord()
    {
        return !is_null($this->trainer_id);
    }

    /**
     * Calculate duration automatically from check-in and check-out times.
     */
    public function calculateDuration()
    {
        if ($this->check_in_time && $this->check_out_time) {
            $checkIn = \Carbon\Carbon::parse($this->check_in_time);
            $checkOut = \Carbon\Carbon::parse($this->check_out_time);
            $this->duration_minutes = $checkOut->diffInMinutes($checkIn);
        }
        return $this;
    }

    /**
     * Check if the attendance was late based on attendance session settings.
     */
    public function checkLateStatus()
    {
        if ($this->check_in_time && $this->attendance->start_time && $this->attendance->late_threshold_minutes) {
            $sessionStart = \Carbon\Carbon::parse($this->attendance->attendance_date->format('Y-m-d') . ' ' . $this->attendance->start_time);
            $checkInTime = \Carbon\Carbon::parse($this->check_in_time);

            $lateMinutes = $checkInTime->diffInMinutes($sessionStart, false);

            if ($lateMinutes > $this->attendance->late_threshold_minutes) {
                $this->is_late = true;
                $this->late_minutes = $lateMinutes;
                $this->status = 'late';
            } else {
                $this->is_late = false;
                $this->late_minutes = 0;
                if ($this->status === 'late') {
                    $this->status = 'present';
                }
            }
        }
        return $this;
    }

    /**
     * Get the attendee name (student or trainer).
     */
    public function getAttendeeNameAttribute()
    {
        if ($this->isStudentRecord()) {
            return $this->student->full_name ?? 'Unknown Student';
        } elseif ($this->isTrainerRecord()) {
            return $this->trainer->name ?? 'Unknown Trainer';
        }
        return 'Unknown Attendee';
    }

    /**
     * Get the attendee type.
     */
    public function getAttendeeTypeAttribute()
    {
        if ($this->isStudentRecord()) {
            return 'student';
        } elseif ($this->isTrainerRecord()) {
            return 'trainer';
        }
        return 'unknown';
    }

    /**
     * Mark record as verified.
     */
    public function markAsVerified($verifiedBy = null)
    {
        $this->is_verified = true;
        $this->verified_by = $verifiedBy;
        $this->needs_review = false;
        $this->save();
    }

    /**
     * Mark record as needing review.
     */
    public function markForReview($notes = null)
    {
        $this->needs_review = true;
        $this->review_notes = $notes;
        $this->save();
    }

    /**
     * Check if record can be edited (based on attendance session lock).
     */
    public function getCanEditAttribute()
    {
        return !$this->attendance->is_locked;
    }

    /**
     * Get status with color for UI display.
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'leave' => 'info',
            'half_day' => 'primary',
            'excused' => 'secondary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}
