<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentSelfEnrollmentController extends Controller
{
    /**
     * Dashboard: current term, fees, and next-term eligibility.
     */
    public function index()
    {
        $student = $this->currentStudent();

        // Every enrollment, newest first, with term + course loaded
        $enrollments = $student->enrollments()
            ->with(['term', 'course', 'payments'])
            ->orderByDesc('created_at')
            ->get();

        // Current enrollment = newest active/in_progress, else newest
        $currentEnrollment = $enrollments
            ->first(fn ($e) => in_array($e->status, ['active', 'in_progress']))
            ?? $enrollments->first();

        // Current term: from the enrollment, else globally-current
        $currentTerm = $currentEnrollment?->term
            ?? AcademicTerm::where('is_current', true)->first();

        // Next term
        $nextTerm = $this->nextTermAfter($currentTerm);

        // Fee for next term (same course)
        $nextTermFee = null;
        if ($nextTerm && $currentEnrollment) {
            $nextTermFee = FeeStructure::where('course_id', $currentEnrollment->course_id)
                ->where('term_id', $nextTerm->id)
                ->first();
        }

        // Already enrolled in the next term?
        $alreadyEnrolledNext = false;
        if ($nextTerm && $currentEnrollment) {
            $alreadyEnrolledNext = Enrollment::where('student_id', $student->id)
                ->where('course_id', $currentEnrollment->course_id)
                ->where('term_id', $nextTerm->id)
                ->exists();
        }

        // Eligibility
        $eligibility = $this->buildEligibility($currentEnrollment);

        return view('ktvtc.students.enrollment.index', compact(
            'student',
            'enrollments',
            'currentEnrollment',
            'currentTerm',
            'nextTerm',
            'nextTermFee',
            'alreadyEnrolledNext',
            'eligibility'
        ));
    }

    /**
     * Enroll for the next term. Enforces the 50% rule server-side.
     */
    public function store(Request $request)
    {
        $student = $this->currentStudent();

        $currentEnrollment = $student->enrollments()
            ->with(['term', 'course'])
            ->orderByDesc('created_at')
            ->first();

        if (!$currentEnrollment) {
            return back()->with('error', 'No prior enrollment found. Please contact the admin office.');
        }

        // 🔒 Hard check — never trust the view
        if (!$currentEnrollment->meetsSelfEnrollThreshold()) {
            Log::warning('Self-enrollment blocked (below threshold)', [
                'student_id'      => $student->id,
                'enrollment_id'   => $currentEnrollment->id,
                'percentage_paid' => $currentEnrollment->percentage_paid,
            ]);

            return back()->with('error', sprintf(
                'You must pay at least %d%% of your current term fees before enrolling. You are at %.1f%%. You need KES %s more.',
                Enrollment::SELF_ENROLL_THRESHOLD,
                $currentEnrollment->percentage_paid,
                number_format($currentEnrollment->amount_to_threshold, 2)
            ));
        }

        $nextTerm = $this->nextTermAfter($currentEnrollment->term);
        if (!$nextTerm) {
            return back()->with('error', 'No term is currently open for enrollment. Please try again when registration opens.');
        }

        $feeStructure = FeeStructure::where('course_id', $currentEnrollment->course_id)
            ->where('term_id', $nextTerm->id)
            ->first();

        if (!$feeStructure) {
            return back()->with('error', 'Fees for the next term have not been set yet. Please contact the finance office.');
        }

        // Already enrolled?
        $existing = Enrollment::where('student_id', $student->id)
            ->where('course_id', $currentEnrollment->course_id)
            ->where('term_id', $nextTerm->id)
            ->first();

        if ($existing) {
            return redirect()->route('student.enrollment.index')
                ->with('info', 'You are already enrolled for ' . $nextTerm->name . '.');
        }

        DB::beginTransaction();
        try {
            $totalFee = (float) $feeStructure->total_course_fee;

            if ($nextTerm->isInLateWindow() && $nextTerm->late_registration_fee > 0) {
                $totalFee += (float) $nextTerm->late_registration_fee;
            }

            $enrollment = Enrollment::create([
                'student_id'         => $student->id,
                'course_id'          => $currentEnrollment->course_id,
                'campus_id'          => $student->campus_id,
                'term_id'            => $nextTerm->id,

                'student_name'       => $student->full_name,
                'student_number'     => $student->student_number,

                'course_name'        => $currentEnrollment->course_name,
                'course_code'        => $currentEnrollment->course_code,
                'department'         => $currentEnrollment->department,

                'intake_year'        => $nextTerm->academic_year,
                'intake_month'       => $nextTerm->name,
                'enrollment_date'    => now()->toDateString(),

                'study_mode'         => $currentEnrollment->study_mode,
                'student_type'       => 'continuing',
                'sponsorship_type'   => $currentEnrollment->sponsorship_type,
                'duration_months'    => $feeStructure->total_course_months,
                'start_date'         => $nextTerm->start_date,
                'expected_end_date'  => $nextTerm->end_date,

                'total_fees'         => $totalFee,
                'amount_paid'        => 0,
                'balance'            => $totalFee,

                'status'             => 'active',
                'is_active'          => true,

                'is_self_enrolled'   => true,
                'enrolled_by_user_id'=> auth()->id(),
                'self_enrolled_at'   => now(),
            ]);

            DB::commit();

            Log::info('Student self-enrolled', [
                'student_id'    => $student->id,
                'enrollment_id' => $enrollment->id,
                'term_id'       => $nextTerm->id,
                'total_fee'     => $totalFee,
            ]);

            return redirect()->route('student.enrollment.index')
                ->with('success', "You are now enrolled for {$nextTerm->name}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Self-enrollment failed', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->with('error', 'Enrollment failed. Please try again or contact the office.');
        }
    }

    // ------------------------------------------------------------

    protected function currentStudent(): Student
    {
        $user    = auth()->user();
        $student = Student::with('campus')->find($user->student_id);

        abort_unless($student, 404, 'No student record linked to this account.');

        return $student;
    }

    /**
     * The term that comes after the given one and is currently open.
     */
    protected function nextTermAfter(?AcademicTerm $currentTerm): ?AcademicTerm
    {
        if (!$currentTerm) {
            return null;
        }

        $today = now()->toDateString();

        return AcademicTerm::query()
            ->where('is_active', true)
            ->where('is_registration_open', true)
            ->where(function ($q) use ($currentTerm) {
                $q->where('start_date', '>', $currentTerm->end_date)
                  ->orWhere('academic_year', '>', $currentTerm->academic_year);
            })
            ->where(function ($q) use ($today) {
                // open registration window, or late window
                $q->where(function ($w) use ($today) {
                    $w->whereDate('registration_start_date', '<=', $today)
                      ->whereDate('registration_end_date', '>=', $today);
                })->orWhere(function ($w) use ($today) {
                    $w->where('allow_late_registration', true)
                      ->whereDate('late_registration_start_date', '<=', $today)
                      ->whereDate('late_registration_end_date', '>=', $today);
                });
            })
            ->orderBy('start_date')
            ->first();
    }


/**
 * Download the exam card for an enrollment.
 * Only allowed when the term fees are fully paid.
 */
public function examCard(Enrollment $enrollment)
{
    $student = $this->currentStudent();

    // Ownership check
    abort_unless($enrollment->student_id === $student->id, 403);

    // Full-payment gate
    if (!$enrollment->examCardUnlocked()) {
        return redirect()->route('student.enrollment.index')
            ->with('error', sprintf(
                'Exam card is locked. You must pay the full term fee to download it. Outstanding balance: KES %s.',
                number_format($enrollment->balance, 2)
            ));
    }

    $enrollment->load(['term', 'course', 'campus']);

    $data = [
        'student'      => $student,
        'enrollment'   => $enrollment,
        'term'         => $enrollment->term,
        'course'       => $enrollment->course,
        'campus'       => $enrollment->campus,
        'issued_at'    => now(),
        'logo_path'    => public_path('Assets/images/Kenswed_logo.png'),
        'college'      => [
            'name'    => 'Kenswed Technical & Vocational Training College',
            'motto'   => 'Educate And Elevate',
            'address' => 'P.O. Box 59440 - 00200, Nairobi',
        ],
    ];

    $pdf = Pdf::loadView('ktvtc.students.enrollment.exam-card', $data)
        ->setPaper('A4', 'portrait');

    $filename = 'exam-card-' . $student->student_number . '-' . ($enrollment->term->code ?? $enrollment->term_id) . '.pdf';

    return $pdf->download($filename);
}
    /**
     * Normalized eligibility data used by the view.
     */
    protected function buildEligibility(?Enrollment $currentEnrollment): array
    {
        if (!$currentEnrollment) {
            return [
                'eligible'        => false,
                'percentage_paid' => 0,
                'threshold'       => Enrollment::SELF_ENROLL_THRESHOLD,
                'amount_required' => 0,
                'message'         => 'No current enrollment found. Contact the admin office.',
            ];
        }

        $eligible = $currentEnrollment->meetsSelfEnrollThreshold();

        return [
            'eligible'        => $eligible,
            'percentage_paid' => $currentEnrollment->percentage_paid,
            'threshold'       => Enrollment::SELF_ENROLL_THRESHOLD,
            'amount_required' => $currentEnrollment->amount_to_threshold,
            'message'         => $eligible
                ? sprintf('You have paid %.1f%% of your current term fees.', $currentEnrollment->percentage_paid)
                : sprintf(
                    'You need KES %s more to reach %d%% and unlock next-term enrollment.',
                    number_format($currentEnrollment->amount_to_threshold, 2),
                    Enrollment::SELF_ENROLL_THRESHOLD
                ),
        ];
    }
}