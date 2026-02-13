<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentPlanInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_plan_id',
        'student_fee_id',
        'installment_number',
        'description',
        'amount',
        'amount_paid',
        'due_date',
        'paid_date',
        'invoice_generated_date',
        'status',
        'late_fee_applied',
        'late_fee_amount',
        'late_fee_applied_date',
        'days_overdue',
        'invoice_number',
        'invoice_generated',
        'invoice_sent_at',
        'invoice_reminder_sent',
        'last_reminder_sent_at',
        'payment_details',
        'payment_reference',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'invoice_generated_date' => 'date',
        'late_fee_applied_date' => 'date',
        'invoice_sent_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'late_fee_applied' => 'boolean',
        'invoice_generated' => 'boolean',
        'invoice_reminder_sent' => 'boolean',
        'payment_details' => 'array',
    ];

    protected $appends = ['balance', 'is_overdue'];

    // Relationships
    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function studentFee()
    {
        return $this->belongsTo(StudentFee::class);
    }

    // Accessors
    public function getBalanceAttribute()
    {
        return $this->amount - $this->amount_paid;
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() &&
               in_array($this->status, ['pending', 'partial']);
    }

    // Business Logic Methods
    public function recordPayment($amount, $paymentMethod = null, $reference = null)
    {
        $this->amount_paid += $amount;

        if ($this->amount_paid >= $this->amount) {
            $this->status = 'paid';
            $this->paid_date = now();
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        }

        if ($paymentMethod) {
            $this->payment_method = $paymentMethod;
        }

        if ($reference) {
            $this->payment_reference = $reference;
        }

        $this->save();

        // Update parent payment plan
        $this->paymentPlan->recordPayment($amount);

        return $this;
    }

    public function generateInvoice()
    {
        if ($this->invoice_generated) {
            return $this->invoice_number;
        }

        // Generate invoice number
        $this->invoice_number = 'INV-' . $this->paymentPlan->plan_code . '-' . str_pad($this->installment_number, 3, '0', STR_PAD_LEFT);
        $this->invoice_generated = true;
        $this->invoice_generated_date = now();
        $this->save();

        // Create or update student fee record
        $studentFee = $this->studentFee()->firstOrNew([]);
        $studentFee->fill([
            'student_id' => $this->paymentPlan->student_id,
            'registration_id' => $this->paymentPlan->registration_id,
            'fee_structure_id' => $this->paymentPlan->fee_structure_id,
            'invoice_number' => $this->invoice_number,
            'description' => $this->description ?? "Installment {$this->installment_number} - {$this->paymentPlan->plan_name}",
            'fee_category' => 'tuition',
            'fee_type' => 'recurring',
            'academic_year' => $this->paymentPlan->registration->academic_year ?? date('Y'),
            'amount' => $this->amount,
            'amount_paid' => $this->amount_paid,
            'payment_status' => $this->status,
            'invoice_date' => now(),
            'due_date' => $this->due_date,
            'paid_date' => $this->paid_date,
            'installment_number' => $this->installment_number,
            'total_installments' => $this->paymentPlan->number_of_installments,
            'is_installment' => true,
        ])->save();

        $this->student_fee_id = $studentFee->id;
        $this->save();

        return $this->invoice_number;
    }

    public function applyLateFee()
    {
        if (!$this->is_overdue || $this->late_fee_applied) {
            return $this;
        }

        $paymentPlan = $this->paymentPlan;
        $lateFeeRate = $paymentPlan->late_fee_percentage / 100;
        $this->late_fee_amount = $this->balance * $lateFeeRate;
        $this->late_fee_applied = true;
        $this->late_fee_applied_date = now();
        $this->days_overdue = now()->diffInDays($this->due_date);

        // Update amount to include late fee
        $this->amount += $this->late_fee_amount;
        $this->save();

        // Update associated student fee
        if ($this->studentFee) {
            $this->studentFee->amount += $this->late_fee_amount;
            $this->studentFee->save();
        }

        return $this;
    }

    public function sendReminder()
    {
        if ($this->invoice_reminder_sent &&
            now()->diffInDays($this->last_reminder_sent_at) < 3) {
            return false;
        }

        // Logic to send reminder (email/SMS)
        $this->invoice_reminder_sent = true;
        $this->last_reminder_sent_at = now();
        $this->save();

        return true;
    }
}
