<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseUnit;
use App\Models\GradingScale;
use App\Models\UnitResult;
use App\Models\User;
use App\Services\Academics\ResultsService;
use App\Services\Terms\CourseTermStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Academic admin: course tracks, units per term (with trainers), grading
 * scales, and reviewing/publishing trainers' results.
 */
class AcademicsController extends Controller
{
    public function __construct(private ResultsService $results) {}

    public function index(Request $request)
    {
        $courses = Course::query()
            ->withCount(['units', 'enrollments as active_enrollments_count' => fn ($q) => $q->where('status', 'active')])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->orderBy('name')->get();

        $pending = UnitResult::where('status', UnitResult::SUBMITTED)->count();
        $trainerCount = User::where('role', User::ROLE_TRAINERS)->count();

        return view('ktvtc.academics.index', compact('courses', 'pending', 'trainerCount'));
    }

    public function course(Course $course)
    {
        $units = $course->units()->with('trainer')->withCount('results')->get()->groupBy('term_number');
        $trainers = User::where('role', User::ROLE_TRAINERS)->where(fn ($q) => $q->whereNull('is_active')->orWhere('is_active', true))->orderBy('name')->get(['id', 'name']);
        $tracks = GradingScale::TRACKS;

        return view('ktvtc.academics.course', compact('course', 'units', 'trainers', 'tracks'));
    }

