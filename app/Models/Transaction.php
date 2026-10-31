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
        'issued_by',
        'returned_by',
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

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'overdue' ||
               ($this->status === 'borrowed' && $this->due_date < now());
    }

    public function getDaysOverdueAttribute(): int
    {
        return $this->daysOverdueAt($this->return_date ?? now());
    }

    /** Whole days late on $at (0 if not late). Carbon 3's diffInDays is signed, so order matters. */
    public function daysOverdueAt($at): int
    {
        if (!$this->due_date) {
            return 0;
        }
        $at = Carbon::parse($at)->startOfDay();
        $due = $this->due_date->copy()->startOfDay();

        return $at->greaterThan($due) ? (int) $due->diffInDays($at) : 0;
    }

    /**
     * Fine for returning on $at under the active rules. Each rule skips its grace
     * days and is capped by its own day and amount limits.
     */
    public function calculateFine($fineRules, $at = null): float
    {
        $daysOverdue = $this->daysOverdueAt($at ?? now());
        $fineAmount = 0;

        foreach ($fineRules as $rule) {
            if (!$rule->is_active) {
                continue;
            }
            $days = max(0, $daysOverdue - (int) $rule->grace_period_days);
            if ($days === 0) {
                continue;
            }
            if ($rule->max_fine_days) {
                $days = min($days, (int) $rule->max_fine_days);
            }
            $amount = match ($rule->calculation_type) {
                'daily' => $days * (float) $rule->fine_amount,
                'fixed' => (float) $rule->fine_amount,
                'percentage' => round((float) ($this->item?->book?->price ?? 0) * (float) $rule->fine_amount / 100, 2),
                default => 0,
            };
            if ($rule->max_fine_amount !== null && (float) $rule->max_fine_amount > 0) {
                $amount = min($amount, (float) $rule->max_fine_amount);
            }
            $fineAmount += $amount;
        }

        return round($fineAmount, 2);
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
