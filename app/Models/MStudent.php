<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MStudent extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'm_students';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'student_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'mobile_school_id',
        'student_code',
        'enrollment_date',
        'is_active',
        'profile_image',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'guardian_address',
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
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the mobile school that the student belongs to.
     */
    public function mobileSchool()
    {
        return $this->belongsTo(MobileSchool::class, 'mobile_school_id');
    }

    /**
     * Get the user who created this student record.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this student record.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
// Add to MStudent model
public function enrollments()
{
    return $this->hasMany(MEnrollment::class, 'student_id', 'student_id');
}

public function activeEnrollments()
{
    return $this->enrollments()->where('status', 'active');
}
    /**
     * Accessor for full name.
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
