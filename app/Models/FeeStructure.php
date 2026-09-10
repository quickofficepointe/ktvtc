<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'campus_id',
        'cdacc_program_code',
        'tvet_qualification_type',
        'academic_year',
        'intake_month',
        'total_course_months',
        'course_duration_type',
        'registration_fee',
        'tuition_per_month',
        'caution_money',
        'student_id_fee',
        'library_fee',
        'medical_fee',
        'sports_fee',
        'activity_fee',
        'workshop_levy',
        'practical_materials',
        'tool_kit_deposit',
        'protective_clothing',
        'industrial_attachment_fee',
        'cdacc_registration_fee',
        'cdacc_examination_fee',
        'cdacc_certification_fee',
        'tvet_authority_levy',
        'trade_test_fee',
        'monthly_total',
        'one_time_fees',
        'final_month_fees',
        'total_course_fee',
        'payment_plans',
        'has_government_sponsorship',
        'government_subsidy_amount',
        'sponsorship_type',
        'valid_from',
        'valid_to',
        'is_active',
        'is_approved',
        'approved_by',
        'grace_period_days',
        'late_fee_percentage',
        'suspension_days',
    ];

    protected $casts = [
        'payment_plans' => 'array',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'has_government_sponsorship' => 'boolean',
        'registration_fee' => 'decimal:2',
        'tuition_per_month' => 'decimal:2',
        'caution_money' => 'decimal:2',
        'student_id_fee' => 'decimal:2',
        'library_fee' => 'decimal:2',
        'medical_fee' => 'decimal:2',
        'sports_fee' => 'decimal:2',
        'activity_fee' => 'decimal:2',
        'workshop_levy' => 'decimal:2',
        'practical_materials' => 'decimal:2',
        'tool_kit_deposit' => 'decimal:2',
        'protective_clothing' => 'decimal:2',
        'industrial_attachment_fee' => 'decimal:2',
        'cdacc_registration_fee' => 'decimal:2',
        'cdacc_examination_fee' => 'decimal:2',
        'cdacc_certification_fee' => 'decimal:2',
        'tvet_authority_levy' => 'decimal:2',
        'trade_test_fee' => 'decimal:2',
        'monthly_total' => 'decimal:2',
        'one_time_fees' => 'decimal:2',
        'final_month_fees' => 'decimal:2',
        'total_course_fee' => 'decimal:2',
        'government_subsidy_amount' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('valid_from', '<=', now())
                     ->where('valid_to', '>=', now());
    }

    public function scopeForCampus($query, $campusId)
    {
        return $query->where('campus_id', $campusId);
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeForIntake($query, $year, $month)
    {
        return $query->where('academic_year', $year)
                     ->where('intake_month', $month);
    }

    public function scopeCdaccProgram($query, $programCode)
    {
        return $query->where('cdacc_program_code', $programCode);
    }

    // Business Logic Methods
    public function calculateMonthlyTotal()
    {
        return $this->tuition_per_month
             + $this->workshop_levy
             + $this->library_fee
             + $this->medical_fee
             + $this->sports_fee
             + $this->activity_fee;
    }

    public function calculateOneTimeFees()
    {
        return $this->registration_fee
             + $this->caution_money
             + $this->student_id_fee
             + $this->cdacc_registration_fee
             + $this->tool_kit_deposit
             + $this->protective_clothing;
    }

    public function calculateFinalMonthFees()
    {
        return $this->cdacc_examination_fee
             + $this->cdacc_certification_fee
             + $this->trade_test_fee;
    }

    public function calculateTotalCourseFee()
    {
        $monthlyTotal = $this->calculateMonthlyTotal();
        $oneTimeFees = $this->calculateOneTimeFees();
        $finalFees = $this->calculateFinalMonthFees();

        // Monthly total for all months except first and last (if they have special fees)
        $regularMonths = $this->total_course_months > 2 ? $this->total_course_months - 2 : 0;
        $monthlyPayments = $monthlyTotal * max(0, $regularMonths);

        return $oneTimeFees + $monthlyPayments + $finalFees;
    }

    public function updateCalculatedFields()
    {
        $this->monthly_total = $this->calculateMonthlyTotal();
        $this->one_time_fees = $this->calculateOneTimeFees();
        $this->final_month_fees = $this->calculateFinalMonthFees();
        $this->total_course_fee = $this->calculateTotalCourseFee();
        $this->save();
    }

    public function getStudentMonthlyFee()
    {
        if ($this->has_government_sponsorship) {
            return max(0, $this->monthly_total - $this->government_subsidy_amount);
        }
        return $this->monthly_total;
    }

    public function getAvailablePaymentPlans()
    {
        $defaultPlans = [
            [
                'name' => 'Full Course Payment',
                'type' => 'full_course',
                'discount' => 5.00,
                'description' => 'Pay entire course fee upfront and get 5% discount'
            ],
            [
                'name' => 'Monthly Installments',
                'type' => 'monthly',
                'installments' => $this->total_course_months,
                'description' => 'Pay monthly throughout the course duration'
            ],
            [
                'name' => 'Quarterly Payments',
                'type' => 'quarterly',
                'installments' => ceil($this->total_course_months / 3),
                'description' => 'Pay every 3 months'
            ],
        ];

        return array_merge($defaultPlans, $this->payment_plans ?? []);
    }

    public function getLateFeeAmount($amount, $daysOverdue)
    {
        if ($daysOverdue <= $this->grace_period_days) {
            return 0;
        }

        return $amount * ($this->late_fee_percentage / 100);
    }
}
