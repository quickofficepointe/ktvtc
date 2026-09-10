<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enrollment_id',
        'student_id',
        'plan_code',
        'plan_name',
        'plan_type',
        'total_amount',
        'discount_amount',
        'discount_reason',
        'net_amount',
        'amount_paid',
        'balance',
        'number_of_installments',
        'installment_schedule',
        'start_date',
        'end_date',
        'first_payment_date',
        'late_fee_percentage',
        'grace_period_days',
        'auto_generate_invoices',
        'status',
        'is_approved',
        'approved_by',
        'approved_at',
        'agreement_document_path',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',
        'grace_period_days' => 'integer',
        'auto_generate_invoices' => 'boolean',
        'is_approved' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'first_payment_date' => 'date',
        'approved_at' => 'datetime',
        'installment_schedule' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->plan_code)) {
                $plan->plan_code = self::generatePlanCode();
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

    public function installments()
    {
        return $this->hasMany(PlanInstallment::class, 'payment_plan_id');
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
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Methods
    public static function generatePlanCode()
    {
        $prefix = 'PLN';
        $year = date('Y');
        $random = Str::upper(Str::random(6));

        return "{$prefix}-{$year}-{$random}";
    }

    public function generateInstallments()
    {
        $installments = [];
        $amountPerInstallment = $this->net_amount / $this->number_of_installments;
        $currentDate = $this->first_payment_date;

        for ($i = 1; $i <= $this->number_of_installments; $i++) {
            $installments[] = [
                'installment_number' => $i,
                'amount' => $amountPerInstallment,
                'due_date' => $currentDate->copy(),
                'status' => 'upcoming',
            ];

            // Add frequency logic (monthly, quarterly, etc.)
            $currentDate = match($this->plan_type) {
                'monthly' => $currentDate->addMonth(),
                'quarterly' => $currentDate->addMonths(3),
                'semester' => $currentDate->addMonths(6),
                'annual' => $currentDate->addYear(),
                default => $currentDate->addMonth(),
            };
        }

        $this->installment_schedule = $installments;
        $this->save();

        // Create installment records
        foreach ($installments as $installment) {
            $this->installments()->create($installment);
        }

        return $this->installments;
    }

    public function updateBalance()
    {
        $this->amount_paid = $this->installments()->sum('amount_paid');
        $this->balance = $this->net_amount - $this->amount_paid;
        $this->save();

        if ($this->balance <= 0) {
            $this->status = 'completed';
            $this->save();
        }

        return $this;
    }

    public function approve($approvedBy = null)
    {
        $this->is_approved = true;
        $this->approved_by = $approvedBy ?? auth()->id();
        $this->approved_at = now();
        $this->status = 'active';
        $this->save();

        return $this;
    }
}
