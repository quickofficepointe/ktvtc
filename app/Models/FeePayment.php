<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FeePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_fee_id',
        'student_id',
        'registration_id',
        'transaction_id',
        'receipt_number',
        'reference_number',
        'amount',
        'balance_before',
        'balance_after',
        'currency',
        'payment_method',
        'kcb_transaction_code',
        'kcb_merchant_request_id',
        'kcb_checkout_request_id',
        'kcb_phone_number',
        'kcb_account_number',
        'paybill_number',
        'paybill_account_number',
        'paybill_transaction_code',
        'bank_name',
        'bank_branch',
        'deposit_slip_number',
        'deposit_date',
        'payment_date',
        'payment_time',
        'processed_at',
        'status',
        'is_verified',
        'verified_by',
        'verified_at',
        'verification_notes',
        'payer_name',
        'payer_email',
        'payer_phone',
        'payer_id_number',
        'payer_address',
        'payer_type',
        'receipt_generated_by',
        'receipt_generated_at',
        'receipt_file_path',
        'receipt_sent_to_payer',
        'receipt_sent_at',
        'recorded_by',
        'approved_by',
        'approved_at',
        'description',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'deposit_date' => 'date',
        'processed_at' => 'datetime',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'receipt_generated_at' => 'datetime',
        'receipt_sent_at' => 'datetime',
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'is_verified' => 'boolean',
        'receipt_sent_to_payer' => 'boolean',
        'metadata' => 'array',
    ];

    protected $appends = [
        'formatted_amount',
        'payment_method_label',
        'is_kcb_payment',
        'is_paybill_payment',
        'payment_datetime'
    ];

    // Static boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = self::generateTransactionId();
            }

            if (empty($payment->receipt_number)) {
                $payment->receipt_number = self::generateReceiptNumber();
            }

            if (empty($payment->payment_date)) {
                $payment->payment_date = now();
            }

            if (empty($payment->payment_time)) {
                $payment->payment_time = now()->format('H:i:s');
            }

            // Set recorded_by if not set
            if (empty($payment->recorded_by) && auth()->check()) {
                $payment->recorded_by = auth()->id();
            }
        });

        static::created(function ($payment) {
            // Update student fee balance
            if ($payment->studentFee) {
                $payment->studentFee->recordPayment($payment->amount);
            }

            // Update registration monthly payments
            if ($payment->studentFee && $payment->studentFee->month_number) {
                $payment->registration->updateMonthlyPaymentStatus(
                    'month_' . $payment->studentFee->month_number,
                    'paid',
                    $payment->payment_date
                );
            }

            // Send receipt if configured
            if (config('app.auto_send_receipts')) {
                $payment->sendReceipt();
            }
        });
    }

    // Relationships
    public function studentFee()
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiptGenerator()
    {
        return $this->belongsTo(User::class, 'receipt_generated_by');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'kcb_stk_push' => 'KCB STK Push',
            'paybill' => 'Paybill',
            'bank_deposit' => 'Bank Deposit',
            'cash' => 'Cash',
            'helb' => 'HELB',
            'sponsor' => 'Sponsor',
            'other' => 'Other',
        ];

        return $labels[$this->payment_method] ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    public function getIsKcbPaymentAttribute()
    {
        return $this->payment_method === 'kcb_stk_push' && !empty($this->kcb_transaction_code);
    }

    public function getIsPaybillPaymentAttribute()
    {
        return $this->payment_method === 'paybill' && !empty($this->paybill_transaction_code);
    }

    public function getPaymentDateTimeAttribute()
    {
        return Carbon::parse($this->payment_date . ' ' . $this->payment_time);
    }

    // Static Methods
    public static function generateTransactionId()
    {
        $prefix = 'TXN';
        $date = date('Ymd');

        do {
            $random = Str::upper(Str::random(6));
            $transactionId = "{$prefix}-{$date}-{$random}";
        } while (self::where('transaction_id', $transactionId)->exists());

        return $transactionId;
    }

    public static function generateReceiptNumber()
    {
        $prefix = 'RCPT';
        $year = date('Y');
        $campusCode = 'KTVTC'; // Default, can be overridden

        $lastReceipt = self::whereYear('created_at', $year)
            ->orderBy('receipt_number', 'desc')
            ->first();

        $sequence = $lastReceipt ?
            (int) substr($lastReceipt->receipt_number, -6) + 1 : 1;

        return sprintf('%s-%s-%s-%06d',
            $prefix,
            $year,
            $campusCode,
            $sequence
        );
    }

    // Business Logic Methods
    public function verifyPayment($verifiedBy = null, $notes = null)
    {
        $this->is_verified = true;
        $this->verified_by = $verifiedBy ?? auth()->id();
        $this->verified_at = now();
        $this->verification_notes = $notes;
        $this->status = 'completed';
        $this->save();

        return $this;
    }

    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        $this->notes = $this->notes . "\nPayment failed. Reason: " . $reason;
        $this->save();

        return $this;
    }

    public function reversePayment($reason = null, $reversedBy = null)
    {
        $this->status = 'reversed';
        $this->notes = $this->notes . "\nPayment reversed. Reason: " . $reason . " | By: " . ($reversedBy ?? 'System');
        $this->save();

        // Reverse the payment in student fee
        if ($this->studentFee) {
            $this->studentFee->amount_paid -= $this->amount;
            $this->studentFee->updatePaymentStatus();
        }

        // Update registration balance
        if ($this->registration) {
            $this->registration->calculateBalance();
        }

        return $this;
    }

    public function sendReceipt($sendEmail = true, $sendSms = false)
    {
        // Generate receipt PDF
        $receiptPath = $this->generateReceiptPdf();
        $this->receipt_file_path = $receiptPath;
        $this->receipt_generated_by = auth()->id();
        $this->receipt_generated_at = now();

        // Send email receipt
        if ($sendEmail && $this->payer_email) {
            $this->sendEmailReceipt();
        }

        // Send SMS receipt
        if ($sendSms && $this->payer_phone) {
            $this->sendSmsReceipt();
        }

        $this->receipt_sent_to_payer = true;
        $this->receipt_sent_at = now();
        $this->save();

        return $this;
    }

    private function generateReceiptPdf()
    {
        // This would generate a PDF receipt
        // For now, return a placeholder path
        return 'receipts/' . $this->receipt_number . '.pdf';
    }

    private function sendEmailReceipt()
    {
        // Send email logic here
        // You would use Laravel's Mail facade
        return true;
    }

    private function sendSmsReceipt()
    {
        // Send SMS logic here
        // You would use an SMS gateway
        return true;
    }

    public function getReceiptData()
    {
        return [
            'receipt_number' => $this->receipt_number,
            'transaction_id' => $this->transaction_id,
            'date' => $this->payment_date->format('d/m/Y'),
            'time' => $this->payment_time,
            'student_name' => $this->student->name ?? 'N/A',
            'student_number' => $this->student->student_number ?? 'N/A',
            'registration_number' => $this->registration->registration_number ?? 'N/A',
            'course' => $this->registration->course->name ?? 'N/A',
            'campus' => $this->registration->campus->name ?? 'N/A',
            'fee_description' => $this->studentFee->description ?? 'N/A',
            'amount' => $this->formatted_amount,
            'payment_method' => $this->payment_method_label,
            'payer_name' => $this->payer_name,
            'payer_phone' => $this->payer_phone,
            'balance_before' => 'KES ' . number_format($this->balance_before, 2),
            'balance_after' => 'KES ' . number_format($this->balance_after, 2),
            'processed_by' => $this->recorder->name ?? 'System',
        ];
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePendingVerification($query)
    {
        return $query->where('is_verified', false)->where('status', 'completed');
    }

    public function scopeKcbPayments($query)
    {
        return $query->where('payment_method', 'kcb_stk_push');
    }

    public function scopePaybillPayments($query)
    {
        return $query->where('payment_method', 'paybill');
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
