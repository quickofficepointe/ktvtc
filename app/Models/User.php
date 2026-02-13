<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'bio',
        'profile_picture',
        'status',
        'campus_id',    // Add this if you have campus_id
        'shop_id',      // Add this if you have shop_id
        'is_active',    // Add this if you have is_active
        'is_approved',  // Add this if you have is_approved
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['role_name', 'role_badge', 'profile_picture_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',    // Add this
            'is_approved' => 'boolean',  // Add this
        ];
    }

    public function accessibleShopIds()
    {
        // Admin users (role 2) can access all shops
        if ($this->role == 6) {
            return Shop::pluck('id')->toArray();
        }

        // For cafeteria users (role 6) and other roles, return their assigned shop
        return [$this->shop_id ?? 1];
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Accessor for role badge Tailwind classes
     */
    public function getRoleBadgeAttribute()
    {
        return match($this->role) {
            0 => 'bg-red-100 text-red-800',      // Super Admin (ADD THIS)
            1 => 'bg-purple-100 text-purple-800', // Main School
            2 => 'bg-blue-100 text-blue-800',    // Admin
            3 => 'bg-green-100 text-green-800',  // Scholarship
            4 => 'bg-indigo-100 text-indigo-800', // Library
            5 => 'bg-gray-100 text-gray-800',    // Student
            6 => 'bg-yellow-100 text-yellow-800', // Cafeteria
            7 => 'bg-pink-100 text-pink-800',    // Finance
            8 => 'bg-teal-100 text-teal-800',    // Trainers
            9 => 'bg-orange-100 text-orange-800', // Website
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Accessor for role name
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
            0 => 'Super Admin',     // ADD THIS
            1 => 'Main School',
            2 => 'Admin',
            3 => 'Scholarship',
            4 => 'Library',
            5 => 'Student',
            6 => 'Cafeteria',
            7 => 'Finance',
            8 => 'Trainers',
            9 => 'Website',
            default => 'Unknown'
        };
    }

    /**
     * Accessor for profile picture URL
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }

        // Return default avatar
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Accessor for status badge color
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'suspended' => 'danger',
            'pending' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->role == 0; // Assuming 0 is super admin role
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role == 2; // Assuming 2 is admin role
    }

    /**
     * Check if user can be impersonated
     */
    public function canBeImpersonated()
    {
        // Don't allow impersonating super admin
        return !$this->isSuperAdmin();
    }

    public function scopeStudents($query)
    {
        return $query->where('role', 5);
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->status === 'active' || $this->is_active === true;
    }
}
