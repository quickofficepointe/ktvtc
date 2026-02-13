<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnrollmentFeeItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enrollment_id',
        'fee_category_id',
        'fee_template_item_id',
        'item_name',
        'description',
        'amount',
        'quantity',
        'total_amount',
        'amount_paid',
        'balance',
        'applicable_terms',
        'term_number',
        'is_required',
        'is_refundable',
        'due_day_offset',
        'due_date',
        'is_advance_payment',
        'status',
        'is_active',
        'sort_order',
        'is_visible_to_student',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'quantity' => 'integer',
        'due_date' => 'date',
        'due_day_offset' => 'integer',
        'term_number' => 'integer',
        'is_required' => 'boolean',
        'is_refundable' => 'boolean',
        'is_advance_payment' => 'boolean',
        'is_active' => 'boolean',
        'is_visible_to_student' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * ============ RELATIONSHIPS ============
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function feeTemplateItem()
    {
        return $this->belongsTo(FeeTemplateItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * ============ SCOPES ============
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partially_paid');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeByTerm($query, $termNumber)
    {
        return $query->where('term_number', $termNumber);
    }

    public function scopeDueBefore($query, $date)
    {
        return $query->where('due_date', '<=', $date);
    }

    /**
     * ============ ACCESSORS ============
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'paid' => 'green',
            'partially_paid' => 'yellow',
            'pending' => 'blue',
            'waived' => 'gray',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'paid' => 'Paid',
            'partially_paid' => 'Partial',
            'pending' => 'Pending',
            'waived' => 'Waived',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }

    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return 'KES ' . number_format($this->total_amount, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return 'KES ' . number_format($this->balance, 2);
    }

    public function getFormattedDueDateAttribute()
    {
        return $this->due_date ? $this->due_date->format('M d, Y') : 'N/A';
    }

    public function getTermLabelAttribute()
    {
        if ($this->term_number) {
            return "Term {$this->term_number}";
        }
        if ($this->applicable_terms) {
            return $this->applicable_terms;
        }
        return 'All Terms';
    }

    /**
     * ============ METHODS ============
     */
    public function calculateBalance()
    {
        $this->balance = $this->total_amount - $this->amount_paid;
        return $this->balance;
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isOverdue()
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               !in_array($this->status, ['paid', 'waived']);
    }

    public function markAsPaid($amount = null)
    {
        $paidAmount = $amount ?? $this->total_amount;

        $this->amount_paid = $paidAmount;
        $this->balance = $this->total_amount - $paidAmount;

        if ($this->balance <= 0) {
            $this->status = 'paid';
            $this->balance = 0;
        } else {
            $this->status = 'partially_paid';
        }

        return $this->save();
    }

    public function waive()
    {
        $this->status = 'waived';
        $this->amount_paid = 0;
        $this->balance = 0;
        return $this->save();
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->is_active = false;
        return $this->save();
    }
}
