<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'stat_date',
        'branch_id',
        'total_borrows',
        'total_returns',
        'total_reservations',
        'new_members',
        'active_members',
        'total_fines',
        'collected_fines',
    ];

    protected $casts = [
        'stat_date' => 'date',
        'total_borrows' => 'integer',
        'total_returns' => 'integer',
        'total_reservations' => 'integer',
        'new_members' => 'integer',
        'active_members' => 'integer',
        'total_fines' => 'decimal:2',
        'collected_fines' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getUtilizationRateAttribute(): float
    {
        if ($this->active_members === 0) {
            return 0;
        }

        return ($this->total_borrows / $this->active_members) * 100;
    }

    public function getFineCollectionRateAttribute(): float
    {
        if ($this->total_fines === 0) {
            return 0;
        }

        return ($this->collected_fines / $this->total_fines) * 100;
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('stat_date', [$startDate, $endDate]);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
