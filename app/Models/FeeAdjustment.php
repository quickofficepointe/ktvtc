<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A change to what a student owes (fees_change) or has paid (paid_change) that is not a payment.
 */
class FeeAdjustment extends Model
{
    public const DISCOUNT     = 'discount';
    public const WAIVER       = 'waiver';
    public const BURSARY      = 'bursary';
    public const CHARGE       = 'charge';
    public const REFUND       = 'refund';
    public const TRANSFER_OUT = 'transfer_out';
    public const TRANSFER_IN  = 'transfer_in';
    public const WRITE_OFF    = 'write_off';

    /** Term adjustments finance can make, and whether they lower the fee. */
    public const TERM_TYPES = [
        self::DISCOUNT => 'Discount',
        self::WAIVER   => 'Waiver',
        self::BURSARY  => 'Bursary / sponsorship',
        self::CHARGE   => 'Extra charge',
    ];

    protected $fillable = [
        'student_id', 'enrollment_id', 'term_registration_id', 'type', 'fees_change', 'paid_change',
        'reason', 'method', 'reference', 'course_change_request_id', 'created_by',
    ];

    protected $casts = [
        'fees_change' => 'decimal:2',
        'paid_change' => 'decimal:2',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function termRegistration(): BelongsTo
    {
        return $this->belongsTo(TermRegistration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLabelAttribute(): string
    {
        return match ($this->type) {
            self::REFUND       => 'Refund paid out',
            self::TRANSFER_OUT => 'Moved to new course',
            self::TRANSFER_IN  => 'Brought from previous course',
            self::WRITE_OFF    => 'Balance cleared on course change',
            default            => self::TERM_TYPES[$this->type] ?? ucfirst($this->type),
        } . ($this->termRegistration ? " (Term {$this->termRegistration->term_number})" : '');
    }
}
