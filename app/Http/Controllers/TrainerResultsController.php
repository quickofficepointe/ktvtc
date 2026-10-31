<?php

namespace App\Http\Controllers;

use App\Models\CourseUnit;
use App\Models\UnitResult;
use App\Services\Academics\ResultsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Trainer: enter marks for the units they teach and submit them for publishing.
 */
class TrainerResultsController extends Controller
{
    public function __construct(private ResultsService $results) {}

    public function index()
    {
        $units = CourseUnit::with('course')->active()->where('trainer_id', Auth::id())
            ->withCount([
                'results as draft_count'     => fn ($q) => $q->where('status', UnitResult::DRAFT),
                'results as submitted_count' => fn ($q) => $q->where('status', UnitResult::SUBMITTED),
                'results as published_count' => fn ($q) => $q->where('status', UnitResult::PUBLISHED),
            ])
            ->orderBy('course_id')->orderBy('term_number')->get();

        return view('ktvtc.trainers.results.index', compact('units'));
    }

    public function unit(Request $request, CourseUnit $unit)
    {
        $this->authorizeUnit($unit);
        $unit->load('course');
        $scope = $request->query('scope') === 'all' ? 'all' : 'current';

        try {
            $scale = $this->results->scaleFor($unit);
        } catch (\DomainException $e) {
            return redirect()->route('trainers.results.index')->with('error', $e->getMessage());
        }
        $roster = $this->results->roster($unit, $scope);

        return view('ktvtc.trainers.results.unit', compact('unit', 'roster', 'scale', 'scope'));
    }

    public function save(Request $request, CourseUnit $unit)
    {
        $this->authorizeUnit($unit);
        $request->validate(['marks' => 'required|array']);

        try {
            $r = $this->results->saveMarks($unit, $request->input('marks'), Auth::user());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = "{$r['saved']} result(s) saved as draft.";
        if ($r['locked']) {
            $message .= " {$r['locked']} already submitted or published were not changed.";
        }

        if ($request->input('then') === 'submit') {
            $s = $this->results->submit($unit, Auth::user());
            $message .= " {$s['submitted']} submitted for publishing." . ($s['incomplete'] ? " {$s['incomplete']} incomplete (missing a score) stayed as draft." : '');
        }

        return back()->with('success', $message);
    }

    private function authorizeUnit(CourseUnit $unit): void
    {
        abort_unless((int) $unit->trainer_id === (int) Auth::id(), 403, 'This unit is assigned to another trainer.');
    }
}
