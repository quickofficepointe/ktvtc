<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_id',
        'student_id',
        'campus_id',
        'course_id',
        'fee_structure_id',
        'registration_number',
        'student_number',
        'official_email',
        'academic_year',
        'intake_month',
        'start_date',
        'expected_completion_date',
        'actual_completion_date',
        'total_course_months',
        'current_month',
        'study_mode',
        'registration_fee',
        'tuition_per_month',
        'caution_money',
        'cdacc_registration_fee',
        'cdacc_examination_fee',
        'total_course_fee',
        'amount_paid',
        'balance',
        'payment_plan',
        'monthly_payments',
        'cdacc_index_number',
        'cdacc_registration_number',
        'cdacc_registration_date',
        'cdacc_status',
        'cdacc_fee_paid',
        'status',
        'requirements_checklist',
        'documents_submitted',
        'processed_by',
        'academic_advisor_id',
        'registration_date',
        'monthly_due_day',
        'admission_letter_path',
        'fee_structure_path',
        'cdacc_registration_form_path',
        'student_id_card_path',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'cdacc_registration_date' => 'date',
        'registration_date' => 'date',
        'monthly_payments' => 'array',
        'requirements_checklist' => 'array',
        'documents_submitted' => 'array',
        'cdacc_fee_paid' => 'boolean',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'total_course_fee' => 'decimal:2',
        'registration_fee' => 'decimal:2',
        'tuition_per_month' => 'decimal:2',
        'caution_money' => 'decimal:2',
        'cdacc_registration_fee' => 'decimal:2',
        'cdacc_examination_fee' => 'decimal:2',
    ];

    protected $appends = [
        'is_active',
        'is_overdue',
        'can_proceed_to_next_month',
        'payment_status',
        'completion_percentage',
        'months_remaining',
        'next_payment_due_date'
    ];

    // Static boot method for auto-generating numbers
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            if (empty($registration->registration_number)) {
                $registration->registration_number = self::generateRegistrationNumber($registration);
            }

            if (empty($registration->student_number)) {
                $registration->student_number = self::generateStudentNumber();
            }

            if (empty($registration->registration_date)) {
                $registration->registration_date = now();
            }

            if (empty($registration->start_date)) {
                $registration->start_date = now();
            }

            if (empty($registration->expected_completion_date) && $registration->total_course_months) {
                $registration->expected_completion_date = now()->addMonths($registration->total_course_months);
            }
        });

        static::created(function ($registration) {
            // Initialize monthly payments if not set
            if (empty($registration->monthly_payments) && $registration->total_course_months > 0) {
                $registration->initializeMonthlyPayments();
            }

            // Initialize requirements checklist if not set
            if (empty($registration->requirements_checklist)) {
                $registration->initializeRequirementsChecklist();
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function academicAdvisor()
    {
        return $this->belongsTo(User::class, 'academic_advisor_id');
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }

    public function paymentPlan()
    {
        return $this->hasOne(PaymentPlan::class);
    }

    public function cdaccRegistration()
    {
        return $this->hasOne(CdaccRegistration::class);
    }

    public function studentDetail()
    {
        return $this->hasOneThrough(
            StudentDetail::class,
            User::class,
            'id', // Foreign key on users table
            'student_id', // Foreign key on student_details table
            'student_id', // Local key on registrations table
            'id' // Local key on users table
        );
    }

    // ==================== ACCESSORS ====================

    public function getIsActiveAttribute()
    {
        return $this->status === 'active' || $this->status === 'registered';
    }

    public function getIsOverdueAttribute()
    {
        // Check if current month payment is overdue
        $currentMonth = 'month_' . $this->current_month;
        $payments = $this->monthly_payments ?? [];

        if (isset($payments[$currentMonth])) {
            $dueDate = $payments[$currentMonth]['due_date'] ?? null;
            if ($dueDate && strtotime($dueDate) < time()) {
                return $payments[$currentMonth]['status'] !== 'paid';
            }
        }

        return false;
    }

    public function getCanProceedToNextMonthAttribute()
    {
        // Check if current month payment is paid
        $currentMonth = 'month_' . $this->current_month;
        $payments = $this->monthly_payments ?? [];

        if (isset($payments[$currentMonth])) {
            return $payments[$currentMonth]['status'] === 'paid';
        }

        return false;
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->balance <= 0) {
            return 'paid';
        } elseif ($this->amount_paid > 0) {
            return 'partial';
        } elseif ($this->is_overdue) {
            return 'overdue';
        } else {
            return 'pending';
        }
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->total_course_months <= 0) {
            return 0;
        }

        $percentage = ($this->current_month / $this->total_course_months) * 100;
        return min(100, round($percentage, 2));
    }

    public function getMonthsRemainingAttribute()
    {
        return max(0, $this->total_course_months - $this->current_month);
    }

    public function getNextPaymentDueDateAttribute()
    {
        $currentMonth = 'month_' . $this->current_month;
        $payments = $this->monthly_payments ?? [];

        if (isset($payments[$currentMonth])) {
            return $payments[$currentMonth]['due_date'] ?? null;
        }

        // Calculate next due date
        $nextMonth = $this->current_month + 1;
        if ($nextMonth <= $this->total_course_months) {
            return date('Y-m-' . $this->monthly_due_day, strtotime("+{$this->current_month} months", strtotime($this->start_date)));
        }

        return null;
    }

    // ==================== STATIC METHODS ====================

    public static function generateRegistrationNumber($registration)
    {
        $campusCode = $registration->campus->code ?? 'GEN';
        $courseCode = $registration->course->code ?? 'GEN';
        $year = $registration->academic_year ?? date('Y');

        // Convert intake month to number
        $monthNumber = date('m', strtotime($registration->intake_month ?? 'january'));

        // Get next sequence
        $lastReg = self::where('campus_id', $registration->campus_id)
            ->where('course_id', $registration->course_id)
            ->where('academic_year', $year)
            ->where('intake_month', $registration->intake_month)
            ->orderBy('registration_number', 'desc')
            ->first();

        $sequence = $lastReg ?
            (int) substr($lastReg->registration_number, -4) + 1 : 1;

        return sprintf('KTVTC/REG/%s/%s/%s/%04d',
            $year,
            str_pad($monthNumber, 2, '0', STR_PAD_LEFT),
            $campusCode,
            $courseCode,
            $sequence
        );
    }

    public static function generateStudentNumber()
    {
        $year = date('y');

        $lastStudent = self::whereNotNull('student_number')
            ->where('student_number', 'like', "KTVTC/STU/{$year}/%")
            ->orderBy('student_number', 'desc')
            ->first();

        $sequence = $lastStudent ?
            (int) substr($lastStudent->student_number, -5) + 1 : 1;

        return sprintf('KTVTC/STU/%s/%05d', $year, $sequence);
    }

    // ==================== BUSINESS LOGIC METHODS ====================

    public function initializeMonthlyPayments()
    {
        $payments = [];
        $startDate = $this->start_date;

        for ($month = 1; $month <= $this->total_course_months; $month++) {
            // Calculate due date (e.g., 5th of each month)
            $dueDate = date('Y-m-' . $this->monthly_due_day, strtotime("+" . ($month - 1) . " months", strtotime($startDate)));

            $payments['month_' . $month] = [
                'due_date' => $dueDate,
                'amount' => $this->calculateMonthlyAmount($month),
                'status' => 'pending',
                'paid_date' => null,
                'invoice_number' => null,
            ];
        }

        $this->monthly_payments = $payments;
        $this->save();
    }

    private function calculateMonthlyAmount($monthNumber)
    {
        $baseAmount = $this->tuition_per_month;

        // First month includes registration fee
        if ($monthNumber === 1) {
            $baseAmount += $this->registration_fee;
        }

        // Last month includes CDACC examination fee
        if ($monthNumber === $this->total_course_months) {
            $baseAmount += $this->cdacc_examination_fee;
        }

        return $baseAmount;
    }

    public function initializeRequirementsChecklist()
    {
        $checklist = [
            'documents_verified' => false,
            'registration_fee_paid' => false,
            'medical_check_done' => false,
            'cdacc_registered' => false,
            'orientation_attended' => false,
            'student_id_collected' => false,
            'fee_structure_signed' => false,
            'caution_money_paid' => false,
            'workshop_safety_training' => false,
        ];

        $this->requirements_checklist = $checklist;
        $this->save();
    }

    public function updateRequirement($requirement, $status)
    {
        $checklist = $this->requirements_checklist ?? [];
        $checklist[$requirement] = $status;
        $this->requirements_checklist = $checklist;
        $this->save();

        // Check if all requirements are met to update status
        $this->checkRequirementsCompletion();
    }

    public function checkRequirementsCompletion()
    {
        $checklist = $this->requirements_checklist ?? [];
        $allComplete = true;

        foreach ($checklist as $requirement => $status) {
            if (!$status) {
                $allComplete = false;
                break;
            }
        }

        if ($allComplete && $this->status === 'provisional') {
            $this->status = 'registered';
            $this->save();
        }
    }

    public function recordPayment($amount, $description = null)
    {
        $this->amount_paid += $amount;
        $this->balance = max(0, $this->total_course_fee - $this->amount_paid);
        $this->save();

        // Update current month payment status if applicable
        $this->updateCurrentMonthPayment($amount);

        return $this;
    }

    private function updateCurrentMonthPayment($amount)
    {
        $currentMonth = 'month_' . $this->current_month;
        $payments = $this->monthly_payments ?? [];

        if (isset($payments[$currentMonth])) {
            $monthlyAmount = $payments[$currentMonth]['amount'] ?? 0;

            // For simplicity, mark as paid if any payment received
            // In real system, track partial payments per month
            $payments[$currentMonth]['status'] = 'paid';
            $payments[$currentMonth]['paid_date'] = now()->format('Y-m-d');

            $this->monthly_payments = $payments;
            $this->save();
        }
    }

    public function advanceToNextMonth()
    {
        if (!$this->can_proceed_to_next_month) {
            throw new \Exception('Cannot advance to next month. Current month payment not cleared.');
        }

        if ($this->current_month < $this->total_course_months) {
            $this->current_month += 1;
            $this->save();
            return true;
        }

        return false;
    }

    public function completeRegistration()
    {
        if ($this->checkRequirementsCompletion()) {
            $this->status = 'active';
            $this->save();
            return true;
        }

        return false;
    }

    public function generateAdmissionLetter()
    {
        // This would generate a PDF admission letter
        // For now, return a placeholder path
        $filename = 'admission_letter_' . $this->registration_number . '_' . time() . '.pdf';
        $path = 'admission_letters/' . $filename;

        $this->admission_letter_path = $path;
        $this->save();

        return $path;
    }

    public function generateFeeStructure()
    {
        // Generate fee structure document
        $filename = 'fee_structure_' . $this->registration_number . '_' . time() . '.pdf';
        $path = 'fee_structures/' . $filename;

        $this->fee_structure_path = $path;
        $this->save();

        return $path;
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'registered']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProvisional($query)
    {
        return $query->where('status', 'provisional');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'behind_payment')
            ->orWhere(function ($q) {
                $q->where('balance', '>', 0)
                  ->whereRaw('DATEDIFF(NOW(), start_date) > 30');
            });
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

    public function scopeWithCdaccRegistration($query)
    {
        return $query->whereNotNull('cdacc_index_number')
                     ->where('cdacc_status', '!=', 'pending');
    }

    public function scopePaymentComplete($query)
    {
        return $query->where('balance', '<=', 0);
    }

    // ==================== HELPER METHODS ====================

    public function getMonthlyPaymentStatus($monthNumber)
    {
        $monthKey = 'month_' . $monthNumber;
        $payments = $this->monthly_payments ?? [];

        return $payments[$monthKey] ?? null;
    }

    public function getRemainingRequirements()
    {
        $checklist = $this->requirements_checklist ?? [];
        $remaining = [];

        foreach ($checklist as $requirement => $status) {
            if (!$status) {
                $remaining[] = $requirement;
            }
        }

        return $remaining;
    }

    public function isRequirementComplete($requirement)
    {
        $checklist = $this->requirements_checklist ?? [];
        return $checklist[$requirement] ?? false;
    }

    public function getProgressSummary()
    {
        return [
            'current_month' => $this->current_month,
            'total_months' => $this->total_course_months,
            'completion_percentage' => $this->completion_percentage,
            'months_remaining' => $this->months_remaining,
            'payment_status' => $this->payment_status,
            'amount_paid' => $this->amount_paid,
            'balance' => $this->balance,
            'total_fee' => $this->total_course_fee,
            'next_payment_due' => $this->next_payment_due_date,
            'requirements_complete' => count(array_filter($this->requirements_checklist ?? [])),
            'requirements_total' => count($this->requirements_checklist ?? []),
        ];
    }
}
