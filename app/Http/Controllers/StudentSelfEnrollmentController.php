<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TermRegistration;
use App\Services\Academics\EvaluationService;
use App\Services\Terms\ExamCardGate;
use App\Services\Terms\TermLedger;
use App\Services\Terms\TermRegistrationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Student portal: register for the next term (50% of the current term paid)
 * and download the exam card (term 100% paid + trainer evaluations done).
 */
class StudentSelfEnrollmentController extends Controller
{
    public function __construct(
        private TermRegistrationService $terms,
        private TermLedger $ledger,
        private ExamCardGate $examCards,
    ) {}

    public function index()
    {
        $student = $this->currentStudent();

        $courses = $student->enrollments()->with('course')->whereNot('status', 'dropped')->orderByDesc('id')->get()
            ->map(function ($enrollment) {
                // Existing students: register the terms they are already in
                $this->terms->syncLegacy($enrollment);

                $ledger = $this->ledger->forEnrollment($enrollment->fresh());
                return [
                    'enrollment' => $enrollment,
                    'ledger'     => $ledger->map(fn ($row) => $row + [
                        'exam_card'   => $this->examCards->check($row['registration']),
                        'evaluations' => app(EvaluationService::class)->unitsFor($row['registration']),
                    ]),
                    'credit'     => $this->ledger->credit($enrollment->fresh()),
                    'next'       => $this->terms->nextTermStatus($enrollment->fresh()),
                ];
            });

        return view('ktvtc.students.enrollment.index', [
            'student'   => $student,
            'courses'   => $courses,
            'threshold' => TermRegistrationService::THRESHOLD_PERCENT,
        ]);
    }

    public function store(Request $request)
    {
        $student = $this->currentStudent();
        $request->validate(['enrollment_id' => 'required|integer']);

        $enrollment = $student->enrollments()->whereKey($request->enrollment_id)->firstOrFail();

        try {
            $reg = $this->terms->selfRegister($enrollment, auth()->id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        Log::info('Student registered for term', ['student_id' => $student->id, 'enrollment_id' => $enrollment->id, 'term' => $reg->term_number]);

        return redirect()->route('student.enrollment.index')->with('success', sprintf(
            'You are registered for Term %d (%s – %s). Term fee: KES %s.',
            $reg->term_number, $reg->starts_on->format('d M Y'), $reg->ends_on->format('d M Y'), number_format($reg->fee_amount, 2)
        ));
    }

    public function examCard(TermRegistration $registration)
    {
        $student = $this->currentStudent();
        abort_unless((int) $registration->student_id === (int) $student->id && $registration->status === 'registered', 403);

        $gate = $this->examCards->check($registration);
        if (!$gate['allowed']) {
            return redirect()->route('student.enrollment.index')
                ->with('error', 'Exam card is locked. ' . implode(' ', $gate['reasons']));
        }

        $registration->forceFill(['exam_card_downloaded_at' => now()])->save();
        $enrollment = $registration->enrollment->load(['course', 'campus']);

        $pdf = Pdf::loadView('ktvtc.students.enrollment.exam-card', [
            'student'    => $student,
            'enrollment' => $enrollment,
            'term'       => (object) [
                'name'          => "Term {$registration->term_number} ({$registration->starts_on->format('d M Y')} – {$registration->ends_on->format('d M Y')})",
                'academic_year' => $registration->starts_on->year,
            ],
            'course'     => $enrollment->course,
            'campus'     => $enrollment->campus,
            'issued_at'  => now(),
            'logo_path'  => public_path('Assets/images/Kenswed_logo.png'),
            'college'    => [
                'name'    => 'Kenswed Technical & Vocational Training College',
                'motto'   => 'Educate And Elevate',
            ],
        ])->setPaper('A4', 'portrait');

        return $pdf->download('exam-card-' . str_replace('/', '-', $student->student_number) . '-term-' . $registration->term_number . '.pdf');
    }

    protected function currentStudent(): Student
    {
        $student = Student::with('campus')->find(auth()->user()->student_id);
        abort_unless($student, 404, 'No student record linked to this account.');
        return $student;
    }
}
