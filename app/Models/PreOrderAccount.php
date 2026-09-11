<?php
// app/Models/PreOrderAccount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PreOrderAccount extends Model
{
    protected $table = 'pre_order_accounts';

    protected $fillable = [
        'user_type',
        'user_id',
        'identifier',
        'display_name',
        'pin_hash',
        'phone',
        'pin_set_at',
        'last_login_at',
        'reset_code_hash',
        'reset_code_expires_at',
        'reset_attempts',
        'verification_code_hash',
        'verification_code_expires_at',
        'is_active',
        'is_verified',
    ];

    protected $casts = [
        'pin_set_at' => 'datetime',
        'last_login_at' => 'datetime',
        'reset_code_expires_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'reset_attempts' => 'integer',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    protected $hidden = [
        'pin_hash',
        'reset_code_hash',
        'verification_code_hash',
    ];

    // ──────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(PreOrder::class);
    }

    /**
     * Get the underlying user/student this account belongs to.
     * Returns null if the source record was deleted.
     */
    public function owner()
    {
        return match ($this->user_type) {
            'staff' => User::find($this->user_id),
            'college_student' => Student::find($this->user_id),
            'high_school_student' => HighSchoolStudent::find($this->user_id),
            default => null,
        };
    }

    /**
     * Get the CardAccount for high school students (null for others).
     */
    public function cardAccount(): ?CardAccount
    {
        if ($this->user_type !== 'high_school_student') {
            return null;
        }

        return CardAccount::where('high_school_student_id', $this->user_id)->first();
    }

    // ──────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────

    public function isStaff(): bool
    {
        return $this->user_type === 'staff';
    }

    public function isCollegeStudent(): bool
    {
        return $this->user_type === 'college_student';
    }

    public function isHighSchoolStudent(): bool
    {
        return $this->user_type === 'high_school_student';
    }

    /**
     * True if this account uses M-Pesa STK push at checkout.
     * Staff and college students pay via M-Pesa.
     */
    public function paysViaMpesa(): bool
    {
        return in_array($this->user_type, ['staff', 'college_student']);
    }

    /**
     * True if this account deducts from cafeteria card balance.
     * High school students pay from their card.
     */
    public function paysViaCard(): bool
    {
        return $this->user_type === 'high_school_student';
    }

    /**
     * Human-friendly label for the user type.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->user_type) {
            'staff' => 'Staff',
            'college_student' => 'College Student',
            'high_school_student' => 'High School Student',
            default => 'Unknown',
        };
    }

    /**
     * True if we have a phone number on file for SMS.
     */
    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }

    // ──────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeForType($q, string $type)
    {
        return $q->where('user_type', $type);
    }
}
