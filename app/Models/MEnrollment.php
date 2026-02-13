<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MEnrollment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'm_enrollments';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'enrollment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'course_id',
        'mobile_school_id',
        'enrollment_code',
        'enrollment_date',
        'start_date',
        'end_date',
        'completion_date',
        'status',
        'progress',
        'current_semester',
        'total_fees',
        'paid_amount',
        'payment_status',
        'academic_year',
        'semester',
        'batch',
        'certificate_number',
        'certificate_issue_date',
        'certificate_file_path',
        'remarks',
        'is_active',
        'created_by',
        'updated_by',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'completion_date' => 'date',
        'certificate_issue_date' => 'date',
        'progress' => 'decimal:2',
        'total_fees' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'current_semester' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the student that owns the enrollment.
     */
    public function student()
    {
        return $this->belongsTo(MStudent::class, 'student_id', 'student_id');
    }

    /**
     * Get the course that owns the enrollment.
     */
    public function course()
    {
        return $this->belongsTo(MCourse::class, 'course_id', 'course_id');
    }

    /**
     * Get the mobile school that owns the enrollment.
     */
    public function mobileSchool()
    {
        return $this->belongsTo(MobileSchool::class, 'mobile_school_id');
    }
// Add to MEnrollment model, after the other relationships:
public function certificates()
{
    return $this->hasMany(MCertificate::class, 'enrollment_id', 'enrollment_id');
}
    /**
     * Get the user who created this enrollment.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this enrollment.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for active enrollments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for enrollments by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Calculate remaining fees.
     */
    public function getRemainingFeesAttribute()
    {
        return $this->total_fees - $this->paid_amount;
    }

    /**
     * Check if enrollment is completed.
     */
    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if certificate is issued.
     */
    public function getHasCertificateAttribute()
    {
        return !empty($this->certificate_number);
    }
}
