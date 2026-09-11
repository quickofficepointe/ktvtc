<?php

namespace App\Http\Controllers;

use App\Models\AttachmentApplication;
use App\Models\Enrollment;
use App\Models\Opportunity;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StudentAttachmentController extends Controller
{
    // ============================================================
    // INDEX — hub page: status + shortcuts
    // ============================================================

    public function index()
    {
        $student = $this->currentStudent();

        $currentEnrollment = $student->enrollments()
            ->with(['course', 'term'])
            ->orderByDesc('created_at')
            ->first();

        $applications = AttachmentApplication::with(['opportunity.company', 'opportunity.course'])
            ->where('student_id', $student->id)
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'total'    => $applications->count(),
            'pending'  => $applications->where('status', 'pending')->count(),
            'approved' => $applications->where('status', 'approved')->count(),
        ];

        // Matching opportunities for this student's course
        $openCount = 0;
        if ($currentEnrollment) {
            $openCount = Opportunity::where('course_id', $currentEnrollment->course_id)
                ->where('status', 'open')
                ->where('is_active', true)
                ->whereColumn('slots_taken', '<', 'slots')
                ->count();
        }

        return view('ktvtc.students.attachment.index', compact(
            'student',
            'currentEnrollment',
            'applications',
            'stats',
            'openCount'
        ));
    }

    // ============================================================
    // OPPORTUNITIES — list filtered by student's course
    // ============================================================

    public function opportunities(Request $request)
    {
        $student = $this->currentStudent();

        $currentEnrollment = $student->enrollments()
            ->with(['course', 'term'])
            ->orderByDesc('created_at')
            ->first();

        abort_unless($currentEnrollment, 404, 'You have no active enrollment.');

        $query = Opportunity::with(['company', 'course', 'term'])
            ->where('course_id', $currentEnrollment->course_id)
            ->where('status', 'open')
            ->where('is_active', true)
            ->whereColumn('slots_taken', '<', 'slots');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $opportunities = $query->orderBy('application_closes_at')->paginate(12);

        // Which ones has this student already applied to?
        $appliedIds = AttachmentApplication::where('student_id', $student->id)
            ->pluck('opportunity_id')
            ->toArray();

        return view('ktvtc.students.attachment.opportunities', compact(
            'student',
            'currentEnrollment',
            'opportunities',
            'appliedIds'
        ));
    }

    public function showOpportunity(Opportunity $opportunity)
    {
        $student = $this->currentStudent();

        // Must be for this student's course
        $currentEnrollment = $student->enrollments()->latest()->first();
        abort_unless(
            $currentEnrollment && $opportunity->course_id === $currentEnrollment->course_id,
            403,
            'This opportunity is not for your course.'
        );

        $opportunity->load(['company', 'course', 'term']);

        $alreadyApplied = AttachmentApplication::where('student_id', $student->id)
            ->where('opportunity_id', $opportunity->id)
            ->exists();

        return view('ktvtc.students.attachment.opportunity-show', compact(
            'student',
            'opportunity',
            'alreadyApplied'
        ));
    }

    // ============================================================
    // APPLY
    // ============================================================

    public function apply(Request $request, Opportunity $opportunity)
    {
        $student = $this->currentStudent();

        $currentEnrollment = $student->enrollments()->latest()->first();
        abort_unless(
            $currentEnrollment && $opportunity->course_id === $currentEnrollment->course_id,
            403,
            'This opportunity is not for your course.'
        );

        if (!$opportunity->isOpen()) {
            return back()->with('error', 'This opportunity is no longer accepting applications.');
        }

        // Prevent duplicates
        $existing = AttachmentApplication::where('student_id', $student->id)
            ->where('opportunity_id', $opportunity->id)
            ->first();

        if ($existing) {
            return redirect()->route('student.attachment.applications')
                ->with('info', 'You have already applied to this opportunity.');
        }

        $data = $request->validate([
            'cv'            => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'cover_letter'  => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'student_notes' => 'nullable|string|max:2000',
        ]);

        try {
            $cvPath = null;
            $clPath = null;

            if ($request->hasFile('cv')) {
                $cvPath = $request->file('cv')->store('attachment-cvs', 'public');
            }
            if ($request->hasFile('cover_letter')) {
                $clPath = $request->file('cover_letter')->store('attachment-letters', 'public');
            }

            $application = AttachmentApplication::create([
                'opportunity_id'   => $opportunity->id,
                'student_id'       => $student->id,
                'cv_path'          => $cvPath,
                'cover_letter_path'=> $clPath,
                'student_notes'    => $data['student_notes'] ?? null,
                'status'           => 'pending',
            ]);

            Log::info('Attachment application submitted', [
                'application_id' => $application->id,
                'student_id'     => $student->id,
                'opportunity_id' => $opportunity->id,
            ]);

            return redirect()->route('student.attachment.applications')
                ->with('success', 'Application submitted successfully. You will be notified once it is reviewed.');

        } catch (\Throwable $e) {
            Log::error('Application failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Could not submit application: ' . $e->getMessage())->withInput();
        }
    }

    // ============================================================
    // MY APPLICATIONS
    // ============================================================

    public function applications()
    {
        $student = $this->currentStudent();

        $applications = AttachmentApplication::with([
                'opportunity.company',
                'opportunity.course',
                'opportunity.term',
            ])
            ->where('student_id', $student->id)
            ->latest()
            ->paginate(15);

        return view('ktvtc.students.attachment.applications', compact('student', 'applications'));
    }

    public function withdraw(AttachmentApplication $application)
    {
        $student = $this->currentStudent();

        abort_unless($application->student_id === $student->id, 403);

        if (!$application->isPending() && !$application->isShortlisted()) {
            return back()->with('error', 'You can only withdraw pending or shortlisted applications.');
        }

        $application->withdraw();

        return back()->with('success', 'Application withdrawn.');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    protected function currentStudent(): Student
    {
        $user = auth()->user();
        $student = Student::with('campus')->find($user->student_id);
        abort_unless($student, 404, 'No student record linked to this account.');
        return $student;
    }
}