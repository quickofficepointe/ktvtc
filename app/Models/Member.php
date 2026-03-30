<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'membership_start_date',
        'membership_end_date',
        'membership_type',
        'is_active',
        'branch_id',
        'outstanding_fines',
        'max_borrow_limit',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'membership_start_date' => 'date',
        'membership_end_date' => 'date',
        'is_active' => 'boolean',
        'outstanding_fines' => 'decimal:2',
        'max_borrow_limit' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function acquisitionRequests(): HasMany
    {
        return $this->hasMany(AcquisitionRequest::class);
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function readingChallenges(): HasMany
    {
        return $this->hasMany(ReadingChallenge::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getCurrentBorrowedBooksAttribute(): int
    {
        return $this->transactions()
                    ->whereIn('status', ['borrowed', 'overdue'])
                    ->count();
    }

    public function getCanBorrowMoreAttribute(): bool
    {
        return $this->current_borrowed_books < $this->max_borrow_limit;
    }

    public function getIsMembershipActiveAttribute(): bool
    {
        return $this->is_active && $this->membership_end_date >= now();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('membership_end_date', '>=', now());
    }
}
