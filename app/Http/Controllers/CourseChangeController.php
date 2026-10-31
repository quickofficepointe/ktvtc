<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseChangeRequest;
use App\Models\CourseTermFee;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\Students\CourseChangeService;
use App\Services\Students\EnrollmentCreator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Course change requests: students ask from the portal; academics, finance and
 * admissions approve in turn (see CourseChangeService).
 */
class CourseChangeController extends Controller
{
    public function __construct(private CourseChangeService $changes) {}

    // ------------------------------------------------------------------
    // Student portal
    // ------------------------------------------------------------------

    public function studentIndex()
    {
        $student = $this->currentStudent();
        $enrollments = $student->enrollments()->with('course')->whereIn('status', EnrollmentCreator::OPEN_STATUSES)->get();
        $courses = Course::where('is_active', true)->whereNotIn('id', $enrollments->pluck('course_id'))->orderBy('name')->get(['id', 'name', 'code']);
        $requests = CourseChangeRequest::with(['fromEnrollment', 'toCourse', 'newEnrollment'])->where('student_id', $student->id)->latest('id')->get();

        return view('ktvtc.students.course-change.index', compact('student', 'enrollments', 'courses', 'requests'));
    }

    public function store(Request $request)
    {
        $student = $this->currentStudent();
        $data = $request->validate([
            'from_enrollment_id' => 'required|integer',
            'to_course_id'       => 'required|exists:courses,id',
            'reason'             => 'required|string|min:10|max:1000',
        ]);
        $from = Enrollment::where('student_id', $student->id)->findOrFail($data['from_enrollment_id']);

        try {
            $this->changes->submit($student, $from, Course::findOrFail($data['to_course_id']), $data['reason']);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Your request has been sent. Academics, finance and admissions will review it in turn; you will get an SMS with the outcome.');
    }

    public function cancel(CourseChangeRequest $courseChange)
    {
        try {
            $this->changes->cancel($courseChange, $this->currentStudent());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request withdrawn.');
    }

    // ------------------------------------------------------------------
    // Academics, finance, admissions
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $user = Auth::user();
        $myStage = array_search((int) $user->role, CourseChangeRequest::STAGE_ROLES, true);
        $filter = $request->query('status', 'mine');

        $requests = CourseChangeRequest::with(['student', 'fromEnrollment', 'toCourse'])
            ->when($filter === 'mine', fn ($q) => $q->where('status', $myStage))
            ->when($filter === 'pending', fn ($q) => $q->pending())
            ->when(in_array($filter, ['approved', 'rejected', 'cancelled'], true), fn ($q) => $q->where('status', $filter))
            ->latest('id')->paginate(25)->withQueryString();

        $counts = CourseChangeRequest::selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status');

        return view('ktvtc.course-changes.index', [
            'requests' => $requests, 'counts' => $counts, 'filter' => $filter, 'myStage' => $myStage, 'layout' => $this->layout(),
        ]);
    }

    public function show(CourseChangeRequest $courseChange)
    {
        $courseChange->load(['student', 'fromEnrollment.course', 'toCourse', 'newEnrollment', 'academicsUser', 'financeUser', 'adminUser']);
        $from = $courseChange->fromEnrollment;
        $to = $courseChange->toCourse;

        // What the student would be billed for their first term on the new course
        $startFee = null;
        if ($to->hasTermSetup() && $courseChange->start_term) {
            $startFee = CourseTermFee::where('course_id', $to->id)->where('term_number', $courseChange->start_term)
                ->whereDate('effective_from', '<=', today())->orderByDesc('effective_from')->value('total')
                ?? CourseTermFee::where('course_id', $to->id)->where('term_number', $courseChange->start_term)->orderBy('effective_from')->value('total');
        }

        return view('ktvtc.course-changes.show', [
            'change' => $courseChange, 'from' => $from, 'to' => $to, 'startFee' => $startFee,
            'canAct' => $courseChange->awaits(Auth::user()), 'layout' => $this->layout(),
        ]);
    }

    public function approve(Request $request, CourseChangeRequest $courseChange)
    {
        $user = Auth::user();
        if (!$courseChange->awaits($user)) {
            return back()->with('error', $courseChange->isPending()
                ? 'This step is for ' . CourseChangeRequest::STAGE_NAMES[$courseChange->status] . '.'
                : 'This request has already been decided.');
        }

        try {
            match ($courseChange->status) {
                CourseChangeRequest::STAGE_ACADEMICS => $this->changes->approveAcademics($courseChange, $user,
                    (int) $request->validate(['start_term' => 'required|integer|min:1|max:20'])['start_term'], $request->input('note')),
                CourseChangeRequest::STAGE_FINANCE => $this->changes->approveFinance($courseChange, $user,
                    (float) $request->validate(['amount_moved' => 'required|numeric|min:0'])['amount_moved'], $request->boolean('clear_old_balance'), $request->input('note')),
                CourseChangeRequest::STAGE_ADMIN => $this->changes->approveAdmin($courseChange, $user, $request->input('note')),
                default => throw new \DomainException('This request has already been decided.'),
            };
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $courseChange->refresh();
        $message = $courseChange->status === CourseChangeRequest::APPROVED
            ? "Approved. New registration number: {$courseChange->newEnrollment->student_number}. The student has been texted."
            : 'Approved and passed to ' . CourseChangeRequest::STAGE_NAMES[$courseChange->status] . '.';

        return redirect()->route('course-changes.show', $courseChange)->with('success', $message);
    }

    public function reject(Request $request, CourseChangeRequest $courseChange)
    {
        $data = $request->validate(['reason' => 'required|string|min:5|max:500']);

        try {
            $this->changes->reject($courseChange, Auth::user(), $data['reason']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('course-changes.show', $courseChange)->with('success', 'Request turned down. The student has been texted the reason.');
    }

    private function layout(): string
    {
        return match ((int) Auth::user()->role) {
            11      => 'ktvtc.academics.layout',
            7       => 'ktvtc.finance.layouts.app',
            default => 'ktvtc.admin.layout.adminlayout',
        };
    }

    private function currentStudent(): Student
    {
        $student = Auth::user()->student;
        abort_unless($student, 403, 'No student record is linked to this account.');
        return $student;
    }
}
