<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'plan_installments';

    protected $fillable = [
        'payment_plan_id',
        'invoice_id',
        'installment_number',
        'description',
        'amount',
        'amount_paid',
        'balance',
        'due_date',
        'paid_date',
        'status',
        'late_fee_applied',
        'late_fee_amount',
        'days_overdue',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'days_overdue' => 'integer',
        'due_date' => 'date',
        'paid_date' => 'date',
        'late_fee_applied' => 'boolean',
    ];

    // Relationships
    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereIn('status', ['upcoming', 'pending']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Methods
    public function markAsPaid($amount = null, $paymentReference = null)
    {
        $paidAmount = $amount ?? $this->amount;

        $this->amount_paid = $paidAmount;
        $this->balance = $this->amount - $paidAmount;
        $this->paid_date = now();
        $this->payment_reference = $paymentReference;

        if ($this->balance <= 0) {
            $this->status = 'paid';
            $this->balance = 0;
        } else {
            $this->status = 'partial';
        }

        $this->save();

        // Update parent payment plan
        $this->paymentPlan->updateBalance();

        return $this;
    }

    public function applyLateFee()
    {
        if ($this->late_fee_applied || $this->status === 'paid') {
            return;
        }

        $lateFeePercentage = $this->paymentPlan->late_fee_percentage / 100;
        $this->late_fee_amount = $this->balance * $lateFeePercentage;
        $this->late_fee_applied = true;
        $this->days_overdue = now()->diffInDays($this->due_date);
        $this->save();

        return $this;
    }
}
