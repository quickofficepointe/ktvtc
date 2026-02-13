<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        // Campus Information
        'campus_id',

        // Course Information
        'course_id',
        'intake_period',
        'study_mode',

        // ID Type Information
        'id_type',
        'id_number',

        // Personal Information
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',

        // Contact Information
        'address',
        'city',
        'county',
        'postal_code',
        'country',

        // Education Background
        'education_level',
        'school_name',
        'graduation_year',
        'mean_grade',
        'application_type',

        // Documents
        'id_document',
        'education_certificates',
        'passport_photo',

        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',

        // Special Needs
        'special_needs',

        // Status and Tracking
        'status',
        'application_number',
        'submitted_at',
        'accepted_at',
        'accepted_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',

        // Metadata
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'submitted_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'graduation_year' => 'integer',
    ];

    protected $appends = ['full_name', 'age', 'status_badge', 'status_label', 'id_type_label', 'study_mode_label'];

    /**
     * Get the campus that the application is for.
     */
    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    /**
     * Get the course that the application belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
/**
 * Get the student associated with this application.
 */
public function student()
{
    return $this->hasOne(Student::class, 'application_id');
}
    /**
     * Get the user who accepted the application.
     */
    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    /**
     * Get the user who rejected the application.
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the registration record (if application was accepted).
     */
    public function registration()
    {
        return $this->hasOne(Registration::class);
    }

    /**
     * Get the student user account (if created).
     */
    public function studentAccount()
    {
        return $this->hasOneThrough(
            User::class,
            Registration::class,
            'application_id', // Foreign key on registrations table
            'id', // Foreign key on users table
            'id', // Local key on applications table
            'student_id' // Local key on registrations table
        );
    }

    /**
     * Get the ID type label.
     */
    public function getIdTypeLabelAttribute()
    {
        return match($this->id_type) {
            'id' => 'National ID',
            'birth_certificate' => 'Birth Certificate',
            default => 'Unknown'
        };
    }

    /**
     * Get the study mode label.
     */
    public function getStudyModeLabelAttribute()
    {
        return match($this->study_mode) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'evening' => 'Evening Classes',
            'weekend' => 'Weekend Classes',
            'online' => 'Online',
            default => 'Unknown'
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'under_review' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'waiting_list' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'under_review' => 'Under Review',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'waiting_list' => 'Waiting List',
            default => 'Unknown'
        };
    }

    /**
     * Scope a query to only include pending applications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include reviewed applications.
     */
    public function scopeReviewed($query)
    {
        return $query->whereIn('status', ['under_review', 'accepted', 'rejected', 'waiting_list']);
    }

    /**
     * Scope a query to only include accepted applications.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the full name of the applicant.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the age of the applicant.
     */
    public function getAgeAttribute()
    {
        if ($this->date_of_birth) {
            return now()->diffInYears($this->date_of_birth);
        }
        return null;
    }

    /**
     * Generate student email from application details.
     */
    public function generateStudentEmail()
    {
        $baseEmail = strtolower($this->first_name . '.' . $this->last_name);
        $baseEmail = preg_replace('/[^a-z.]/', '', $baseEmail);

        $email = $baseEmail . '@students.ktvtc.ac.ke';

        // Check for duplicates
        $counter = 1;
        $originalEmail = $email;

        while (\App\Models\User::where('email', $email)->exists() ||
               \App\Models\Registration::where('official_email', $email)->exists()) {
            $email = $baseEmail . $counter . '@students.ktvtc.ac.ke';
            $counter++;
        }

        return $email;
    }

    /**
     * Generate KTVTC registration number.
     */
    public function generateRegistrationNumber()
    {
        $campusCode = $this->campus->code ?? 'GEN';
        $courseCode = $this->course->code ?? 'GEN';
        $year = date('Y');
        $month = date('m'); // Current month for monthly intake

        // Get intake month from application or use current
        $intakeMonth = $this->intake_period ?? strtolower(date('F'));
        $monthNumber = date('m', strtotime($intakeMonth));

        // Get next sequence for this campus/course/year/month
        $lastReg = \App\Models\Registration::where('campus_id', $this->campus_id)
            ->where('course_id', $this->course_id)
            ->where('academic_year', $year)
            ->where('intake_month', $intakeMonth)
            ->orderBy('registration_number', 'desc')
            ->first();

        $sequence = $lastReg ?
            (int) substr($lastReg->registration_number, -4) + 1 : 1;

        return sprintf('KTVTC/REG/%s/%s/%s/%04d',
            $year,
            $monthNumber,
            $campusCode,
            $courseCode,
            $sequence
        );
    }

    /**
     * Generate student number.
     */
    public function generateStudentNumber()
    {
        $year = date('y'); // Last 2 digits of year

        $lastStudent = \App\Models\Registration::whereNotNull('student_number')
            ->where('student_number', 'like', "KTVTC/STU/{$year}/%")
            ->orderBy('student_number', 'desc')
            ->first();

        $sequence = $lastStudent ?
            (int) substr($lastStudent->student_number, -5) + 1 : 1;

        return sprintf('KTVTC/STU/%s/%05d', $year, $sequence);
    }

    /**
     * Accept the application and create registration.
     */
    public function accept($acceptedBy = null, $data = [])
    {
        DB::transaction(function () use ($acceptedBy, $data) {
            // Update application status
            $this->status = 'accepted';
            $this->accepted_at = now();
            $this->accepted_by = $acceptedBy ?? auth()->id();
            $this->save();

            // Generate student email
            $studentEmail = $this->generateStudentEmail();

            // Create or get student user account
            $studentUser = $this->createStudentAccount($studentEmail);

            // Create registration record
            $registration = Registration::create([
                'application_id' => $this->id,
                'student_id' => $studentUser->id,
                'campus_id' => $this->campus_id,
                'course_id' => $this->course_id,
                'registration_number' => $this->generateRegistrationNumber(),
                'student_number' => $this->generateStudentNumber(),
                'official_email' => $studentEmail,
                'academic_year' => date('Y'),
                'intake_month' => $this->intake_period ?? strtolower(date('F')),
                'start_date' => now(),
                'expected_completion_date' => now()->addMonths($this->course->duration_months ?? 6),
                'total_course_months' => $this->course->duration_months ?? 6,
                'study_mode' => $this->study_mode,
                'registration_date' => now(),
                'registration_deadline' => now()->addDays(14),
                'monthly_due_day' => 5,
                'status' => 'provisional',
                'requirements_checklist' => json_encode([
                    'documents_verified' => false,
                    'registration_fee_paid' => false,
                    'medical_check_done' => false,
                    'cdacc_registered' => false,
                    'orientation_attended' => false,
                    'student_id_collected' => false,
                ]),
                'processed_by' => $acceptedBy ?? auth()->id(),
            ]);

            // Get fee structure for this course/campus
            $feeStructure = FeeStructure::where('course_id', $this->course_id)
                ->where('campus_id', $this->campus_id)
                ->where('academic_year', date('Y'))
                ->where('intake_month', $this->intake_period ?? strtolower(date('F')))
                ->where('is_active', true)
                ->first();

            if ($feeStructure) {
                $registration->fee_structure_id = $feeStructure->id;

                // Update registration with fee amounts
                $registration->registration_fee = $feeStructure->registration_fee;
                $registration->tuition_per_month = $feeStructure->tuition_per_month;
                $registration->caution_money = $feeStructure->caution_money;
                $registration->cdacc_registration_fee = $feeStructure->cdacc_registration_fee;
                $registration->cdacc_examination_fee = $feeStructure->cdacc_examination_fee;
                $registration->total_course_fee = $feeStructure->total_course_fee;
                $registration->balance = $feeStructure->total_course_fee;
                $registration->save();

                // Generate student fees
                $this->generateStudentFees($registration, $feeStructure);
            }

            // Create CDACC registration if applicable
            if ($feeStructure && $feeStructure->cdacc_program_code) {
                $this->createCdaccRegistration($registration, $feeStructure);
            }

            // Create student details record
            StudentDetail::create([
                'student_id' => $studentUser->id,
                'next_of_kin_name' => $this->emergency_contact_name,
                'next_of_kin_relationship' => $this->emergency_contact_relationship,
                'next_of_kin_phone' => $this->emergency_contact_phone,
                'next_of_kin_address' => $this->address,
                'secondary_school_name' => $this->school_name,
                'secondary_school_completion_year' => $this->graduation_year,
                'kcse_mean_grade' => $this->mean_grade,
                'special_needs' => $this->special_needs,
                'created_by' => $acceptedBy ?? auth()->id(),
            ]);

            // Send notification
            $this->sendAcceptanceNotification($registration, $studentUser);
        });

        return $this;
    }

    /**
     * Create student user account.
     */
    private function createStudentAccount($email)
    {
        // Check if user already exists with this personal email
        $existingUser = User::where('email', $this->email)->first();

        if ($existingUser) {
            // Update existing user with student details
            $existingUser->update([
                'student_number' => $this->generateStudentNumber(),
                'role' => 'student',
                'campus_id' => $this->campus_id,
            ]);
            return $existingUser;
        }

        // Create new student user
        $password = Str::random(12);

        $studentUser = User::create([
            'name' => $this->first_name . ' ' . $this->last_name,
            'email' => $email, // Official KTVTC email
            'personal_email' => $this->email, // Personal email from application
            'phone' => $this->phone,
            'password' => bcrypt($password),
            'student_number' => $this->generateStudentNumber(),
            'role' => 'student',
            'campus_id' => $this->campus_id,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'email_verified_at' => now(),
            'temp_password' => $password, // Store temporarily for email
            'must_change_password' => true,
        ]);

        return $studentUser;
    }

    /**
     * Generate student fees from fee structure.
     */
    private function generateStudentFees($registration, $feeStructure)
    {
        // Create registration fee (first month only)
        StudentFee::create([
            'student_id' => $registration->student_id,
            'registration_id' => $registration->id,
            'fee_structure_id' => $feeStructure->id,
            'description' => 'Registration Fee',
            'fee_category' => 'registration',
            'fee_type' => 'one_time',
            'amount' => $feeStructure->registration_fee,
            'due_date' => now()->addDays(7),
            'invoice_date' => now(),
            'payment_status' => 'pending',
            'month_number' => 1,
            'billing_month' => ucfirst($registration->intake_month),
            'academic_year' => $registration->academic_year,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Create caution money (refundable)
        if ($feeStructure->caution_money > 0) {
            StudentFee::create([
                'student_id' => $registration->student_id,
                'registration_id' => $registration->id,
                'fee_structure_id' => $feeStructure->id,
                'description' => 'Caution Money (Refundable)',
                'fee_category' => 'caution_money',
                'fee_type' => 'refundable',
                'amount' => $feeStructure->caution_money,
                'due_date' => now()->addDays(7),
                'invoice_date' => now(),
                'payment_status' => 'pending',
                'is_refundable' => true,
                'month_number' => 1,
                'billing_month' => ucfirst($registration->intake_month),
                'academic_year' => $registration->academic_year,
                'created_by' => auth()->id() ?? 1,
            ]);
        }

        // Create CDACC registration fee
        if ($feeStructure->cdacc_registration_fee > 0) {
            StudentFee::create([
                'student_id' => $registration->student_id,
                'registration_id' => $registration->id,
                'fee_structure_id' => $feeStructure->id,
                'description' => 'CDACC Registration Fee',
                'fee_category' => 'certification',
                'fee_type' => 'one_time',
                'amount' => $feeStructure->cdacc_registration_fee,
                'due_date' => now()->addDays(30),
                'invoice_date' => now(),
                'payment_status' => 'pending',
                'is_cdacc_fee' => true,
                'cdacc_status' => 'pending',
                'month_number' => 1,
                'billing_month' => ucfirst($registration->intake_month),
                'academic_year' => $registration->academic_year,
                'created_by' => auth()->id() ?? 1,
            ]);
        }

        // Generate monthly fees for the course duration
        for ($month = 1; $month <= $feeStructure->total_course_months; $month++) {
            $monthName = date('F', strtotime("+ " . ($month - 1) . " months", strtotime($registration->start_date)));

            $monthlyTotal = $feeStructure->calculateMonthlyTotal();

            // Special handling for first month (already has registration fee)
            if ($month === 1) {
                $monthlyTotal -= $feeStructure->registration_fee;
            }

            // Special handling for last month (add examination fees)
            if ($month === $feeStructure->total_course_months) {
                $monthlyTotal += $feeStructure->calculateFinalMonthFees();

                // Add CDACC examination fee separately
                if ($feeStructure->cdacc_examination_fee > 0) {
                    StudentFee::create([
                        'student_id' => $registration->student_id,
                        'registration_id' => $registration->id,
                        'fee_structure_id' => $feeStructure->id,
                        'description' => 'CDACC Examination Fee',
                        'fee_category' => 'examination',
                        'fee_type' => 'one_time',
                        'amount' => $feeStructure->cdacc_examination_fee,
                        'due_date' => now()->addMonths($month - 1)->addDays(7),
                        'invoice_date' => now(),
                        'payment_status' => 'pending',
                        'is_cdacc_fee' => true,
                        'cdacc_status' => 'pending',
                        'month_number' => $month,
                        'billing_month' => $monthName,
                        'academic_year' => $registration->academic_year,
                        'created_by' => auth()->id() ?? 1,
                    ]);
                }
            }

            if ($monthlyTotal > 0) {
                StudentFee::create([
                    'student_id' => $registration->student_id,
                    'registration_id' => $registration->id,
                    'fee_structure_id' => $feeStructure->id,
                    'description' => "Tuition and Fees - {$monthName}",
                    'detailed_description' => "Monthly tuition, workshop levy, and other charges for {$monthName}",
                    'fee_category' => 'tuition',
                    'fee_type' => 'recurring',
                    'amount' => $monthlyTotal,
                    'due_date' => now()->addMonths($month - 1)->addDays(7),
                    'invoice_date' => now(),
                    'payment_status' => 'pending',
                    'month_number' => $month,
                    'billing_month' => $monthName,
                    'academic_year' => $registration->academic_year,
                    'created_by' => auth()->id() ?? 1,
                ]);
            }
        }
    }

    /**
     * Create CDACC registration.
     */
    private function createCdaccRegistration($registration, $feeStructure)
    {
        CdaccRegistration::create([
            'registration_id' => $registration->id,
            'student_id' => $registration->student_id,
            'course_id' => $registration->course_id,
            'fee_structure_id' => $feeStructure->id,
            'cdacc_program_code' => $feeStructure->cdacc_program_code,
            'cdacc_program_name' => $registration->course->name,
            'cdacc_qualification_title' => $registration->course->name,
            'cdacc_qualification_level' => $feeStructure->tvet_qualification_type,
            'cdacc_trade_area' => $registration->course->department->name ?? 'General',
            'cdacc_registration_date' => now(),
            'cdacc_registration_expiry' => now()->addYears(2),
            'cdacc_center_number' => 'CDACC-CENTER-001', // Should be configured
            'cdacc_center_name' => $registration->campus->name,
            'cdacc_registration_fee' => $feeStructure->cdacc_registration_fee,
            'cdacc_examination_fee' => $feeStructure->cdacc_examination_fee,
            'cdacc_certification_fee' => $feeStructure->cdacc_certification_fee,
            'cdacc_total_fee' => $feeStructure->cdacc_registration_fee +
                                 $feeStructure->cdacc_examination_fee +
                                 $feeStructure->cdacc_certification_fee,
            'cdacc_status' => 'pending',
            'processed_by' => auth()->id() ?? 1,
        ]);
    }

    /**
     * Send acceptance notification.
     */
    private function sendAcceptanceNotification($registration, $studentUser)
    {
        // Send email to student
        // Mail::to($studentUser->email)->send(new ApplicationAcceptedMail($this, $registration));

        // Send SMS notification
        // SMS::send($this->phone, "Your application to KTVTC has been accepted. Registration No: {$registration->registration_number}");

        // Log the acceptance
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log("Application accepted and registration created: {$registration->registration_number}");
    }

    /**
     * Reject the application.
     */
    public function reject($rejectedBy = null, $reason = null)
    {
        $this->status = 'rejected';
        $this->rejected_at = now();
        $this->rejected_by = $rejectedBy ?? auth()->id();
        $this->rejection_reason = $reason;
        $this->save();

        // Send rejection notification
        // Mail::to($this->email)->send(new ApplicationRejectedMail($this));

        return $this;
    }

    /**
     * Check if application can be accepted.
     */
    public function canBeAccepted()
    {
        return $this->status === 'pending' || $this->status === 'under_review' || $this->status === 'waiting_list';
    }

    /**
     * Boot function for handling model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            // Generate application number if not set
            if (empty($application->application_number)) {
                $application->application_number = 'APP-' . date('Y') . '-' . strtoupper(uniqid());
            }

            // Set submitted_at timestamp
            if (empty($application->submitted_at)) {
                $application->submitted_at = now();
            }
        });
    }
}
