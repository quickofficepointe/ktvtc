<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FineRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'fine_amount',
        'calculation_type',
        'grace_period_days',
        'max_fine_days',
        'max_fine_amount',
        'is_active',
    ];

    protected $casts = [
        'fine_amount' => 'decimal:2',
        'max_fine_amount' => 'decimal:2',
        'grace_period_days' => 'integer',
        'max_fine_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getEffectiveFineAmount($daysOverdue): float
    {
        if ($daysOverdue <= $this->grace_period_days) {
            return 0;
        }

        $effectiveDays = $daysOverdue - $this->grace_period_days;

        if ($this->max_fine_days) {
            $effectiveDays = min($effectiveDays, $this->max_fine_days);
        }

        $calculatedFine = $effectiveDays * $this->fine_amount;

        if ($this->max_fine_amount) {
            return min($calculatedFine, $this->max_fine_amount);
        }

        return $calculatedFine;
    }
}
