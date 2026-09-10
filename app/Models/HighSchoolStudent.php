<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class HighSchoolStudent extends Model
{
    protected $table = 'high_school_students';

    protected $fillable = [
        'admission_number',
        'full_name',
        'class',
        'stream',
        'date_of_birth',
        'gender',
        'admission_date',
        'is_boarder',
        'profile_picture',
        'parent_phone',
        'parent_name',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'is_boarder' => 'boolean',
    ];

    /**
     * Get the card account for this student.
     */
    public function cardAccount(): HasOne
    {
        return $this->hasOne(CardAccount::class, 'high_school_student_id');
    }

    /**
     * Get the contacts for this student.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(StudentContact::class, 'high_school_student_id');
    }

    /**
     * Get the primary contact.
     */
    public function primaryContact(): HasOne
    {
        return $this->hasOne(StudentContact::class, 'high_school_student_id')
            ->where('is_primary', true);
    }

    /**
     * Get all transactions for this student through their card.
     */
    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            CardTransaction::class,
            CardAccount::class,
            'high_school_student_id', // Foreign key on card_accounts
            'card_account_id',        // Foreign key on card_transactions
            'id',                     // Local key on high_school_students
            'id'                      // Local key on card_accounts
        );
    }

    /**
     * ✅ ADD THIS METHOD: Get the parent funding portal link
     */
    public function getParentFundingLink(): string
    {
        return route('parent.fund.portal', ['admissionNumber' => $this->admission_number]);
    }

    /**
     * Scope for active students.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for students by class.
     */
    public function scopeInClass($query, $class)
    {
        return $query->where('class', $class);
    }

    /**
     * Scope for students with cards.
     */
    public function scopeWithCard($query)
    {
        return $query->has('cardAccount');
    }

    /**
     * Scope for students without cards.
     */
    public function scopeWithoutCard($query)
    {
        return $query->doesntHave('cardAccount');
    }

    /**
     * Scope for boarders.
     */
    public function scopeBoarders($query)
    {
        return $query->where('is_boarder', true);
    }

    /**
     * Scope for day scholars.
     */
    public function scopeDayScholars($query)
    {
        return $query->where('is_boarder', false);
    }

    /**
     * Get student's card balance
     */
    public function getCardBalanceAttribute(): float
    {
        return $this->cardAccount ? $this->cardAccount->balance : 0;
    }

    /**
     * Get student's canteen balance
     */
    public function getCanteenBalanceAttribute(): float
    {
        if (!$this->cardAccount) {
            return 0;
        }
        return $this->cardAccount->canteen_balance ?? $this->cardAccount->balance;
    }

    /**
     * Get total funded amount
     */
    public function getTotalFundedAttribute(): float
    {
        return $this->cardAccount ? $this->cardAccount->total_funded : 0;
    }

    /**
     * Get total spent amount
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->cardAccount ? $this->cardAccount->total_spent : 0;
    }

    /**
     * Check if student has an active card
     */
    public function hasActiveCard(): bool
    {
        return $this->cardAccount && $this->cardAccount->is_active && !$this->cardAccount->is_blocked;
    }

    /**
     * Get recent transactions (limited)
     */
    public function getRecentTransactions($limit = 10)
    {
        if (!$this->cardAccount) {
            return collect();
        }

        return CardTransaction::where('card_account_id', $this->cardAccount->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get profile picture URL
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return null;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'graduated' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get boarder label
     */
    public function getBoarderLabelAttribute(): string
    {
        return $this->is_boarder ? 'Boarder' : 'Day Scholar';
    }

    /**
     * Get gender label
     */
    public function getGenderLabelAttribute(): string
    {
        return ucfirst($this->gender ?? 'N/A');
    }

    /**
     * Get formatted date of birth
     */
    public function getFormattedDobAttribute(): string
    {
        return $this->date_of_birth ? $this->date_of_birth->format('d/m/Y') : 'N/A';
    }

    /**
     * Get formatted admission date
     */
    public function getFormattedAdmissionDateAttribute(): string
    {
        return $this->admission_date ? $this->admission_date->format('d/m/Y') : 'N/A';
    }
}