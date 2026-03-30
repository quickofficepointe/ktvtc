<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class StudentDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'national_id_number',
        'passport_number',
        'birth_certificate_number',
        'kra_pin',
        'nssf_number',
        'nhif_number',
        'personal_email',
        'personal_phone',
        'alternative_phone',
        'postal_address',
        'postal_code',
        'town',
        'county',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_phone',
        'next_of_kin_email',
        'next_of_kin_address',
        'next_of_kin_id_number',
        'next_of_kin_occupation',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'emergency_contact_alternate_phone',
        'emergency_contact_address',
        'secondary_school_name',
        'secondary_school_completion_year',
        'kcse_index_number',
        'kcse_mean_grade',
        'secondary_school_address',
        'primary_school_name',
        'primary_school_completion_year',
        'knec_index_primary',
        'primary_school_address',
        'previous_institution',
        'previous_course',
        'previous_start_year',
        'previous_end_year',
        'previous_qualification',
        'transfer_reason',
        'previous_transcript_path',
        'is_employed',
        'employer_name',
        'employer_address',
        'employer_phone',
        'job_title',
        'employment_duration',
        'employer_email',
        'blood_group',
        'medical_conditions',
        'allergies',
        'chronic_illnesses',
        'disabilities',
        'special_needs',
        'doctor_name',
        'doctor_phone',
        'doctor_address',
        'medical_insurance_provider',
        'medical_insurance_number',
        'sponsorship_type',
        'sponsor_name',
        'sponsor_id_number',
        'sponsor_phone',
        'sponsor_email',
        'sponsor_address',
        'sponsor_relationship',
        'sponsor_occupation',
        'sponsorship_letter_path',
        'has_helb_loan',
        'helb_loan_number',
        'helb_disbursement_code',
        'helb_loan_amount',
        'helb_bank_name',
        'helb_bank_account',
        'helb_application_date',
        'helb_approval_date',
        'helb_letter_path',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'documents_uploaded',
        'workshop_safety_training_date',
        'workshop_safety_certificate_path',
        'tool_kit_issued',
        'tool_kit_issue_date',
        'tool_kit_return_date',
        'protective_clothing_issued',
        'protective_clothing_size',
        'workshop_access_level',
        'industrial_attachment_company',
        'industrial_attachment_supervisor',
        'industrial_attachment_phone',
        'industrial_attachment_email',
        'industrial_attachment_start_date',
        'industrial_attachment_end_date',
        'industrial_attachment_address',
        'industrial_attachment_report_path',
        'industrial_attachment_supervisor_report_path',
        'industrial_attachment_status',
        'hobbies_interests',
        'sports_activities',
        'clubs_societies',
        'leadership_positions',
        'awards_achievements',
        'cumulative_gpa',
        'current_class_position',
        'attendance_percentage',
        'academic_notes',
        'disciplinary_records',
        'employment_status_after_graduation',
        'employer_after_graduation',
        'job_title_after_graduation',
        'starting_salary',
        'graduation_year',
        'is_alumni',
        'alumni_membership_date',
        'created_by',
        'updated_by',
        'last_updated_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'secondary_school_completion_year' => 'integer',
        'primary_school_completion_year' => 'integer',
        'previous_start_year' => 'integer',
        'previous_end_year' => 'integer',
        'is_employed' => 'boolean',
        'has_helb_loan' => 'boolean',
        'is_alumni' => 'boolean',
        'helb_loan_amount' => 'decimal:2',
        'starting_salary' => 'decimal:2',
        'cumulative_gpa' => 'decimal:2',
        'workshop_safety_training_date' => 'date',
        'tool_kit_issue_date' => 'date',
        'tool_kit_return_date' => 'date',
        'industrial_attachment_start_date' => 'date',
        'industrial_attachment_end_date' => 'date',
        'alumni_membership_date' => 'date',
        'helb_application_date' => 'date',
        'helb_approval_date' => 'date',
        'last_updated_at' => 'datetime',
        'documents_uploaded' => 'array',
        'hobbies_interests' => 'array',
        'sports_activities' => 'array',
        'clubs_societies' => 'array',
        'leadership_positions' => 'array',
        'awards_achievements' => 'array',
        'disciplinary_records' => 'array',
        'metadata' => 'array',
    ];

    protected $appends = [
        'age',
        'is_minor',
        'full_address',
        'documents_completion_percentage',
        'has_complete_documents',
        'industrial_attachment_duration',
        'is_attachment_active'
    ];

    // ==================== RELATIONSHIPS ====================

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function registration()
    {
        return $this->hasOneThrough(
            Registration::class,
            User::class,
            'id', // Foreign key on users table
            'student_id', // Foreign key on registrations table
            'student_id', // Local key on student_details table
            'id' // Local key on users table
        );
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== ACCESSORS ====================

    public function getAgeAttribute()
    {
        $student = $this->student;
        if ($student && $student->date_of_birth) {
            return now()->diffInYears($student->date_of_birth);
        }
        return null;
    }

    public function getIsMinorAttribute()
    {
        return $this->age !== null && $this->age < 18;
    }

    public function getFullAddressAttribute()
    {
        $parts = [];
        if ($this->postal_address) $parts[] = $this->postal_address;
        if ($this->town) $parts[] = $this->town;
        if ($this->county) $parts[] = $this->county;
        if ($this->postal_code) $parts[] = $this->postal_code;

        return implode(', ', $parts);
    }

    public function getDocumentsCompletionPercentageAttribute()
    {
        $documents = $this->documents_uploaded ?? [];
        if (empty($documents)) {
            return 0;
        }

        $uploaded = 0;
        foreach ($documents as $document) {
            if (is_array($document) && ($document['uploaded'] ?? false)) {
                $uploaded++;
            } elseif ($document === true) {
                $uploaded++;
            }
        }

        $total = count($documents);
        return $total > 0 ? round(($uploaded / $total) * 100, 0) : 0;
    }

    public function getHasCompleteDocumentsAttribute()
    {
        return $this->documents_completion_percentage >= 100;
    }

    public function getIndustrialAttachmentDurationAttribute()
    {
        if ($this->industrial_attachment_start_date && $this->industrial_attachment_end_date) {
            $start = Carbon::parse($this->industrial_attachment_start_date);
            $end = Carbon::parse($this->industrial_attachment_end_date);
            return $start->diffInMonths($end);
        }
        return null;
    }

    public function getIsAttachmentActiveAttribute()
    {
        if (!$this->industrial_attachment_start_date || !$this->industrial_attachment_end_date) {
            return false;
        }

        $now = now();
        $start = Carbon::parse($this->industrial_attachment_start_date);
        $end = Carbon::parse($this->industrial_attachment_end_date);

        return $now->between($start, $end) && $this->industrial_attachment_status === 'ongoing';
    }

    // ==================== BUSINESS LOGIC METHODS ====================

    public function updateDocumentStatus($document, $status = true, $path = null, $verified = false)
    {
        $documents = $this->documents_uploaded ?? [];

        if (is_array($status)) {
            $documents[$document] = $status;
        } else {
            $documents[$document] = [
                'uploaded' => $status,
                'verified' => $verified,
                'path' => $path,
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        $this->documents_uploaded = $documents;
        $this->save();

        return $this;
    }

    public function isDocumentUploaded($document)
    {
        $documents = $this->documents_uploaded ?? [];

        if (isset($documents[$document])) {
            if (is_array($documents[$document])) {
                return $documents[$document]['uploaded'] ?? false;
            }
            return $documents[$document] === true;
        }

        return false;
    }

    public function isDocumentVerified($document)
    {
        $documents = $this->documents_uploaded ?? [];

        if (isset($documents[$document]) && is_array($documents[$document])) {
            return $documents[$document]['verified'] ?? false;
        }

        return false;
    }

    public function getRequiredDocuments()
    {
        return [
            'id_copy' => [
                'name' => 'National ID/Passport/Birth Certificate',
                'mandatory' => true,
                'description' => 'Government issued identification document'
            ],
            'kcse_certificate' => [
                'name' => 'KCSE Certificate/Result Slip',
                'mandatory' => true,
                'description' => 'Original or certified copy of KCSE certificate'
            ],
            'passport_photo' => [
                'name' => 'Passport Photo',
                'mandatory' => true,
                'description' => 'Recent passport size photo (white background)'
            ],
            'medical_certificate' => [
                'name' => 'Medical Certificate',
                'mandatory' => true,
                'description' => 'Medical fitness certificate from registered doctor'
            ],
            'next_of_kin_id' => [
                'name' => 'Next of Kin ID',
                'mandatory' => true,
                'description' => 'Copy of next of kin identification'
            ],
            'sponsorship_letter' => [
                'name' => 'Sponsorship Letter',
                'mandatory' => $this->sponsorship_type !== 'self',
                'description' => 'Official sponsorship letter'
            ],
            'helb_letter' => [
                'name' => 'HELB Letter',
                'mandatory' => $this->has_helb_loan,
                'description' => 'HELB loan award letter'
            ],
            'birth_certificate' => [
                'name' => 'Birth Certificate',
                'mandatory' => $this->age < 18,
                'description' => 'For students below 18 years'
            ],
            'recommendation_letter' => [
                'name' => 'Recommendation Letter',
                'mandatory' => false,
                'description' => 'Academic or professional recommendation'
            ],
        ];
    }

    public function recordIndustrialAttachment($company, $supervisor, $phone, $email, $startDate, $endDate, $address = null)
    {
        $this->industrial_attachment_company = $company;
        $this->industrial_attachment_supervisor = $supervisor;
        $this->industrial_attachment_phone = $phone;
        $this->industrial_attachment_email = $email;
        $this->industrial_attachment_start_date = $startDate;
        $this->industrial_attachment_end_date = $endDate;
        $this->industrial_attachment_address = $address;
        $this->industrial_attachment_status = 'ongoing';
        $this->save();

        return $this;
    }

    public function completeIndustrialAttachment()
    {
        $this->industrial_attachment_status = 'completed';
        $this->save();

        return $this;
    }

    public function issueWorkshopEquipment($toolKit, $protectiveClothing, $size = null)
    {
        $this->tool_kit_issued = $toolKit;
        $this->protective_clothing_issued = $protectiveClothing;
        $this->protective_clothing_size = $size;
        $this->tool_kit_issue_date = now();
        $this->workshop_safety_training_date = now();
        $this->save();

        return $this;
    }

    public function returnToolKit($returnDate = null)
    {
        $this->tool_kit_return_date = $returnDate ?? now();
        $this->save();

        return $this;
    }

    public function addExtracurricularActivity($type, $activity, $position = null, $year = null)
    {
        switch ($type) {
            case 'hobby':
                $hobbies = $this->hobbies_interests ?? [];
                $hobbies[] = $activity;
                $this->hobbies_interests = $hobbies;
                break;

            case 'sport':
                $sports = $this->sports_activities ?? [];
                $sports[] = $activity;
                $this->sports_activities = $sports;
                break;

            case 'club':
                $clubs = $this->clubs_societies ?? [];
                $clubs[] = $activity;
                $this->clubs_societies = $clubs;
                break;

            case 'leadership':
                $leadership = $this->leadership_positions ?? [];
                $leadership[] = [
                    'position' => $activity,
                    'role' => $position,
                    'year' => $year ?? date('Y')
                ];
                $this->leadership_positions = $leadership;
                break;

            case 'award':
                $awards = $this->awards_achievements ?? [];
                $awards[] = [
                    'award' => $activity,
                    'description' => $position,
                    'year' => $year ?? date('Y')
                ];
                $this->awards_achievements = $awards;
                break;
        }

        $this->save();
        return $this;
    }

    public function recordHelbLoan($loanNumber, $amount, $bankName, $accountNumber, $disbursementCode = null)
    {
        $this->has_helb_loan = true;
        $this->helb_loan_number = $loanNumber;
        $this->helb_loan_amount = $amount;
        $this->helb_bank_name = $bankName;
        $this->helb_bank_account = $accountNumber;
        $this->helb_disbursement_code = $disbursementCode;
        $this->helb_application_date = now();
        $this->save();

        return $this;
    }

    public function updateSponsorship($type, $sponsorName = null, $sponsorId = null, $sponsorPhone = null)
    {
        $this->sponsorship_type = $type;

        if ($type !== 'self') {
            $this->sponsor_name = $sponsorName;
            $this->sponsor_id_number = $sponsorId;
            $this->sponsor_phone = $sponsorPhone;
        }

        $this->save();
        return $this;
    }

    public function markAsAlumni($graduationYear = null)
    {
        $this->is_alumni = true;
        $this->graduation_year = $graduationYear ?? date('Y');
        $this->alumni_membership_date = now();
        $this->save();

        return $this;
    }

    public function updateEmploymentAfterGraduation($employer, $jobTitle, $salary = null)
    {
        $this->employment_status_after_graduation = 'employed';
        $this->employer_after_graduation = $employer;
        $this->job_title_after_graduation = $jobTitle;
        $this->starting_salary = $salary;
        $this->save();

        return $this;
    }

    // ==================== SCOPES ====================

    public function scopeHasCompleteDocuments($query)
    {
        return $query->whereRaw("JSON_LENGTH(documents_uploaded) = JSON_LENGTH(JSON_REMOVE(documents_uploaded, '$.*.?(@.uploaded = false)'))");
    }

    public function scopeWithHelbLoan($query)
    {
        return $query->where('has_helb_loan', true);
    }

    public function scopeGovernmentSponsored($query)
    {
        return $query->whereIn('sponsorship_type', ['government', 'county', 'helb']);
    }

    public function scopeEmployed($query)
    {
        return $query->where('is_employed', true);
    }

    public function scopeWithIndustrialAttachment($query)
    {
        return $query->whereNotNull('industrial_attachment_company');
    }

    public function scopeActiveIndustrialAttachment($query)
    {
        return $query->where('industrial_attachment_status', 'ongoing')
                     ->where('industrial_attachment_start_date', '<=', now())
                     ->where('industrial_attachment_end_date', '>=', now());
    }

    public function scopeAlumni($query)
    {
        return $query->where('is_alumni', true);
    }

    public function scopeMinors($query)
    {
        return $query->whereHas('student', function ($q) {
            $q->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18');
        });
    }

    public function scopeWithMedicalConditions($query)
    {
        return $query->whereNotNull('medical_conditions')
                     ->where('medical_conditions', '!=', '');
    }

    // ==================== HELPER METHODS ====================

    public function getContactInfo()
    {
        return [
            'primary_phone' => $this->personal_phone,
            'alternative_phone' => $this->alternative_phone,
            'personal_email' => $this->personal_email,
            'official_email' => $this->student->email ?? null,
            'postal_address' => $this->full_address,
            'next_of_kin' => [
                'name' => $this->next_of_kin_name,
                'relationship' => $this->next_of_kin_relationship,
                'phone' => $this->next_of_kin_phone,
                'email' => $this->next_of_kin_email,
            ],
            'emergency_contact' => [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
                'alternate_phone' => $this->emergency_contact_alternate_phone,
            ]
        ];
    }

    public function getAcademicBackground()
    {
        return [
            'secondary_school' => [
                'name' => $this->secondary_school_name,
                'completion_year' => $this->secondary_school_completion_year,
                'kcse_index' => $this->kcse_index_number,
                'mean_grade' => $this->kcse_mean_grade,
            ],
            'primary_school' => [
                'name' => $this->primary_school_name,
                'completion_year' => $this->primary_school_completion_year,
                'knec_index' => $this->knec_index_primary,
            ],
            'previous_education' => [
                'institution' => $this->previous_institution,
                'course' => $this->previous_course,
                'years' => $this->previous_start_year && $this->previous_end_year
                    ? $this->previous_start_year . '-' . $this->previous_end_year
                    : null,
                'qualification' => $this->previous_qualification,
            ]
        ];
    }

    public function getFinancialInfo()
    {
        return [
            'sponsorship' => [
                'type' => $this->sponsorship_type,
                'sponsor' => $this->sponsor_name,
                'sponsor_id' => $this->sponsor_id_number,
                'sponsor_phone' => $this->sponsor_phone,
            ],
            'helb_loan' => $this->has_helb_loan ? [
                'loan_number' => $this->helb_loan_number,
                'amount' => $this->helb_loan_amount,
                'bank' => $this->helb_bank_name,
                'account' => $this->helb_bank_account,
            ] : null,
            'bank_details' => [
                'bank' => $this->bank_name,
                'account_name' => $this->bank_account_name,
                'account_number' => $this->bank_account_number,
                'branch' => $this->bank_branch,
            ]
        ];
    }
}
