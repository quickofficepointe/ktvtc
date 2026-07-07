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
        'profile_picture',
        'parent_phone',
        'parent_name',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the card account for this student
     */
    public function cardAccount(): HasOne
    {
        return $this->hasOne(CardAccount::class, 'high_school_student_id');
    }

    /**
     * Get the contacts for this student
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(StudentContact::class, 'high_school_student_id');
    }

    /**
     * Get the primary contact
     */
    public function primaryContact(): HasOne
    {
        return $this->hasOne(StudentContact::class, 'high_school_student_id')
            ->where('is_primary', true);
    }

    /**
     * Get all transactions for this student through their card
     * Fixed: Changed return type to HasManyThrough
     */
    public function transactions(): HasManyThrough  // Changed from HasMany to HasManyThrough
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
     * Scope for active students
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for students by class
     */
    public function scopeInClass($query, $class)
    {
        return $query->where('class', $class);
    }
}
