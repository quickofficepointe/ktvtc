<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PlanInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'plan_installments';

    protected $fillable = [
        'payment_plan_id',
        'installment_number',
        'description',
        'amount',
        'amount_paid',
        'balance',
        'due_date',
        'paid_date',
        'status', // upcoming, pending, paid, overdue, waived
        'payment_reference',
        'late_fee_applied',
        'late_fee_amount',
        'days_overdue',
        'invoice_id',
        'invoice_generated',
        'invoice_generated_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'invoice_generated_date' => 'date',
        'late_fee_applied' => 'boolean',
        'invoice_generated' => 'boolean',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
    ];

    /**
     * Get the payment plan that owns this installment.
     */
    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }

    /**
     * Get the invoice associated with this installment.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Get the user who created this installment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this installment.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if installment is overdue.
     */
    public function isOverdue()
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    /**
     * Get status color for badges.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'paid' => 'green',
            'pending' => 'yellow',
            'overdue' => 'red',
            'upcoming' => 'blue',
            'waived' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'overdue' => 'Overdue',
            'upcoming' => 'Upcoming',
            'waived' => 'Waived',
            default => ucfirst($this->status)
        };
    }

    /**
     * Mark installment as paid.
     */
    public function markAsPaid($amount, $reference = null)
    {
        $this->amount_paid = $amount;
        $this->balance = $this->amount - $amount;
        $this->paid_date = now();
        $this->payment_reference = $reference;
        $this->status = $this->balance <= 0 ? 'paid' : 'pending';
        $this->save();
    }

    /**
     * Apply late fee.
     */
    public function applyLateFee()
    {
        if (!$this->late_fee_applied && $this->isOverdue()) {
            // Get late fee rules from settings or use defaults
            $lateFeePercentage = 0.02; // 2% default
            $lateFeeAmount = $this->balance * $lateFeePercentage;

            $this->late_fee_applied = true;
            $this->late_fee_amount = $lateFeeAmount;
            $this->balance += $lateFeeAmount;
            $this->save();
        }
    }

    /**
     * Scope a query to only include upcoming installments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
            ->where('due_date', '>', now());
    }

    /**
     * Scope a query to only include pending installments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid installments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include overdue installments.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<', now());
    }

    /**
     * Scope a query to only include installments due within a date range.
     */
    public function scopeDueBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }
}
