<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseTermFee;
use App\Models\TermRegistration;
use App\Services\Terms\CourseTermStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Finance: how each course is split into terms, and the fee breakdown per term.
 * Fee versions apply by intake date, so changing fees never alters what
 * already-admitted students were charged.
 */
class TermFeeController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::query()
            ->withCount('termFees')
            ->withCount(['enrollments as active_enrollments_count' => fn ($q) => $q->where('status', 'active')])
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->when($request->query('filter') === 'pending', fn ($q) => $q->where(fn ($w) => $w->whereNull('term_count')->orWhereDoesntHave('termFees')))
            ->orderBy('name')
            ->get();

        return view('ktvtc.finance.term-fees.index', compact('courses'));
    }

    public function course(Course $course)
    {
        $versions = $course->termFees()->withCount('registrations')->orderBy('term_number')->orderByDesc('effective_from')->get()->groupBy('term_number');
        $maxRegistered = (int) TermRegistration::where('course_id', $course->id)->active()->max('term_number');

        return view('ktvtc.finance.term-fees.course', compact('course', 'versions', 'maxRegistered'));
    }

    public function updateStructure(Request $request, Course $course)
    {
        $data = $request->validate([
            'term_count'       => 'required|integer|min:1|max:24',
            'term_length'      => 'required|integer|min:1|max:52',
            'term_length_unit' => 'required|in:weeks,months',
        ]);

        try {
            app(CourseTermStructure::class)->update($course, $data);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$course->name}: {$data['term_count']} terms of {$data['term_length']} {$data['term_length_unit']}.");
    }

    public function storeFee(Request $request, Course $course)
    {
        if (!$course->hasTermSetup()) {
            return back()->with('error', 'Set how many terms the course has first.');
        }

        $data = $request->validate([
            'term_number'     => 'required|integer|min:1|max:' . $course->term_count,
            'effective_from'  => 'required|date',
            'notes'           => 'nullable|string|max:255',
            'items'           => 'required|array|min:1',
            'items.*.name'    => 'nullable|string|max:80',
            'items.*.amount'  => 'nullable|numeric|min:0',
            'apply_to_all'    => 'nullable|boolean',
        ]);

        $items = collect($data['items'])
            ->filter(fn ($i) => filled($i['name'] ?? null) && ($i['amount'] ?? null) !== null)
            ->map(fn ($i) => ['name' => trim($i['name']), 'amount' => round((float) $i['amount'], 2)])
            ->values()->all();
        if (!$items) {
            return back()->withInput()->with('error', 'Add at least one fee item with a name and amount.');
        }

        $terms = $request->boolean('apply_to_all') ? range(1, $course->term_count) : [(int) $data['term_number']];
        $saved = 0;

        foreach ($terms as $termNumber) {
            $existing = CourseTermFee::where('course_id', $course->id)->where('term_number', $termNumber)
                ->whereDate('effective_from', $data['effective_from'])->withCount('registrations')->first();

            if ($existing && $existing->registrations_count > 0) {
                return back()->withInput()->with('error', "Term {$termNumber} fees from {$existing->effective_from->format('d M Y')} are already charged to students. Add a new version with a later date instead.");
            }

            CourseTermFee::updateOrCreate(
                ['course_id' => $course->id, 'term_number' => $termNumber, 'effective_from' => $data['effective_from']],
                ['items' => $items, 'total' => collect($items)->sum('amount'), 'notes' => $data['notes'] ?? null, 'created_by' => Auth::id()]
            );
            $saved++;
        }

        return redirect()->route('finance.fee-structure.course', $course)->with('success', sprintf(
            'Saved KES %s fees for %s, for intakes from %s.',
            number_format(collect($items)->sum('amount'), 2),
            $saved > 1 ? "all {$saved} terms" : 'Term ' . $terms[0],
            \Carbon\Carbon::parse($data['effective_from'])->format('d M Y')
        ));
    }

    public function destroyFee(CourseTermFee $fee)
    {
        if ($fee->registrations()->exists()) {
            return back()->with('error', 'These fees are already charged to students and can\'t be deleted.');
        }
        $course = $fee->course;
        $fee->delete();

        return redirect()->route('finance.fee-structure.course', $course)->with('success', 'Fee version deleted.');
    }

    public function export()
    {
        $rows = CourseTermFee::with('course')->orderBy('course_id')->orderBy('term_number')->orderBy('effective_from')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Course', 'Term', 'Applies to intakes from', 'Item', 'Amount', 'Term total']);
            foreach ($rows as $fee) {
                foreach ($fee->items as $item) {
                    fputcsv($out, [$fee->course->name ?? '', $fee->term_number, $fee->effective_from->toDateString(), $item['name'], $item['amount'], $fee->total]);
                }
            }
            fclose($out);
        }, 'term-fees-' . now()->format('Ymd') . '.csv');
    }
}
