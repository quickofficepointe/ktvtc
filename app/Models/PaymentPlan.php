<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_id',
        'student_id',
        'fee_structure_id',
        'plan_code',
        'plan_name',
        'plan_type',
        'total_course_amount',
        'discount_amount',
        'discount_reason',
        'amount_paid',
        'total_due',
        'number_of_installments',
        'installment_frequency',
        'installment_schedule',
        'start_date',
        'end_date',
        'first_payment_date',
        'last_payment_date',
        'terms_and_conditions',
        'late_fee_percentage',
        'grace_period_days',
        'auto_generate_invoices',
        'invoice_days_before_due',
        'status',
        'is_approved',
        'approved_by',
        'approved_at',
        'student_signatory_id',
        'parent_signatory_id',
        'institution_signatory_id',
        'student_signed_at',
        'parent_signed_at',
        'institution_signed_at',
        'agreement_document_path',
        'created_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'first_payment_date' => 'date',
        'last_payment_date' => 'date',
        'approved_at' => 'datetime',
        'student_signed_at' => 'datetime',
        'parent_signed_at' => 'datetime',
        'institution_signed_at' => 'datetime',
        'total_course_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'total_balance' => 'decimal:2',
        'total_due' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',
        'is_approved' => 'boolean',
        'auto_generate_invoices' => 'boolean',
        'installment_schedule' => 'array',
        'metadata' => 'array',
    ];

    protected $appends = [
        'net_amount',
        'total_balance',
        'is_active',
        'is_overdue',
        'remaining_installments',
        'completion_percentage'
    ];

    // Static boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->plan_code)) {
                $plan->plan_code = self::generatePlanCode();
            }

            if (empty($plan->created_by) && auth()->check()) {
                $plan->created_by = auth()->id();
            }
        });

        static::created(function ($plan) {
            // Generate installments if not custom schedule
            if ($plan->plan_type !== 'custom' && $plan->number_of_installments > 1) {
                $plan->generateInstallments();
            }
        });
    }

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function studentSignatory()
    {
        return $this->belongsTo(User::class, 'student_signatory_id');
    }

    public function parentSignatory()
    {
        return $this->belongsTo(User::class, 'parent_signatory_id');
    }

    public function institutionSignatory()
    {
        return $this->belongsTo(User::class, 'institution_signatory_id');
    }

    public function installments()
    {
        return $this->hasMany(PaymentPlanInstallment::class);
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }

    // Accessors
    public function getNetAmountAttribute()
    {
        return $this->total_course_amount - $this->discount_amount;
    }

    public function getTotalBalanceAttribute()
    {
        return $this->net_amount - $this->amount_paid;
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active' &&
               $this->start_date <= now() &&
               $this->end_date >= now();
    }

    public function getIsOverdueAttribute()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $overdueInstallments = $this->installments()
            ->where('due_date', '<', now())
            ->whereIn('status', ['pending', 'partial'])
            ->exists();

        return $overdueInstallments;
    }

    public function getRemainingInstallmentsAttribute()
    {
        return $this->installments()
            ->whereIn('status', ['pending', 'partial'])
            ->count();
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->net_amount <= 0) {
            return 100;
        }

        return min(100, round(($this->amount_paid / $this->net_amount) * 100, 2));
    }

    // Static Methods
    public static function generatePlanCode()
    {
        $prefix = 'PLAN';
        $year = date('Y');

        do {
            $random = Str::upper(Str::random(6));
            $planCode = "{$prefix}-{$year}-{$random}";
        } while (self::where('plan_code', $planCode)->exists());

        return $planCode;
    }

    // Business Logic Methods
    public function generateInstallments()
    {
        $installmentAmount = $this->net_amount / $this->number_of_installments;
        $installmentAmount = round($installmentAmount, 2);

        // Adjust last installment for rounding differences
        $totalAllocated = $installmentAmount * ($this->number_of_installments - 1);
        $lastInstallmentAmount = $this->net_amount - $totalAllocated;

        $dueDate = Carbon::parse($this->first_payment_date);

        for ($i = 1; $i <= $this->number_of_installments; $i++) {
            $amount = ($i === $this->number_of_installments) ?
                $lastInstallmentAmount : $installmentAmount;

            // Calculate due date based on frequency
            if ($i > 1) {
                switch ($this->installment_frequency) {
                    case 'monthly':
                        $dueDate->addMonth();
                        break;
                    case 'quarterly':
                        $dueDate->addMonths(3);
                        break;
                    case 'semester':
                        $dueDate->addMonths(6);
                        break;
                    case 'annual':
                        $dueDate->addYear();
                        break;
                    default:
                        $dueDate->addMonth();
                }
            }

            PaymentPlanInstallment::create([
                'payment_plan_id' => $this->id,
                'installment_number' => $i,
                'amount' => $amount,
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => $i === 1 ? 'pending' : 'upcoming',
                'description' => "Installment {$i} of {$this->number_of_installments} - {$this->plan_name}",
            ]);
        }

        // Update last payment date
        $this->last_payment_date = $dueDate;
        $this->save();
    }

    public function approve($approvedBy = null, $notes = null)
    {
        $this->is_approved = true;
        $this->approved_by = $approvedBy ?? auth()->id();
        $this->approved_at = now();
        $this->status = 'approved';

        if ($notes) {
            $this->notes = $this->notes . "\nApproved: " . $notes;
        }

        $this->save();

        // Activate plan if start date is today or earlier
        if ($this->start_date <= now()) {
            $this->activate();
        }

        return $this;
    }

    public function activate()
    {
        if (!$this->is_approved) {
            throw new \Exception('Plan must be approved before activation');
        }

        $this->status = 'active';
        $this->save();

        // Generate first invoice if auto-generate is enabled
        if ($this->auto_generate_invoices) {
            $this->generateInvoicesForDueInstallments();
        }

        return $this;
    }

    public function generateInvoicesForDueInstallments()
    {
        $dueInstallments = $this->installments()
            ->where('due_date', '<=', now()->addDays($this->invoice_days_before_due))
            ->where('status', 'pending')
            ->get();

        foreach ($dueInstallments as $installment) {
            $installment->generateInvoice();
        }
    }

    public function recordPayment($amount)
    {
        $this->amount_paid += $amount;
        $this->save();

        // Update status if completed
        if ($this->amount_paid >= $this->net_amount) {
            $this->status = 'completed';
            $this->save();
        }

        return $this;
    }

    public function signByStudent($studentId = null)
    {
        $this->student_signatory_id = $studentId ?? auth()->id();
        $this->student_signed_at = now();
        $this->save();

        return $this;
    }

    public function signByParent($parentId)
    {
        $this->parent_signatory_id = $parentId;
        $this->parent_signed_at = now();
        $this->save();

        return $this;
    }

    public function signByInstitution($institutionRepresentativeId)
    {
        $this->institution_signatory_id = $institutionRepresentativeId;
        $this->institution_signed_at = now();
        $this->save();

        return $this;
    }

    public function isFullySigned()
    {
        return $this->student_signed_at &&
               $this->institution_signed_at &&
               ($this->parent_signed_at || $this->student->age >= 18);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->whereHas('installments', function ($q) {
            $q->where('due_date', '<', now())
              ->whereIn('status', ['pending', 'partial']);
        });
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeMonthlyPlans($query)
    {
        return $query->where('plan_type', 'monthly');
    }
}
