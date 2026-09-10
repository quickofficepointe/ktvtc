<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'description',
        'subtotal',
        'discount',
        'discount_reason',
        'tax',
        'total_amount',
        'amount_paid',
        'balance',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            if (empty($invoice->invoice_date)) {
                $invoice->invoice_date = now();
            }
        });
    }

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class, 'invoice_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereIn('status', ['sent', 'partial']);
    }

    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['sent', 'partial'])
                     ->where('balance', '>', 0);
    }

    // Methods
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        $lastInvoice = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = $lastInvoice ?
            (int) substr($lastInvoice->invoice_number, -5) + 1 : 1;

        return sprintf('%s-%s%s-%05d', $prefix, $year, $month, $sequence);
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->items()->sum('total');
        $this->total_amount = $this->subtotal - $this->discount + $this->tax;
        $this->balance = $this->total_amount - $this->amount_paid;
        $this->save();

        return $this;
    }

    public function updatePaymentStatus()
    {
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date < now()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'sent';
        }
        $this->save();

        // Update enrollment balance
        if ($this->enrollment) {
            $this->enrollment->amount_paid = $this->enrollment->invoices()->sum('amount_paid');
            $this->enrollment->balance = $this->enrollment->total_course_fee - $this->enrollment->amount_paid;
            $this->enrollment->save();
        }

        return $this;
    }

    public function markAsPaid($amount = null)
    {
        $this->amount_paid = $amount ?? $this->total_amount;
        $this->balance = $this->total_amount - $this->amount_paid;
        $this->updatePaymentStatus();
        return $this;
    }
}
