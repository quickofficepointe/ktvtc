<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // This should be role_id if you're using foreign key
        'phone_number',
        'bio',
        'profile_picture',
        'status',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor for role badge color
     */
    public function getRoleBadgeAttribute()
    {
        return match($this->role) {
            1 => 'primary',    // Main School
            2 => 'danger',     // Admin
            3 => 'info',       // Scholarship
            4 => 'warning',    // Library
            5 => 'success',    // Student
            6 => 'secondary',  // Cafeteria
            7 => 'dark',       // Finance
            8 => 'info',       // Trainers
            9 => 'primary',    // Website
            default => 'secondary'
        };
    }

    /**
     * Accessor for role name
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
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
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role == 2; // Assuming 2 is admin role
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }
}
