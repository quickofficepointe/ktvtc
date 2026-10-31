<?php

namespace App\Http\Controllers;

use App\Models\CourseUnit;
use App\Models\EvaluationQuestion;
use App\Models\TermRegistration;
use App\Services\Academics\EvaluationService;
use Illuminate\Http\Request;

/**
 * Student: evaluate the trainer of each unit taken in a term. Anonymous.
 */
class StudentEvaluationController extends Controller
{
    public function __construct(private EvaluationService $evaluations) {}

    public function show(TermRegistration $registration)
    {
        $this->authorizeRegistration($registration);

        return view('ktvtc.students.evaluations.show', [
            'registration' => $registration->load('course'),
            'units'        => $this->evaluations->unitsFor($registration),
            'questions'    => EvaluationQuestion::active()->get(),
        ]);
    }

    public function store(Request $request, TermRegistration $registration, CourseUnit $unit)
    {
        $this->authorizeRegistration($registration);

        try {
            $this->evaluations->submit($registration, $unit, (array) $request->input('answers', []));
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage())->with('open_unit', $unit->id);
        }

        $left = $this->evaluations->unitsFor($registration)->reject(fn ($u) => $u->evaluated)->count();

        return redirect()->route('student.evaluations.show', $registration)->with('success',
            'Thank you. Your evaluation is anonymous.' . ($left ? " {$left} left for this term." : ' All trainers for this term are evaluated.'));
    }

    private function authorizeRegistration(TermRegistration $registration): void
    {
        abort_unless((int) $registration->student_id === (int) auth()->user()->student_id && $registration->status === 'registered', 403);
    }
}
