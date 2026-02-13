<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'item_id',
        'branch_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
        'notes',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'overdue' ||
               ($this->status === 'borrowed' && $this->due_date < now());
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return max(0, now()->diffInDays($this->due_date));
    }

    public function calculateFine($fineRules): float
    {
        if (!$this->is_overdue) {
            return 0;
        }

        $daysOverdue = $this->days_overdue;
        $fineAmount = 0;

        foreach ($fineRules as $rule) {
            if ($rule->is_active) {
                if ($rule->calculation_type === 'daily') {
                    $applicableDays = min($daysOverdue, $rule->max_fine_days ?? $daysOverdue);
                    $fineAmount += $applicableDays * $rule->fine_amount;
                } elseif ($rule->calculation_type === 'fixed') {
                    $fineAmount += $rule->fine_amount;
                }
            }
        }

        return min($fineAmount, $fineRules->max('max_fine_amount') ?? $fineAmount);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereIn('status', ['borrowed', 'overdue']);
    }

    public function scopeCurrent($query)
    {
        return $query->whereIn('status', ['borrowed', 'overdue']);
    }
}
