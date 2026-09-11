<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 0;
    public const ROLE_MSCHOOL     = 1;
    public const ROLE_ADMIN       = 2;
    public const ROLE_SCHOLARSHIP = 3;
    public const ROLE_LIBRARY     = 4;
    public const ROLE_STUDENT     = 5;
    public const ROLE_CAFETERIA   = 6;
    public const ROLE_FINANCE     = 7;
    public const ROLE_TRAINERS    = 8;
    public const ROLE_WEBSITE     = 9;
    public const ROLE_ILO         = 10;

    public const ROLE_APPROVAL_NONE     = 'none';
    public const ROLE_APPROVAL_PENDING  = 'pending';
    public const ROLE_APPROVAL_APPROVED = 'approved';
    public const ROLE_APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'password',
        'avatar',
        'profile_picture',
        'bio',
        'role',
        'is_verified',
        'is_active',
        'is_approved',
        'requested_role',
        'role_approval_status',
        'role_approval_notes',
        'role_approved_at',
        'role_approved_by',
        'preferences',
        'last_login_at',
        'last_login_ip',
        'student_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'phone_verified_at'  => 'datetime',
            'last_login_at'      => 'datetime',
            'role_approved_at'   => 'datetime',
            'password'           => 'hashed',
            'is_verified'        => 'boolean',
            'is_active'          => 'boolean',
            'is_approved'        => 'boolean',
            'preferences'        => 'array',
        ];
    }

    // ============================================================
    // ROLE HELPERS
    // ============================================================

    public static function getRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_MSCHOOL     => 'Main School',
            self::ROLE_ADMIN       => 'Admin',
            self::ROLE_SCHOLARSHIP => 'Scholarship',
            self::ROLE_LIBRARY     => 'Library',
            self::ROLE_STUDENT     => 'Student',
            self::ROLE_CAFETERIA   => 'Cafeteria',
            self::ROLE_FINANCE     => 'Finance',
            self::ROLE_TRAINERS    => 'Trainers',
            self::ROLE_WEBSITE     => 'Website',
            self::ROLE_ILO         => 'ILO',
        ];
    }

    public static function getRoleApprovalStatuses(): array
    {
        return [
            self::ROLE_APPROVAL_NONE     => 'None',
            self::ROLE_APPROVAL_PENDING  => 'Pending Approval',
            self::ROLE_APPROVAL_APPROVED => 'Approved',
            self::ROLE_APPROVAL_REJECTED => 'Rejected',
        ];
    }

    public function getRoleNameAttribute(): string
    {
        return self::getRoles()[$this->role] ?? 'Unknown';
    }

    public function hasRole(int $role): bool
    {
        return (int) $this->role === $role;
    }

    public function isSuperAdmin(): bool
    {
        return (int) $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array((int) $this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN], true);
    }

    public function isMschool(): bool
    {
        return (int) $this->role === self::ROLE_MSCHOOL;
    }

    public function isScholarship(): bool
    {
        return (int) $this->role === self::ROLE_SCHOLARSHIP;
    }

    public function isLibrary(): bool
    {
        return (int) $this->role === self::ROLE_LIBRARY;
    }

    public function isStudent(): bool
    {
        return (int) $this->role === self::ROLE_STUDENT;
    }

    public function isCafeteria(): bool
    {
        return (int) $this->role === self::ROLE_CAFETERIA;
    }

    public function isFinance(): bool
    {
        return (int) $this->role === self::ROLE_FINANCE;
    }

    public function isTrainers(): bool
    {
        return (int) $this->role === self::ROLE_TRAINERS;
    }

    /**
     * Alias for isTrainers() — singular reads more naturally in controllers.
     */
    public function isTrainer(): bool
    {
        return $this->isTrainers();
    }

    public function isWebsite(): bool
    {
        return (int) $this->role === self::ROLE_WEBSITE;
    }

    public function isIlo(): bool
    {
        return (int) $this->role === self::ROLE_ILO;
    }

    // ============================================================
    // ROLE APPROVAL WORKFLOW
    // ============================================================

    public function hasPendingRoleRequest(): bool
    {
        return $this->role_approval_status === self::ROLE_APPROVAL_PENDING;
    }

    public function approveRoleChange(int $approvedBy): void
    {
        if ($this->requested_role) {
            $this->role = $this->requested_role;
            $this->requested_role = null;
        }
        $this->role_approval_status = self::ROLE_APPROVAL_APPROVED;
        $this->role_approved_at     = now();
        $this->role_approved_by     = $approvedBy;
        $this->save();
    }

    public function rejectRoleChange(string $reason, int $rejectedBy): void
    {
        $this->requested_role       = null;
        $this->role_approval_status = self::ROLE_APPROVAL_REJECTED;
        $this->role_approval_notes  = $reason;
        $this->role_approved_by     = $rejectedBy;
        $this->role_approved_at     = now();
        $this->save();
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * The student record linked to this user account.
     * One user → one student (users.student_id → students.id).
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function approvedRoleRequests(): HasMany
    {
        return $this->hasMany(User::class, 'role_approved_by');
    }

    /**
     * Courses this user teaches (pivot: course_trainer).
     */
    public function coursesAsTrainer()
    {
        return $this->belongsToMany(Course::class, 'course_trainer')
                    ->withPivot('role', 'is_active', 'assigned_by', 'assigned_at', 'notes')
                    ->withTimestamps();
    }

    public function activeCoursesAsTrainer()
    {
        return $this->coursesAsTrainer()->wherePivot('is_active', true);
    }

    // ============================================================
    // TRAINER HELPERS
    // ============================================================

    public function teachesCourse(int $courseId): bool
    {
        return $this->activeCoursesAsTrainer()
                    ->where('courses.id', $courseId)
                    ->exists();
    }

    public function trainerCourseIds(): array
    {
        return $this->activeCoursesAsTrainer()
                    ->pluck('courses.id')
                    ->all();
    }

    public function teachesAnyOf(array $courseIds): bool
    {
        if (empty($courseIds)) {
            return false;
        }

        return $this->activeCoursesAsTrainer()
                    ->whereIn('courses.id', $courseIds)
                    ->exists();
    }

    // ============================================================
    // AVATAR HELPER
    // ============================================================

    public function getAvatarUrlAttribute(): string
    {
        $path = $this->profile_picture ?? $this->avatar ?? null;

        if ($path) {
            return asset('storage/' . $path);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'User')
             . '&background=B91C1C&color=fff';
    }
}