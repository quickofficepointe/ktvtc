<?php
// app/Http/Controllers/TrainersController.php

namespace App\Http\Controllers;

use App\Models\CourseUnit;
use App\Models\TermRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainersController extends Controller
{
    // ============================================================
    // DASHBOARD
    // ============================================================

    /**
     * Trainer overview: my units, marks still to enter or submit, and feedback.
     */
    public function dashboard(\App\Services\Dashboards\RoleDashboards $dashboards)
    {
        return view('ktvtc.trainers.dashboard', $dashboards->trainer(auth()->user()));
    }


    /**
     * Students registered for a term this trainer teaches a unit in: running now or ended in
     * the last 30 days (same count as the dashboard), or every intake with ?show=all.
     */
    public function students(Request $request)
    {
        $units = CourseUnit::with('course:id,name,code')->active()->where('trainer_id', Auth::id())
            ->orderBy('course_id')->orderBy('term_number')->get();
        $show = $request->query('show') === 'all' ? 'all' : 'current';

        $registrations = TermRegistration::with(['student:id,first_name,last_name,student_number,phone', 'enrollment', 'course:id,name'])
            ->where('status', '!=', 'cancelled')
            ->where('registration_type', '!=', TermRegistration::TYPE_EXEMPT)
            ->where(function ($q) use ($units) {
                $q->whereRaw('1 = 0');
                foreach ($units as $u) {
                    $q->orWhere(fn ($w) => $w->where('course_id', $u->course_id)->where('term_number', $u->term_number));
                }
            })
            ->when($show === 'current', fn ($q) => $q->whereDate('ends_on', '>=', today()->subDays(30)))
            ->when($request->filled('course_id'), fn ($q) => $q->where('course_id', $request->course_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->whereHas('student', fn ($w) => $w->where('student_number', 'like', "%{$s}%")
                    ->orWhere('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%"));
            })
            ->orderBy('course_id')->orderBy('term_number')->orderBy('starts_on')
            ->paginate(50)->withQueryString();

        // Share of each term's fee paid (payments clear the oldest term first)
        $ledger = app(\App\Services\Terms\TermLedger::class);
        $termPaid = [];
        foreach ($registrations->getCollection()->pluck('enrollment')->filter()->unique('id') as $enrollment) {
            foreach ($ledger->forEnrollment($enrollment) as $row) {
                $termPaid[$row['registration']->id] = $row['percent'];
            }
        }

        $unitsByTerm = $units->groupBy(fn ($u) => $u->course_id . ':' . $u->term_number);
        $courses = $units->pluck('course')->filter()->unique('id')->sortBy('name')->values();

        return view('ktvtc.trainers.students', compact('registrations', 'unitsByTerm', 'courses', 'show', 'termPaid'));
    }
}