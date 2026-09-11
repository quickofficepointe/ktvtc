<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\AttachmentApplication;
use App\Models\Company;
use App\Models\Course;
use App\Models\Opportunity;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IloController extends Controller
{
    // ============================================================
    // DASHBOARD
    // ============================================================

    public function dashboard()
    {
        $stats = [
            'companies'            => rescue(fn () => Company::count(), 0),
            'active_companies'     => rescue(fn () => Company::active()->count(), 0),
            'open_opportunities'   => rescue(fn () => Opportunity::where('status', 'open')->count(), 0),
            'total_opportunities'  => rescue(fn () => Opportunity::count(), 0),
            'pending_applications' => rescue(fn () => AttachmentApplication::pending()->count(), 0),
            'approved_applications'=> rescue(fn () => AttachmentApplication::approved()->count(), 0),
            'total_applications'   => rescue(fn () => AttachmentApplication::count(), 0),
            'slots_offered'        => rescue(fn () => (int) Opportunity::where('status', 'open')->sum('slots'), 0),
            'slots_filled'         => rescue(fn () => (int) Opportunity::where('status', 'open')->sum('slots_taken'), 0),
        ];

        $recentCompanies = rescue(
            fn () => Company::latest()->limit(5)->get(),
            collect()
        );

        $recentApplications = rescue(
            fn () => AttachmentApplication::with(['opportunity.company', 'student'])
                ->latest()
                ->limit(8)
                ->get(),
            collect()
        );

        $openOpportunities = rescue(
            fn () => Opportunity::with(['company', 'course'])
                ->where('status', 'open')
                ->orderBy('application_closes_at')
                ->limit(6)
                ->get(),
            collect()
        );

        return view('ktvtc.ilo.dashboard', compact(
            'stats',
            'recentCompanies',
            'recentApplications',
            'openOpportunities'
        ));
    }

    // ============================================================
    // COMPANIES
    // ============================================================

    public function companies(Request $request)
    {
        $query = Company::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sector', 'like', "%{$search}%")
                  ->orWhere('contact_person_name', 'like', "%{$search}%")
                  ->orWhere('contact_person_email', 'like', "%{$search}%")
                  ->orWhere('town', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $companies = $query->withCount(['opportunities'])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $sectors = Company::query()
            ->whereNotNull('sector')
            ->distinct()
            ->orderBy('sector')
            ->pluck('sector');

        $stats = [
            'total'        => Company::count(),
            'active'       => Company::active()->count(),
            'with_open_ops'=> Opportunity::where('status', 'open')->distinct('company_id')->count('company_id'),
        ];

        return view('ktvtc.ilo.companies.index', compact('companies', 'sectors', 'stats'));
    }

    public function createCompany()
    {
        return view('ktvtc.ilo.companies.create');
    }

    public function storeCompany(Request $request)
    {
        $data = $this->validateCompany($request);

        try {
            $company = Company::create($data);

            Log::info('Company created', [
                'company_id' => $company->id,
                'name'       => $company->name,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('ilo.companies.show', $company)
                ->with('success', "Company '{$company->name}' registered.");
        } catch (\Throwable $e) {
            Log::error('Company create failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Failed to create company: ' . $e->getMessage())->withInput();
        }
    }

    public function showCompany(Company $company)
    {
        $company->load(['opportunities.course', 'opportunities.term']);

        $applications = AttachmentApplication::with(['opportunity', 'student'])
            ->whereHas('opportunity', fn ($q) => $q->where('company_id', $company->id))
            ->latest()
            ->limit(20)
            ->get();

        $stats = [
            'opportunities'  => $company->opportunities->count(),
            'open'           => $company->opportunities->where('status', 'open')->count(),
            'applications'   => $applications->count(),
            'approved'       => $applications->where('status', 'approved')->count(),
        ];

        return view('ktvtc.ilo.companies.show', compact('company', 'applications', 'stats'));
    }

    public function editCompany(Company $company)
    {
        return view('ktvtc.ilo.companies.edit', compact('company'));
    }

    public function updateCompany(Request $request, Company $company)
    {
        $data = $this->validateCompany($request, $company->id);

        try {
            $company->update($data);

            Log::info('Company updated', ['company_id' => $company->id, 'by' => auth()->id()]);

            return redirect()->route('ilo.companies.show', $company)
                ->with('success', 'Company updated.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to update: ' . $e->getMessage())->withInput();
        }
    }

    public function destroyCompany(Company $company)
    {
        if ($company->opportunities()->exists()) {
            return back()->with('error', 'Cannot delete: company has opportunities. Deactivate instead.');
        }

        $company->delete();

        return redirect()->route('ilo.companies.index')->with('success', 'Company deleted.');
    }

    // ============================================================
    // OPPORTUNITIES
    // ============================================================

    public function opportunities(Request $request)
    {
        $query = Opportunity::with(['company', 'course', 'term']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('company', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('course', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $opportunities = $query->latest()->paginate(20)->withQueryString();

        $companies = Company::active()->orderBy('name')->get(['id', 'name']);
        $courses   = Course::orderBy('name')->get(['id', 'name', 'code']);
        $terms     = AcademicTerm::orderByDesc('start_date')->get(['id', 'name', 'code']);

        $stats = [
            'total'   => Opportunity::count(),
            'open'    => Opportunity::where('status', 'open')->count(),
            'filled'  => Opportunity::where('status', 'filled')->count(),
            'draft'   => Opportunity::where('status', 'draft')->count(),
        ];

        return view('ktvtc.ilo.opportunities.index', compact(
            'opportunities', 'companies', 'courses', 'terms', 'stats'
        ));
    }

    public function createOpportunity(Request $request)
    {
        $companies = Company::active()->orderBy('name')->get(['id', 'name']);
        $courses   = Course::orderBy('name')->get(['id', 'name', 'code']);
        $terms     = AcademicTerm::orderByDesc('start_date')->get(['id', 'name', 'code']);

        $preselectedCompany = $request->filled('company_id')
            ? Company::find($request->company_id)
            : null;

        return view('ktvtc.ilo.opportunities.create', compact(
            'companies', 'courses', 'terms', 'preselectedCompany'
        ));
    }

    public function storeOpportunity(Request $request)
    {
        $data = $this->validateOpportunity($request);

        try {
            $opportunity = Opportunity::create($data);

            Log::info('Opportunity created', [
                'opportunity_id' => $opportunity->id,
                'company_id'     => $opportunity->company_id,
                'course_id'      => $opportunity->course_id,
                'slots'          => $opportunity->slots,
                'created_by'     => auth()->id(),
            ]);

            return redirect()->route('ilo.opportunities.show', $opportunity)
                ->with('success', 'Opportunity created. Publish it when ready.');
        } catch (\Throwable $e) {
            Log::error('Opportunity create failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to create opportunity: ' . $e->getMessage())->withInput();
        }
    }

    public function showOpportunity(Opportunity $opportunity)
    {
        $opportunity->load(['company', 'course', 'term', 'creator']);

        $applications = $opportunity->applications()
            ->with(['student'])
            ->latest()
            ->paginate(20);

        $stats = [
            'applications' => $opportunity->applications()->count(),
            'pending'      => $opportunity->applications()->where('status', 'pending')->count(),
            'approved'     => $opportunity->applications()->where('status', 'approved')->count(),
            'rejected'     => $opportunity->applications()->where('status', 'rejected')->count(),
            'remaining'    => $opportunity->slotsRemaining(),
        ];

        return view('ktvtc.ilo.opportunities.show', compact('opportunity', 'applications', 'stats'));
    }

    public function editOpportunity(Opportunity $opportunity)
    {
        $companies = Company::active()->orderBy('name')->get(['id', 'name']);
        $courses   = Course::orderBy('name')->get(['id', 'name', 'code']);
        $terms     = AcademicTerm::orderByDesc('start_date')->get(['id', 'name', 'code']);

        return view('ktvtc.ilo.opportunities.edit', compact('opportunity', 'companies', 'courses', 'terms'));
    }

    public function updateOpportunity(Request $request, Opportunity $opportunity)
    {
        $data = $this->validateOpportunity($request, $opportunity->id);

        try {
            $opportunity->update($data);
            return redirect()->route('ilo.opportunities.show', $opportunity)
                ->with('success', 'Opportunity updated.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to update: ' . $e->getMessage())->withInput();
        }
    }

    public function destroyOpportunity(Opportunity $opportunity)
    {
        if ($opportunity->applications()->exists()) {
            return back()->with('error', 'Cannot delete: applications exist. Close it instead.');
        }

        $opportunity->delete();
        return redirect()->route('ilo.opportunities.index')->with('success', 'Opportunity deleted.');
    }

    public function publishOpportunity(Opportunity $opportunity)
    {
        $opportunity->publish();

        Log::info('Opportunity published', ['id' => $opportunity->id, 'by' => auth()->id()]);

        return back()->with('success', 'Opportunity published. Students can now apply.');
    }

    public function closeOpportunity(Opportunity $opportunity)
    {
        $opportunity->close();

        return back()->with('success', 'Opportunity closed.');
    }

    // ============================================================
    // APPLICATIONS
    // ============================================================

    public function applications(Request $request)
    {
        $query = AttachmentApplication::with(['opportunity.company', 'opportunity.course', 'student']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('opportunity_id')) {
            $query->where('opportunity_id', $request->opportunity_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($s) use ($search) {
                    $s->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('student_number', 'like', "%{$search}%");
                });
            });
        }

        $applications = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pending'     => AttachmentApplication::pending()->count(),
            'shortlisted' => AttachmentApplication::where('status', 'shortlisted')->count(),
            'approved'    => AttachmentApplication::approved()->count(),
            'rejected'    => AttachmentApplication::where('status', 'rejected')->count(),
        ];

        return view('ktvtc.ilo.applications.index', compact('applications', 'stats'));
    }

    public function showApplication(AttachmentApplication $application)
    {
        $application->load([
            'opportunity.company',
            'opportunity.course',
            'opportunity.term',
            'student',
            'reviewer',
        ]);

        return view('ktvtc.ilo.applications.show', compact('application'));
    }

    public function approveApplication(Request $request, AttachmentApplication $application)
    {
        if (!$application->canBeReviewed()) {
            return back()->with('error', 'This application has already been reviewed.');
        }

        $opportunity = $application->opportunity;
        if ($opportunity->isFull()) {
            return back()->with('error', 'This opportunity is full — no remaining slots.');
        }

        DB::beginTransaction();
        try {
            $application->markApproved($request->input('remarks'));

            $opportunity->incrementSlotsTaken();

            DB::commit();

            Log::info('Application approved', [
                'application_id' => $application->id,
                'opportunity_id' => $opportunity->id,
                'student_id'     => $application->student_id,
                'approved_by'    => auth()->id(),
            ]);

            return back()->with('success', 'Application approved. Slot reserved.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    public function rejectApplication(Request $request, AttachmentApplication $application)
    {
        if (!$application->canBeReviewed()) {
            return back()->with('error', 'This application has already been reviewed.');
        }

        $request->validate([
            'remarks' => 'required|string|min:5|max:500',
        ]);

        try {
            $application->markRejected($request->remarks);

            Log::info('Application rejected', [
                'application_id' => $application->id,
                'by'             => auth()->id(),
                'reason'         => $request->remarks,
            ]);

            return back()->with('success', 'Application rejected.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to reject: ' . $e->getMessage());
        }
    }

    public function shortlistApplication(Request $request, AttachmentApplication $application)
    {
        if (!$application->canBeReviewed()) {
            return back()->with('error', 'This application has already been reviewed.');
        }

        try {
            $application->markShortlisted($request->input('remarks'));
            return back()->with('success', 'Application shortlisted.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to shortlist: ' . $e->getMessage());
        }
    }

    // ============================================================
    // ATTACHMENTS (placeholder — Phase 2)
    // ============================================================

    public function attachments()
    {
        // Placeholder — will list approved placements once the `attachments` table exists
        return view('ktvtc.ilo.attachments.index', [
            'placements' => collect(),
        ]);
    }

    public function showAttachment($attachment)
    {
        abort(404, 'Attachment module coming soon.');
    }

    public function approveAttachment($attachment)
    {
        return back()->with('info', 'Attachment approval coming soon.');
    }

    public function gradeAttachment($attachment)
    {
        return back()->with('info', 'Attachment grading coming soon.');
    }

    public function logVisit($attachment)
    {
        return back()->with('info', 'Site visit logging coming soon.');
    }

    // ============================================================
    // VALIDATION HELPERS
    // ============================================================

    protected function validateCompany(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'                    => 'required|string|max:255',
            'sector'                  => 'nullable|string|max:100',
            'website'                 => 'nullable|url|max:255',
            'physical_address'        => 'nullable|string|max:255',
            'postal_address'          => 'nullable|string|max:255',
            'town'                    => 'nullable|string|max:100',
            'county'                  => 'nullable|string|max:100',
            'contact_person_name'     => 'nullable|string|max:255',
            'contact_person_position' => 'nullable|string|max:100',
            'contact_person_phone'    => 'nullable|string|max:20',
            'contact_person_email'    => 'nullable|email|max:255',
            'company_phone'           => 'nullable|string|max:20',
            'company_email'           => 'nullable|email|max:255',
            'size'                    => 'nullable|in:small,medium,large',
            'is_active'               => 'boolean',
            'notes'                   => 'nullable|string',
        ]);
    }

    protected function validateOpportunity(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'company_id'             => 'required|exists:companies,id',
            'course_id'              => 'required|exists:courses,id',
            'term_id'                => 'nullable|exists:academic_terms,id',
            'slots'                  => 'required|integer|min:1|max:500',
            'application_opens_at'   => 'nullable|date',
            'application_closes_at'  => 'nullable|date|after_or_equal:application_opens_at',
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'title'                  => 'nullable|string|max:255',
            'description'            => 'nullable|string',
            'requirements'           => 'nullable|string',
            'location'               => 'nullable|string|max:255',
            'is_paid'                => 'boolean',
            'provides_transport'     => 'boolean',
            'status'                 => 'required|in:draft,open,closed,filled',
            'is_active'              => 'boolean',
        ]);

        $data['is_paid']             = $request->boolean('is_paid');
        $data['provides_transport']  = $request->boolean('provides_transport');
        $data['is_active']           = $request->boolean('is_active', true);

        return $data;
    }
}