    public function updateCourse(Request $request, Course $course)
    {
        $data = $request->validate([
            'academic_track'   => ['required', Rule::in(array_keys(GradingScale::TRACKS))],
            'term_count'       => 'required|integer|min:1|max:24',
            'term_length'      => 'required|integer|min:1|max:52',
            'term_length_unit' => 'required|in:weeks,months',
        ]);

        if ($course->academic_track && $course->academic_track !== $data['academic_track']
            && UnitResult::whereHas('unit', fn ($q) => $q->where('course_id', $course->id))->exists()) {
            return back()->with('error', 'Results have already been entered on this course\'s track; its track can no longer change.');
        }

        try {
            app(CourseTermStructure::class)->update($course, $data);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        $course->update(['academic_track' => $data['academic_track']]);

        return back()->with('success', 'Course set to ' . GradingScale::TRACKS[$data['academic_track']] . ", {$data['term_count']} terms.");
    }

    public function storeUnit(Request $request, Course $course)
    {
        if (!$course->hasTermSetup()) {
            return back()->with('error', 'Set the number of terms first.');
        }
        $course->units()->create($this->validateUnit($request, $course));

        return back()->with('success', 'Unit added.');
    }

    public function updateUnit(Request $request, CourseUnit $unit)
    {
        $data = $this->validateUnit($request, $unit->course);
        if ($unit->results()->exists() && (int) $data['term_number'] !== $unit->term_number) {
            return back()->with('error', 'Results exist for this unit, so it cannot move to another term.');
        }
        $unit->update($data + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Unit updated.');
    }

    public function destroyUnit(CourseUnit $unit)
    {
        if ($unit->results()->exists()) {
            return back()->with('error', 'Results exist for this unit. Deactivate it instead of deleting.');
        }
        $unit->delete();

        return back()->with('success', 'Unit deleted.');
    }

    public function scales()
    {
        $scales = GradingScale::orderBy('id')->get();
        return view('ktvtc.academics.scales', compact('scales'));
    }

    public function updateScale(Request $request, GradingScale $scale)
    {
        $data = $request->validate([
            'component_a'     => 'required|string|max:30',
            'component_b'     => 'required|string|max:30',
            'weight_a'        => 'required|integer|min:0|max:100',
            'bands'           => 'required|array|min:2',
            'bands.*.label'   => 'required|string|max:30',
            'bands.*.min'     => 'required|numeric|min:0|max:100',
            'bands.*.passed'  => 'nullable|boolean',
        ]);

        $bands = collect($data['bands'])
            ->map(fn ($b) => ['min' => (float) $b['min'], 'label' => $b['label'], 'passed' => (bool) ($b['passed'] ?? false)])
            ->sortByDesc('min')->values();
        if ($bands->last()['min'] != 0) {
            return back()->with('error', 'The lowest band must start at 0 so every total gets a grade.');
        }
        if ($bands->pluck('min')->duplicates()->isNotEmpty()) {
            return back()->with('error', 'Two bands start at the same score.');
        }

        $scale->update([
            'component_a' => $data['component_a'],
            'component_b' => $data['component_b'],
            'weight_a'    => $data['weight_a'],
            'weight_b'    => 100 - $data['weight_a'],
            'bands'       => $bands->all(),
        ]);

        return back()->with('success', "{$scale->name} saved. Results already entered keep their grade until re-saved.");
    }

    public function dashboard(\App\Services\Dashboards\RoleDashboards $dashboards)
    {
        return view('ktvtc.academics.dashboard', $dashboards->academics());
    }

    /**
     * Every mark with its working shown: the two components, their weights, the total
     * and the band it falls in. Filter by course, term, unit, status or student.
     */
    public function results(Request $request)
    {
        $query = UnitResult::query()
            ->with(['unit.course', 'student:id,first_name,last_name,student_number', 'trainer:id,name'])
            ->when($request->filled('course_id'), fn ($q) => $q->whereHas('unit', fn ($u) => $u->where('course_id', $request->course_id)))
            ->when($request->filled('term'), fn ($q) => $q->whereHas('unit', fn ($u) => $u->where('term_number', $request->term)))
            ->when($request->filled('unit_id'), fn ($q) => $q->where('course_unit_id', $request->unit_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->whereHas('student', fn ($s) => $s->where('student_number', 'like', "%{$request->q}%")
                ->orWhere('first_name', 'like', "%{$request->q}%")->orWhere('last_name', 'like', "%{$request->q}%")))
            ->orderByDesc('updated_at');

        $results = $query->paginate(30)->withQueryString();

        // One grading scale per course track, looked up once
        $scales = [];
        foreach ($results as $r) {
            $course = $r->unit?->course;
            if ($course && !array_key_exists($course->id, $scales)) {
                $scales[$course->id] = $course->gradingScale();
            }
        }

        $courses = Course::whereHas('units')->orderBy('name')->get(['id', 'name']);
        $units = $request->filled('course_id')
            ? CourseUnit::where('course_id', $request->course_id)->orderBy('term_number')->orderBy('sort_order')->get(['id', 'title', 'code', 'term_number'])
            : collect();

        return view('ktvtc.academics.results', compact('results', 'scales', 'courses', 'units'));
    }

    public function review()
    {
        $units = CourseUnit::with(['course', 'trainer'])
            ->whereHas('results', fn ($q) => $q->whereIn('status', [UnitResult::SUBMITTED, UnitResult::PUBLISHED]))
            ->withCount([
                'results as submitted_count' => fn ($q) => $q->where('status', UnitResult::SUBMITTED),
                'results as published_count' => fn ($q) => $q->where('status', UnitResult::PUBLISHED),
                'results as draft_count'     => fn ($q) => $q->where('status', UnitResult::DRAFT),
            ])
            ->orderByDesc('submitted_count')->get();

        return view('ktvtc.academics.review', compact('units'));
    }

    public function reviewUnit(CourseUnit $unit)
    {
        $unit->load(['course', 'trainer']);
        $results = $unit->results()->with(['student', 'termRegistration'])->orderBy('status')->orderBy('attempt')->get();
        $scale = $unit->course->gradingScale();

        return view('ktvtc.academics.review-unit', compact('unit', 'results', 'scale'));
    }

    public function decide(Request $request, CourseUnit $unit)
    {
        $data = $request->validate([
            'action'      => 'required|in:publish,return',
            'result_ids'  => 'required|array|min:1',
            'result_ids.*'=> 'integer',
            'reason'      => 'required_if:action,return|nullable|string|min:5|max:255',
        ]);
        $ids = $unit->results()->whereIn('id', $data['result_ids'])->pluck('id')->all();

        if ($data['action'] === 'publish') {
            $n = $this->results->publish($ids, Auth::user());
            return back()->with('success', "{$n} result(s) published. Students can now see them.");
        }

        $n = $this->results->returnToTrainer($ids, $data['reason']);
        return back()->with('success', "{$n} result(s) returned to the trainer.");
    }

    public function unpublish(Request $request, UnitResult $result)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:255']);
        $this->results->unpublish($result, $data['reason']);

        return back()->with('success', 'Result withdrawn and returned to the trainer for correction.');
    }

    private function validateUnit(Request $request, Course $course): array
    {
        return $request->validate([
            'term_number' => 'required|integer|min:1|max:' . $course->term_count,
            'code'        => 'nullable|string|max:40',
            'title'       => 'required|string|max:255',
            'unit_type'   => ['required', Rule::in(array_keys(CourseUnit::TYPES))],
            'trainer_id'  => ['nullable', Rule::exists('users', 'id')->where('role', User::ROLE_TRAINERS)],
            'sort_order'  => 'nullable|integer|min:0|max:999',
        ]);
    }
}
