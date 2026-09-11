<?php
// app/Http/Controllers/AttachmentController.php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Attachment;
use App\Models\AttachmentApplication;
use App\Models\Company;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    // ============================================================
    // STUDENT SIDE
    // ============================================================

    public function index()
    {
        $student = $this->currentStudent();

        $attachment = Attachment::with(['company', 'term', 'opportunity'])
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        $approvedApplication = null;
        if (!$attachment) {
            $approvedApplication = AttachmentApplication::with([
                    'opportunity.company', 'opportunity.course', 'opportunity.term'
                ])
                ->where('student_id', $student->id)
                ->where('status', 'approved')
                ->latest()
                ->first();
        }

        return view('ktvtc.students.attachment.details', compact(
            'student', 'attachment', 'approvedApplication'
        ));
    }

    /**
     * Show the create form.
     * Works with OR without an approved application (self-found path).
     */
    public function create(Request $request)
    {
        $student = $this->currentStudent();

        if (Attachment::where('student_id', $student->id)->exists()) {
            return redirect()->route('student.attachment.details')
                ->with('info', 'You already have an attachment record.');
        }

        // Optional prefill application — do NOT require it
        if ($request->filled('application_id')) {
            $application = AttachmentApplication::with([
                    'opportunity.company', 'opportunity.term'
                ])
                ->where('student_id', $student->id)
                ->where('status', 'approved')
                ->find($request->application_id);
        } else {
            $application = AttachmentApplication::with([
                    'opportunity.company', 'opportunity.term'
                ])
                ->where('student_id', $student->id)
                ->where('status', 'approved')
                ->latest()
                ->first();
        }

        $companies  = Company::active()->orderBy('name')->get();
        $hasPrefill = !is_null($application);

        return view('ktvtc.students.attachment.details-form', [
            'student'     => $student,
            'attachment'  => null,
            'application' => $application,
            'companies'   => $companies,
            'hasPrefill'  => $hasPrefill,
        ]);
    }

    /**
     * Store the new attachment.
     * Accepts submissions with no approved application (self-found).
     */
    public function store(Request $request)
    {
        $student = $this->currentStudent();

        if (Attachment::where('student_id', $student->id)->exists()) {
            return redirect()->route('student.attachment.details')
                ->with('error', 'You already have an attachment record.');
        }

        $data = $this->validateAttachment($request);

        // Link to an approved application if one exists — optional
        $application = null;
        if ($request->filled('application_id')) {
            $application = AttachmentApplication::with('opportunity')
                ->where('student_id', $student->id)
                ->where('status', 'approved')
                ->find($request->input('application_id'));
        }

        try {
            $attachment = new Attachment($data);
            $attachment->student_id     = $student->id;
            $attachment->application_id = $application?->id;
            $attachment->opportunity_id = $application?->opportunity_id;
            $attachment->term_id        = $request->input('term_id')
                ?? $application?->opportunity?->term_id
                ?? AcademicTerm::where('is_current', true)->value('id');
            $attachment->status         = 'submitted';

            // Registered company or self-found?
            if ($request->filled('company_id') && $request->input('company_id') !== 'other') {
                $company = Company::find($request->input('company_id'));
                if ($company) {
                    $attachment->company_id      = $company->id;
                    $attachment->company_name    = $attachment->company_name ?: $company->name;
                    $attachment->company_sector  = $attachment->company_sector ?: $company->sector;
                    $attachment->company_address = $attachment->company_address ?: $company->physical_address;
                    $attachment->company_phone   = $attachment->company_phone ?: $company->company_phone;
                    $attachment->company_email   = $attachment->company_email ?: $company->company_email;
                }
            } else {
                $attachment->company_id = null; // self-found
            }

            $this->handleUploads($request, $attachment);

            $attachment->save();

            Log::info('Attachment submitted', [
                'attachment_id'   => $attachment->id,
                'student_id'      => $student->id,
                'self_found'      => is_null($attachment->company_id),
                'has_application' => !is_null($application),
            ]);

            return redirect()->route('student.attachment.details')
                ->with('success', 'Attachment details submitted. Your trainer will review them shortly.');
        } catch (\Throwable $e) {
            Log::error('Attachment store failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to submit: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Attachment $attachment)
    {
        $student = $this->currentStudent();
        abort_unless($attachment->student_id === $student->id, 403);

        $attachment->load(['company', 'term', 'application', 'reviewer', 'grader']);

        return view('ktvtc.students.attachment.details-show', compact('student', 'attachment'));
    }

    public function edit(Attachment $attachment)
    {
        $student = $this->currentStudent();
        abort_unless($attachment->student_id === $student->id, 403);

        if (!$attachment->canBeEditedByStudent()) {
            return redirect()->route('student.attachment.details')
                ->with('error', 'You cannot edit an attachment at status: ' . $attachment->status_label . '.');
        }

        $companies = Company::active()->orderBy('name')->get();

        return view('ktvtc.students.attachment.details-form', [
            'student'     => $student,
            'attachment'  => $attachment,
            'application' => $attachment->application,
            'companies'   => $companies,
            'hasPrefill'  => !is_null($attachment->application),
        ]);
    }

    public function update(Request $request, Attachment $attachment)
    {
        $student = $this->currentStudent();
        abort_unless($attachment->student_id === $student->id, 403);

        if (!$attachment->canBeEditedByStudent()) {
            return back()->with('error', 'This attachment can no longer be edited.');
        }

        $data = $this->validateAttachment($request);

        try {
            $attachment->fill($data);

            if ($request->filled('company_id') && $request->input('company_id') !== 'other') {
                $company = Company::find($request->input('company_id'));
                if ($company) {
                    $attachment->company_id      = $company->id;
                    $attachment->company_name    = $attachment->company_name ?: $company->name;
                    $attachment->company_sector  = $attachment->company_sector ?: $company->sector;
                    $attachment->company_address = $attachment->company_address ?: $company->physical_address;
                    $attachment->company_phone   = $attachment->company_phone ?: $company->company_phone;
                    $attachment->company_email   = $attachment->company_email ?: $company->company_email;
                }
            } else {
                $attachment->company_id = null;
            }

            $this->handleUploads($request, $attachment);

            if ($attachment->status === 'needs_revision') {
                $attachment->status = 'submitted';
            }

            $attachment->save();

            return redirect()->route('student.attachment.details')
                ->with('success', 'Attachment details updated.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to update: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Attachment $attachment)
    {
        $student = $this->currentStudent();
        abort_unless($attachment->student_id === $student->id, 403);

        if ($attachment->status !== 'draft') {
            return back()->with('error', 'You can only delete draft attachments.');
        }

        $attachment->delete();

        return redirect()->route('student.attachment.details')
            ->with('success', 'Draft attachment removed.');
    }

    // ============================================================
    // TRAINER SIDE
    // ============================================================

    /**
     * List attachments for students enrolled in the trainer's courses.
     */
    public function trainerIndex(Request $request)
    {
        $trainer   = Auth::user();
        $courseIds = $trainer->trainerCourseIds();

        $query = Attachment::with(['student', 'company', 'term'])
            ->forCourses($courseIds);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', [
                'submitted',
                'trainer_approved',
                'needs_revision',
                'active',
                'completed',
                'graded',
            ]);
        }

        // Scope to a specific course
        if ($request->filled('course_id')) {
            $query->whereHas('student.enrollments', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        // Company type filter
        if ($request->filled('company_type')) {
            if ($request->company_type === 'self') {
                $query->whereNull('company_id');
            } elseif ($request->company_type === 'registered') {
                $query->whereNotNull('company_id');
            }
        }

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhere('supervisor_name', 'like', "%{$s}%")
                  ->orWhereHas('student', function ($st) use ($s) {
                      $st->where('first_name', 'like', "%{$s}%")
                         ->orWhere('last_name', 'like', "%{$s}%")
                         ->orWhere('student_number', 'like', "%{$s}%");
                  });
            });
        }

        $attachments = $query->latest()->paginate(20)->withQueryString();

        $base = Attachment::forCourses($courseIds);

        $stats = [
            'submitted'        => (clone $base)->where('status', 'submitted')->count(),
            'trainer_approved' => (clone $base)->where('status', 'trainer_approved')->count(),
            'active'           => (clone $base)->where('status', 'active')->count(),
            'needs_revision'   => (clone $base)->where('status', 'needs_revision')->count(),
            'completed'        => (clone $base)->whereIn('status', ['completed', 'graded'])->count(),
            'self_found'       => (clone $base)->whereNull('company_id')->count(),
            'registered'       => (clone $base)->whereNotNull('company_id')->count(),
        ];

        $courses = $trainer->activeCoursesAsTrainer()->orderBy('name')->get();

        return view('ktvtc.trainers.attachments.index', compact(
            'attachments', 'stats', 'courses'
        ));
    }

    /**
     * Show one attachment (scoped to trainer's courses).
     */
    public function trainerShow(Attachment $attachment)
    {
        $this->authorize('viewAsTrainer', $attachment);

        $attachment->load([
            'student', 'company', 'term', 'opportunity',
            'application', 'reviewer', 'grader',
            'trainerApprovedBy', 'trainerApprovedCourse',
            'eligibilityCheckedBy',
        ]);

        $courses = Auth::user()->activeCoursesAsTrainer()->orderBy('name')->get();

        return view('ktvtc.trainers.attachments.show', compact('attachment', 'courses'));
    }

    /**
     * Trainer approves → status becomes 'trainer_approved'.
     */
    public function trainerApprove(Request $request, Attachment $attachment)
    {
        $this->authorize('approveAsTrainer', $attachment);

        $request->validate([
            'remarks'   => 'nullable|string|max:500',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $trainer = Auth::user();

        // Resolve which course this approval is tied to
        $courseId = $request->input('course_id')
            ?? $this->resolveTrainerCourseId($trainer, $attachment);

        if (!$courseId) {
            return back()->with('error',
                'Could not determine which course this approval belongs to.');
        }

        // Guard: the course must belong to this trainer
        if (!$trainer->teachesCourse((int) $courseId)) {
            return back()->with('error',
                'You are not assigned to that course.');
        }

        try {
            $attachment->approveByTrainer(
                $trainer,
                (int) $courseId,
                $request->remarks
            );

            Log::info('Trainer approved attachment', [
                'attachment_id' => $attachment->id,
                'trainer_id'    => $trainer->id,
                'course_id'     => $courseId,
            ]);

            return back()->with('success',
                'Student approved for attachment. Awaiting ILO activation.');
        } catch (\Throwable $e) {
            Log::error('Trainer approve failed', [
                'attachment_id' => $attachment->id,
                'error'         => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    /**
     * Trainer requests revision → back to the student.
     */
    public function trainerRequestRevision(Request $request, Attachment $attachment)
    {
        $this->authorize('requestRevisionAsTrainer', $attachment);

        $request->validate([
            'remarks' => 'required|string|min:5|max:500',
        ]);

        try {
            $attachment->requestRevisionByTrainer(Auth::user(), $request->remarks);

            return back()->with('warning',
                'Revision requested. Student has been notified.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Trainer marks attachment eligibility (checklist).
     */
    public function trainerMarkEligibility(Request $request, Attachment $attachment)
    {
        $this->authorize('markEligibilityAsTrainer', $attachment);

        $request->validate([
            'is_eligible' => 'required|boolean',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $attachment->markEligible(
            Auth::user(),
            (bool) $request->is_eligible,
            $request->notes
        );

        return back()->with('success', 'Eligibility updated.');
    }

    /**
     * Trainer logs an on-site visit.
     */
    public function trainerLogVisit(Request $request, Attachment $attachment)
    {
        $this->authorize('logVisitAsTrainer', $attachment);

        $request->validate([
            'notes' => 'required|string|min:5|max:2000',
        ]);

        $attachment->logTrainerVisit($request->notes);

        return back()->with('success', 'Trainer site visit logged.');
    }

    // ============================================================
    // ILO SIDE
    // ============================================================

    public function iloIndex(Request $request)
    {
        $query = Attachment::with(['student', 'company', 'term']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', [
                'submitted',
                'trainer_approved',
                'active',
                'needs_revision',
                'completed',
            ]);
        }

        // Filter by registered vs self-found
        if ($request->filled('company_type')) {
            if ($request->company_type === 'self') {
                $query->whereNull('company_id');
            } elseif ($request->company_type === 'registered') {
                $query->whereNotNull('company_id');
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhere('supervisor_name', 'like', "%{$s}%")
                  ->orWhereHas('student', function ($st) use ($s) {
                      $st->where('first_name', 'like', "%{$s}%")
                         ->orWhere('last_name', 'like', "%{$s}%")
                         ->orWhere('student_number', 'like', "%{$s}%");
                  });
            });
        }

        $attachments = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'submitted'        => Attachment::where('status', 'submitted')->count(),
            'trainer_approved' => Attachment::where('status', 'trainer_approved')->count(),
            'active'           => Attachment::where('status', 'active')->count(),
            'needs_revision'   => Attachment::where('status', 'needs_revision')->count(),
            'completed'        => Attachment::whereIn('status', ['completed', 'graded'])->count(),
            'self_found'       => Attachment::whereNull('company_id')->count(),
            'registered'       => Attachment::whereNotNull('company_id')->count(),
        ];

        return view('ktvtc.ilo.attachments.index', compact('attachments', 'stats'));
    }

    public function iloShow(Attachment $attachment)
    {
        $attachment->load([
            'student', 'company', 'term', 'opportunity',
            'application', 'reviewer', 'grader',
            'trainerApprovedBy', 'trainerApprovedCourse',
        ]);

        return view('ktvtc.ilo.attachments.show', compact('attachment'));
    }

    public function iloApprove(Request $request, Attachment $attachment)
    {
        if (!$attachment->canBeActivated()) {
            return back()->with('error', 'This attachment cannot be activated from status: ' . $attachment->status);
        }

        $request->validate(['remarks' => 'nullable|string|max:500']);

        $attachment->approveAndActivate($request->remarks);

        Log::info('Attachment activated by ILO', [
            'attachment_id'  => $attachment->id,
            'by'             => auth()->id(),
            'was_trainer_approved' => $attachment->wasChanged('status') && $attachment->trainer_approved_by !== null,
        ]);

        return back()->with('success', 'Attachment approved and activated.');
    }

    public function iloRequestRevision(Request $request, Attachment $attachment)
    {
        $request->validate(['remarks' => 'required|string|min:5|max:500']);

        $attachment->requestRevision($request->remarks);

        return back()->with('warning', 'Revision requested. Student has been notified.');
    }

    public function iloReject(Request $request, Attachment $attachment)
    {
        $request->validate(['remarks' => 'required|string|min:5|max:500']);

        $attachment->reject($request->remarks);

        return back()->with('warning', 'Attachment rejected.');
    }

    public function iloComplete(Request $request, Attachment $attachment)
    {
        if (!$attachment->canBeCompleted()) {
            return back()->with('error', 'Only active attachments can be completed.');
        }

        $attachment->markCompleted($request->input('remarks'));

        return back()->with('success', 'Attachment marked as completed.');
    }

    public function iloGrade(Request $request, Attachment $attachment)
    {
        $request->validate([
            'grade'   => 'required|string|max:10',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $attachment->grade($request->grade, $request->remarks);

        return back()->with('success', 'Attachment graded.');
    }

    public function iloVisit(Request $request, Attachment $attachment)
    {
        $request->validate([
            'visit_date' => 'nullable|date',
            'notes'      => 'required|string|min:5|max:2000',
        ]);

        $attachment->logVisit($request->notes, $request->visit_date);

        return back()->with('success', 'Site visit logged.');
    }

    public function iloPromoteCompany(Attachment $attachment)
    {
        if ($attachment->company_id) {
            return back()->with('info', 'This attachment already links to a registered company.');
        }

        try {
            $company = Company::create([
                'name'             => $attachment->company_name,
                'sector'           => $attachment->company_sector,
                'physical_address' => $attachment->company_address,
                'company_phone'    => $attachment->company_phone,
                'company_email'    => $attachment->company_email,
                'is_active'        => true,
            ]);

            $attachment->company_id = $company->id;
            $attachment->save();

            Log::info('Self-found company promoted to registry', [
                'attachment_id' => $attachment->id,
                'company_id'    => $company->id,
            ]);

            return back()->with('success', 'Company added to registry and linked to this attachment.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to add company: ' . $e->getMessage());
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    protected function currentStudent(): Student
    {
        $user    = Auth::user();
        $student = Student::with('campus')->find($user->student_id);
        abort_unless($student, 404, 'No student record linked to this account.');
        return $student;
    }

    /**
     * Resolve which of the trainer's courses this attachment belongs to.
     * Returns null if none match.
     */
    protected function resolveTrainerCourseId(User $trainer, Attachment $attachment): ?int
    {
        $courseIds = $trainer->trainerCourseIds();

        if (empty($courseIds)) {
            return null;
        }

        $enrollment = $attachment->student
            ->enrollments()
            ->whereIn('course_id', $courseIds)
            ->first();

        return $enrollment?->course_id;
    }

    protected function validateAttachment(Request $request): array
    {
        return $request->validate([
            'company_id'          => 'nullable',
            'company_name'        => 'required|string|max:255',
            'company_sector'      => 'nullable|string|max:100',
            'company_address'     => 'nullable|string|max:255',
            'company_phone'       => 'nullable|string|max:20',
            'company_email'       => 'nullable|email|max:255',

            'supervisor_name'     => 'required|string|max:255',
            'supervisor_position' => 'nullable|string|max:100',
            'supervisor_phone'    => 'required|string|max:20',
            'supervisor_email'    => 'nullable|email|max:255',

            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after:start_date',

            'job_description'     => 'nullable|string|max:2000',
            'learning_objectives' => 'nullable|string|max:2000',
            'student_notes'       => 'nullable|string|max:2000',

            'acceptance_letter'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'placement_letter'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
    }

    protected function handleUploads(Request $request, Attachment $attachment): void
    {
        if ($request->hasFile('acceptance_letter')) {
            if ($attachment->acceptance_letter_path) {
                Storage::disk('public')->delete($attachment->acceptance_letter_path);
            }
            $attachment->acceptance_letter_path = $request->file('acceptance_letter')
                ->store('attachment-docs', 'public');
        }

        if ($request->hasFile('placement_letter')) {
            if ($attachment->placement_letter_path) {
                Storage::disk('public')->delete($attachment->placement_letter_path);
            }
            $attachment->placement_letter_path = $request->file('placement_letter')
                ->store('attachment-docs', 'public');
        }
    }
}