<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CdaccRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_id',
        'student_id',
        'course_id',
        'fee_structure_id',
        'cdacc_registration_number',
        'cdacc_index_number',
        'cdacc_learner_id',
        'cdacc_batch_number',
        'cdacc_program_code',
        'cdacc_program_name',
        'cdacc_qualification_title',
        'cdacc_qualification_level',
        'cdacc_trade_area',
        'cdacc_occupation',
        'cdacc_registration_date',
        'cdacc_registration_expiry',
        'cdacc_examination_date',
        'cdacc_certification_date',
        'cdacc_center_number',
        'cdacc_center_name',
        'cdacc_assessor_number',
        'cdacc_moderator_number',
        'registered_modules',
        'total_modules',
        'core_modules',
        'elective_modules',
        'cdacc_registration_fee',
        'cdacc_examination_fee',
        'cdacc_certification_fee',
        'cdacc_moderation_fee',
        'cdacc_total_fee',
        'cdacc_fee_status',
        'cdacc_fee_payment_date',
        'cdacc_payment_reference',
        'assessment_type',
        'assessment_components',
        'assessment_venue',
        'cdacc_status',
        'certification_status',
        'submitted_to_cdacc_at',
        'approved_by_cdacc_at',
        'last_sync_with_cdacc_at',
        'cdacc_api_reference',
        'cdacc_api_response',
        'sync_status',
        'sync_notes',
        'cdacc_registration_form_path',
        'cdacc_admission_letter_path',
        'cdacc_exam_card_path',
        'cdacc_certificate_path',
        'cdacc_transcript_path',
        'module_results',
        'overall_score',
        'overall_grade',
        'competency_level',
        'processed_by',
        'approved_by',
        'approved_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'cdacc_registration_date' => 'date',
        'cdacc_registration_expiry' => 'date',
        'cdacc_examination_date' => 'date',
        'cdacc_certification_date' => 'date',
        'cdacc_fee_payment_date' => 'date',
        'submitted_to_cdacc_at' => 'datetime',
        'approved_by_cdacc_at' => 'datetime',
        'last_sync_with_cdacc_at' => 'datetime',
        'approved_at' => 'datetime',
        'cdacc_registration_fee' => 'decimal:2',
        'cdacc_examination_fee' => 'decimal:2',
        'cdacc_certification_fee' => 'decimal:2',
        'cdacc_moderation_fee' => 'decimal:2',
        'cdacc_total_fee' => 'decimal:2',
        'overall_score' => 'decimal:2',
        'registered_modules' => 'array',
        'assessment_components' => 'array',
        'cdacc_api_response' => 'array',
        'module_results' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Business Logic Methods
    public function submitToCdacc()
    {
        $this->cdacc_status = 'submitted';
        $this->submitted_to_cdacc_at = now();
        $this->save();

        // Integration logic would go here
        return $this;
    }

    public function approveCdaccRegistration()
    {
        $this->cdacc_status = 'approved';
        $this->approved_by_cdacc_at = now();

        // Generate CDACC registration number if not exists
        if (!$this->cdacc_registration_number) {
            $this->cdacc_registration_number = $this->generateCdaccRegistrationNumber();
        }

        $this->save();

        return $this;
    }

    public function registerModule($moduleCode, $moduleName, $credits, $type = 'core')
    {
        $modules = $this->registered_modules ?? [];

        $modules[] = [
            'module_code' => $moduleCode,
            'module_name' => $moduleName,
            'credits' => $credits,
            'type' => $type,
            'status' => 'registered',
            'registration_date' => now()->format('Y-m-d'),
            'exam_series' => null,
        ];

        $this->registered_modules = $modules;
        $this->total_modules = count($modules);
        $this->core_modules = count(array_filter($modules, fn($m) => $m['type'] === 'core'));
        $this->elective_modules = count(array_filter($modules, fn($m) => $m['type'] === 'elective'));
        $this->save();

        return $this;
    }

    public function recordModuleResult($moduleCode, $score, $grade, $remarks = null)
    {
        $moduleResults = $this->module_results ?? [];

        $moduleResults[] = [
            'module_code' => $moduleCode,
            'score' => $score,
            'grade' => $grade,
            'remarks' => $remarks,
            'assessment_date' => now()->format('Y-m-d'),
        ];

        $this->module_results = $moduleResults;
        $this->calculateOverallResults();
        $this->save();

        return $this;
    }

    public function calculateOverallResults()
    {
        $moduleResults = $this->module_results ?? [];
        if (empty($moduleResults)) {
            return;
        }

        $totalScore = 0;
        $totalCredits = 0;
        $allCompetent = true;

        foreach ($moduleResults as $result) {
            $totalScore += ($result['score'] ?? 0);
            $totalCredits += ($result['credits'] ?? 1);

            if (($result['grade'] ?? '') !== 'C' && ($result['grade'] ?? '') !== 'A' && ($result['grade'] ?? '') !== 'B') {
                $allCompetent = false;
            }
        }

        $this->overall_score = $totalCredits > 0 ? round($totalScore / $totalCredits, 2) : null;
        $this->competency_level = $allCompetent ? 'Competent' : 'Not Yet Competent';

        // Determine overall grade
        if ($this->overall_score >= 70) {
            $this->overall_grade = 'A';
        } elseif ($this->overall_score >= 60) {
            $this->overall_grade = 'B';
        } elseif ($this->overall_score >= 50) {
            $this->overall_grade = 'C';
        } elseif ($this->overall_score >= 40) {
            $this->overall_grade = 'D';
        } else {
            $this->overall_grade = 'E';
        }
    }

    public function generateCdaccRegistrationNumber()
    {
        $centerCode = substr($this->cdacc_center_number, -4);
        $programCode = $this->cdacc_program_code;
        $year = date('y');
        $sequence = str_pad($this->id, 4, '0', STR_PAD_LEFT);

        return "CDACC-{$centerCode}-{$programCode}-{$year}-{$sequence}";
    }

    public function getModulesSummary()
    {
        $modules = $this->registered_modules ?? [];

        return [
            'total' => count($modules),
            'core' => count(array_filter($modules, fn($m) => ($m['type'] ?? '') === 'core')),
            'elective' => count(array_filter($modules, fn($m) => ($m['type'] ?? '') === 'elective')),
            'completed' => count(array_filter($modules, fn($m) => ($m['status'] ?? '') === 'completed')),
            'pending' => count(array_filter($modules, fn($m) => ($m['status'] ?? '') === 'pending')),
        ];
    }

    // Scopes
    public function scopePendingSubmission($query)
    {
        return $query->where('cdacc_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('cdacc_status', 'approved');
    }

    public function scopeRegistered($query)
    {
        return $query->where('cdacc_status', 'registered');
    }

    public function scopeForProgram($query, $programCode)
    {
        return $query->where('cdacc_program_code', $programCode);
    }

    public function scopeForCenter($query, $centerNumber)
    {
        return $query->where('cdacc_center_number', $centerNumber);
    }
}
