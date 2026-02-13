<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StudentFee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'registration_id',
        'fee_structure_id',
        'invoice_number',
        'description',
        'detailed_description',
        'fee_category',
        'fee_type',
        'academic_year',
        'billing_month',
        'month_number',
        'billing_cycle',
        'amount',
        'discount',
        'discount_reason',
        'tax',
        'amount_paid',
        'payment_status',
        'invoice_date',
        'due_date',
        'paid_date',
        'installment_number',
        'total_installments',
        'is_installment',
        'late_fee_applied',
        'late_fee_amount',
        'late_fee_date',
        'days_overdue',
        'is_refundable',
        'refund_status',
        'refund_date',
        'refund_amount',
        'is_cdacc_fee',
        'cdacc_reference',
        'cdacc_status',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'late_fee_date' => 'date',
        'refund_date' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'is_installment' => 'boolean',
        'late_fee_applied' => 'boolean',
        'is_refundable' => 'boolean',
        'is_cdacc_fee' => 'boolean',
        'metadata' => 'array',
    ];

    // Note: Remove payment_plan_installment_id since it's not in your migration
    // protected $appends = ['is_overdue', 'days_remaining', 'is_refundable_deposit'];

    // Instead, use virtual columns from migration
    // Your migration already has virtual columns: subtotal, total_amount, balance
    // So we don't need to append them

    // Static boot method for auto-generating invoice number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fee) {
            if (empty($fee->invoice_number)) {
                $fee->invoice_number = self::generateInvoiceNumber();
            }

            if (empty($fee->invoice_date)) {
                $fee->invoice_date = now();
            }

            // Set initial payment status
            if ($fee->payment_status === 'draft' && $fee->amount > 0) {
                $fee->payment_status = 'pending';
            }
        });

        static::updating(function ($fee) {
            // Auto-update payment status based on amount_paid
            if ($fee->isDirty('amount_paid')) {
                $fee->updatePaymentStatus();
            }

            // Auto-apply late fee if overdue
            if ($fee->due_date < now() && !$fee->late_fee_applied && $fee->balance > 0) {
                $fee->applyLateFee();
            }
        });
    }

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    // Remove paymentPlanInstallment() relationship since column doesn't exist

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && $this->balance > 0;
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->due_date >= now()) {
            return now()->diffInDays($this->due_date, false); // Negative if overdue
        }
        return 0;
    }

    public function getIsRefundableDepositAttribute()
    {
        return $this->is_refundable &&
               in_array($this->fee_category, ['caution_money', 'tool_kit', 'protective_clothing']);
    }

    // Static Methods - Simplified for your migration
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Get next sequence
        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = $lastInvoice ?
            (int) substr($lastInvoice->invoice_number, -5) + 1 : 1;

        return sprintf('%s-%s%s-%05d',
            $prefix,
            $year,
            $month,
            $sequence
        );
    }

    // Business Logic Methods
    public function updatePaymentStatus()
    {
        if ($this->amount_paid >= $this->total_amount) {
            $this->payment_status = 'paid';
            $this->paid_date = now();
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
        } elseif ($this->due_date < now()) {
            $this->payment_status = 'overdue';
        } else {
            $this->payment_status = 'pending';
        }

        $this->save();
    }

    public function applyLateFee()
    {
        $feeStructure = $this->feeStructure;
        if (!$feeStructure || $this->late_fee_applied) {
            return;
        }

        $lateFeePercentage = $feeStructure->late_fee_percentage / 100;
        $this->late_fee_amount = $this->balance * $lateFeePercentage;
        $this->late_fee_applied = true;
        $this->late_fee_date = now();
        $this->days_overdue = now()->diffInDays($this->due_date);

        // Update the amount to include late fee
        $this->amount += $this->late_fee_amount;
        $this->save();
    }

    public function recordPayment($amount, $paymentData = [])
    {
        $this->amount_paid += $amount;
        $this->updatePaymentStatus();

        // Update registration balance
        if ($this->registration) {
            $this->registration->calculateBalance();
        }

        return $this;
    }

    public function waiveFee($reason = null, $approvedBy = null)
    {
        $this->payment_status = 'waived';
        $this->amount_paid = $this->total_amount;
        $this->notes = $this->notes . "\nFee waived. Reason: " . $reason;
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        $this->save();

        return $this;
    }

    public function initiateRefund($refundAmount = null, $reason = null)
    {
        if (!$this->is_refundable) {
            throw new \Exception('This fee is not refundable');
        }

        $refundAmount = $refundAmount ?? $this->amount_paid;

        $this->refund_status = 'pending_refund';
        $this->refund_amount = $refundAmount;
        $this->notes = $this->notes . "\nRefund initiated. Amount: {$refundAmount}. Reason: " . $reason;
        $this->save();

        return $this;
    }

    public function completeRefund($refundedBy = null)
    {
        if ($this->refund_status !== 'pending_refund') {
            throw new \Exception('Refund not initiated');
        }

        $this->refund_status = 'refunded';
        $this->refund_date = now();
        $this->payment_status = 'refunded';
        $this->notes = $this->notes . "\nRefund completed by: " . ($refundedBy ?: 'System');
        $this->save();

        return $this;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue')
                     ->orWhere(function ($q) {
                         $q->where('due_date', '<', now())
                           ->whereIn('payment_status', ['pending', 'partial']);
                     });
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForRegistration($query, $registrationId)
    {
        return $query->where('registration_id', $registrationId);
    }

    public function scopeForMonth($query, $monthNumber)
    {
        return $query->where('month_number', $monthNumber);
    }

    public function scopeCdaccFees($query)
    {
        return $query->where('is_cdacc_fee', true);
    }

    public function scopeRefundableDeposits($query)
    {
        return $query->where('is_refundable', true)
                     ->whereIn('fee_category', ['caution_money', 'tool_kit', 'protective_clothing']);
    }
}
