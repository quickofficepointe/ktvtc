<?php

namespace App\Http\Controllers;

use App\Models\GradingScale;
use App\Models\Student;
use App\Services\Academics\ResultsService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Student: published results per term, and a transcript PDF.
 * Results are shown whatever the fee balance (college decision).
 */
class StudentResultsController extends Controller
{
    public function __construct(private ResultsService $results) {}

    public function index()
    {
        [$student, $courses] = $this->data();
        return view('ktvtc.students.results.index', compact('student', 'courses'));
    }

    public function transcript()
    {
        [$student, $courses] = $this->data();

        return Pdf::loadView('ktvtc.students.results.transcript', [
            'student'   => $student,
            'courses'   => $courses,
            'issued_at' => now(),
            'logo_path' => public_path('Assets/images/Kenswed_logo.png'),
        ])->setPaper('A4', 'portrait')
          ->download('transcript-' . str_replace('/', '-', $student->student_number) . '.pdf');
    }

    private function data(): array
    {
        $student = Student::find(auth()->user()->student_id);
        abort_unless($student, 404, 'No student record linked to this account.');

        $byEnrollment = $this->results->transcript($student->id);
        $courses = $student->enrollments()->with('course')->get()
            ->filter(fn ($e) => $byEnrollment->has($e->id))
            ->map(fn ($e) => [
                'enrollment' => $e,
                'track'      => GradingScale::TRACKS[$e->course?->academic_track] ?? null,
                'scale'      => $e->course?->gradingScale(),
                'terms'      => $byEnrollment->get($e->id),
            ])->values();

        return [$student, $courses];
    }
}
