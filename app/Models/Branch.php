<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'opening_time',
        'closing_time',
        'is_active',
        'manager_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // Add scopeActive method
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getOperatingHoursAttribute(): string
    {
        return date('g:i A', strtotime($this->opening_time)) . ' - ' .
               date('g:i A', strtotime($this->closing_time));
    }
}
