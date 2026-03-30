<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FeePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // LINKS (KEEP THESE)
        'student_id',
        'enrollment_id',

        // PAYMENT DETAILS (ALL YOU NEED!)
        'amount',
        'payment_date',
        'receipt_number',
        'payment_method', // 'cash', 'mpesa', 'bank', 'kcb', 'other'
        'transaction_code', // MPESA ID, Bank Reference

        // FOR CSV IMPORT
        'payment_for_month', // 'JUNE', 'JULY', etc.

        // PAYER INFO
        'payer_name',
        'payer_phone',
        'payer_type', // 'student', 'parent', 'sponsor', 'employer', 'other'

        // STATUS
        'status', // 'pending', 'completed', 'failed', 'reversed'
        'is_verified',
        'verified_by',
        'verified_at',

        // METADATA
        'notes',
        'recorded_by',
        'import_source', // 'csv_2021', 'manual'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'amount' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    protected $appends = [
        'formatted_amount',
        'payment_method_label'
    ];

    /**
     * ============ RELATIONSHIPS ============
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * ============ ACCESSORS ============
     */
    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'kcb' => 'KCB',
            'mpesa' => 'M-Pesa',
            'bank' => 'Bank Transfer',
            'cash' => 'Cash',
            default => ucfirst($this->payment_method)
        };
    }

    /**
     * ============ STATIC METHODS ============
     */
    public static function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');

        $last = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        return sprintf('RCT-%s%s-%04d', $year, $month, $last + 1);
    }

    /**
     * ============ SCOPES ============
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeForDateRange($query, $start, $end)
    {
        return $query->whereBetween('payment_date', [$start, $end]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', now());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                     ->whereYear('payment_date', now()->year);
    }
}
