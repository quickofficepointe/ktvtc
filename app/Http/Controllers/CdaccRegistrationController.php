<?php

namespace App\Http\Controllers;

use App\Models\CdaccRegistration;
use App\Models\Registration;
use App\Models\User;
use App\Models\Course;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CdaccRegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CdaccRegistration::with(['student', 'course', 'registration'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('cdacc_status')) {
            $query->where('cdacc_status', $request->cdacc_status);
        }

        if ($request->filled('certification_status')) {
            $query->where('certification_status', $request->certification_status);
        }

        if ($request->filled('program_code')) {
            $query->where('cdacc_program_code', $request->program_code);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cdacc_registration_number', 'like', "%{$search}%")
                    ->orWhere('cdacc_index_number', 'like', "%{$search}%")
                    ->orWhere('cdacc_learner_id', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $cdaccRegistrations = $query->paginate(20);

        return view('ktvtc.admin.cdacc.registrations.index', compact('cdaccRegistrations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Get available registrations that don't have CDACC registration yet
        $registrations = Registration::with(['student', 'course'])
            ->whereDoesntHave('cdaccRegistration')
            ->where('status', 'active')
            ->get();

        $courses = Course::where('is_cdacc', true)->get();
        $feeStructures = FeeStructure::where('is_cdacc', true)->get();

        $selectedRegistration = null;
        if ($request->filled('registration_id')) {
            $selectedRegistration = Registration::find($request->registration_id);
        }

        return view('admin.cdacc.registrations.create', compact(
            'registrations',
            'courses',
            'feeStructures',
            'selectedRegistration'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'registration_id' => 'required|exists:registrations,id|unique:cdacc_registrations,registration_id',
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'cdacc_program_code' => 'required|string|max:20',
            'cdacc_program_name' => 'required|string|max:255',
            'cdacc_qualification_title' => 'required|string|max:255',
            'cdacc_qualification_level' => 'required|in:artisan,certificate,diploma,higher_diploma',
            'cdacc_trade_area' => 'required|string|max:255',
            'cdacc_occupation' => 'nullable|string|max:255',
            'cdacc_registration_date' => 'required|date',
            'cdacc_registration_expiry' => 'required|date|after:cdacc_registration_date',
            'cdacc_center_number' => 'required|string|max:50',
            'cdacc_center_name' => 'required|string|max:255',
            'cdacc_assessor_number' => 'nullable|string|max:50',
            'cdacc_moderator_number' => 'nullable|string|max:50',
            'cdacc_registration_fee' => 'numeric|min:0',
            'cdacc_examination_fee' => 'numeric|min:0',
            'cdacc_certification_fee' => 'numeric|min:0',
            'cdacc_moderation_fee' => 'numeric|min:0',
            'assessment_type' => 'required|in:cba,written,practical,oral,portfolio',
            'assessment_venue' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Calculate total fee
            $totalFee = $request->cdacc_registration_fee
                + $request->cdacc_examination_fee
                + $request->cdacc_certification_fee
                + $request->cdacc_moderation_fee;

            $cdaccRegistration = CdaccRegistration::create([
                'registration_id' => $request->registration_id,
                'student_id' => $request->student_id,
                'course_id' => $request->course_id,
                'fee_structure_id' => $request->fee_structure_id,
                'cdacc_program_code' => $request->cdacc_program_code,
                'cdacc_program_name' => $request->cdacc_program_name,
                'cdacc_qualification_title' => $request->cdacc_qualification_title,
                'cdacc_qualification_level' => $request->cdacc_qualification_level,
                'cdacc_trade_area' => $request->cdacc_trade_area,
                'cdacc_occupation' => $request->cdacc_occupation,
                'cdacc_registration_date' => $request->cdacc_registration_date,
                'cdacc_registration_expiry' => $request->cdacc_registration_expiry,
                'cdacc_center_number' => $request->cdacc_center_number,
                'cdacc_center_name' => $request->cdacc_center_name,
                'cdacc_assessor_number' => $request->cdacc_assessor_number,
                'cdacc_moderator_number' => $request->cdacc_moderator_number,
                'cdacc_registration_fee' => $request->cdacc_registration_fee,
                'cdacc_examination_fee' => $request->cdacc_examination_fee,
                'cdacc_certification_fee' => $request->cdacc_certification_fee,
                'cdacc_moderation_fee' => $request->cdacc_moderation_fee,
                'cdacc_total_fee' => $totalFee,
                'assessment_type' => $request->assessment_type,
                'assessment_venue' => $request->assessment_venue,
                'cdacc_status' => 'pending',
                'processed_by' => auth()->id(),
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('admin.cdacc.registrations.show', $cdaccRegistration)
                ->with('success', 'CDACC registration created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create CDACC registration: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CdaccRegistration $cdaccRegistration)
    {
        $cdaccRegistration->load([
            'registration',
            'student',
            'course',
            'feeStructure',
            'processor',
            'approver'
        ]);

        // Parse JSON fields for display
        $registeredModules = $cdaccRegistration->registered_modules ?? [];
        $moduleResults = $cdaccRegistration->module_results ?? [];
        $assessmentComponents = $cdaccRegistration->assessment_components ?? [];

        // Calculate statistics
        $moduleSummary = $cdaccRegistration->getModulesSummary();

        return view('admin.cdacc.registrations.show', compact(
            'cdaccRegistration',
            'registeredModules',
            'moduleResults',
            'assessmentComponents',
            'moduleSummary'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CdaccRegistration $cdaccRegistration)
    {
        $cdaccRegistration->load(['registration', 'student', 'course', 'feeStructure']);

        $courses = Course::where('is_cdacc', true)->get();
        $feeStructures = FeeStructure::where('is_cdacc', true)->get();

        return view('admin.cdacc.registrations.edit', compact(
            'cdaccRegistration',
            'courses',
            'feeStructures'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CdaccRegistration $cdaccRegistration)
    {
        $validator = Validator::make($request->all(), [
            'cdacc_program_code' => 'required|string|max:20',
            'cdacc_program_name' => 'required|string|max:255',
            'cdacc_qualification_title' => 'required|string|max:255',
            'cdacc_qualification_level' => 'required|in:artisan,certificate,diploma,higher_diploma',
            'cdacc_trade_area' => 'required|string|max:255',
            'cdacc_occupation' => 'nullable|string|max:255',
            'cdacc_registration_date' => 'required|date',
            'cdacc_registration_expiry' => 'required|date|after:cdacc_registration_date',
            'cdacc_examination_date' => 'nullable|date|after_or_equal:cdacc_registration_date',
            'cdacc_certification_date' => 'nullable|date|after_or_equal:cdacc_registration_date',
            'cdacc_center_number' => 'required|string|max:50',
            'cdacc_center_name' => 'required|string|max:255',
            'cdacc_assessor_number' => 'nullable|string|max:50',
            'cdacc_moderator_number' => 'nullable|string|max:50',
            'cdacc_registration_fee' => 'numeric|min:0',
            'cdacc_examination_fee' => 'numeric|min:0',
            'cdacc_certification_fee' => 'numeric|min:0',
            'cdacc_moderation_fee' => 'numeric|min:0',
            'assessment_type' => 'required|in:cba,written,practical,oral,portfolio',
            'assessment_venue' => 'nullable|string|max:255',
            'cdacc_status' => 'required|in:pending,submitted,approved,registered,active,under_assessment,completed,certified,suspended,withdrawn,expired',
            'certification_status' => 'required|in:not_applicable,pending,eligible,awarded,withheld,revoked',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Calculate total fee
            $totalFee = $request->cdacc_registration_fee
                + $request->cdacc_examination_fee
                + $request->cdacc_certification_fee
                + $request->cdacc_moderation_fee;

            $cdaccRegistration->update([
                'cdacc_program_code' => $request->cdacc_program_code,
                'cdacc_program_name' => $request->cdacc_program_name,
                'cdacc_qualification_title' => $request->cdacc_qualification_title,
                'cdacc_qualification_level' => $request->cdacc_qualification_level,
                'cdacc_trade_area' => $request->cdacc_trade_area,
                'cdacc_occupation' => $request->cdacc_occupation,
                'cdacc_registration_date' => $request->cdacc_registration_date,
                'cdacc_registration_expiry' => $request->cdacc_registration_expiry,
                'cdacc_examination_date' => $request->cdacc_examination_date,
                'cdacc_certification_date' => $request->cdacc_certification_date,
                'cdacc_center_number' => $request->cdacc_center_number,
                'cdacc_center_name' => $request->cdacc_center_name,
                'cdacc_assessor_number' => $request->cdacc_assessor_number,
                'cdacc_moderator_number' => $request->cdacc_moderator_number,
                'cdacc_registration_fee' => $request->cdacc_registration_fee,
                'cdacc_examination_fee' => $request->cdacc_examination_fee,
                'cdacc_certification_fee' => $request->cdacc_certification_fee,
                'cdacc_moderation_fee' => $request->cdacc_moderation_fee,
                'cdacc_total_fee' => $totalFee,
                'assessment_type' => $request->assessment_type,
                'assessment_venue' => $request->assessment_venue,
                'cdacc_status' => $request->cdacc_status,
                'certification_status' => $request->certification_status,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('admin.cdacc.registrations.show', $cdaccRegistration)
                ->with('success', 'CDACC registration updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update CDACC registration: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CdaccRegistration $cdaccRegistration)
    {
        if (!in_array($cdaccRegistration->cdacc_status, ['pending', 'draft'])) {
            return redirect()->back()
                ->with('error', 'Cannot delete CDACC registration with status: ' . $cdaccRegistration->cdacc_status);
        }

        $cdaccRegistration->delete();

        return redirect()->route('admin.cdacc.registrations.index')
            ->with('success', 'CDACC registration deleted successfully.');
    }

    /**
     * Submit registration to CDACC
     */
    public function submitToCdacc(CdaccRegistration $cdaccRegistration)
    {
        if ($cdaccRegistration->cdacc_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending registrations can be submitted to CDACC.');
        }

        $cdaccRegistration->submitToCdacc();

        // Here you would typically call CDACC API to submit the registration
        // $cdaccRegistration->sync_status = 'pending';
        // $cdaccRegistration->save();

        return redirect()->back()
            ->with('success', 'Registration submitted to CDACC successfully.');
    }

    /**
     * Approve CDACC registration
     */
    public function approve(CdaccRegistration $cdaccRegistration)
    {
        if ($cdaccRegistration->cdacc_status !== 'submitted') {
            return redirect()->back()
                ->with('error', 'Only submitted registrations can be approved.');
        }

        $cdaccRegistration->approveCdaccRegistration();

        return redirect()->back()
            ->with('success', 'CDACC registration approved successfully.');
    }

    /**
     * Register modules
     */
    public function registerModule(Request $request, CdaccRegistration $cdaccRegistration)
    {
        $validator = Validator::make($request->all(), [
            'module_code' => 'required|string|max:50',
            'module_name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
            'type' => 'required|in:core,elective',
            'exam_series' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $cdaccRegistration->registerModule(
            $request->module_code,
            $request->module_name,
            $request->credits,
            $request->type,
            $request->exam_series
        );

        return redirect()->back()
            ->with('success', 'Module registered successfully.');
    }

    /**
     * Remove module
     */
    public function removeModule(CdaccRegistration $cdaccRegistration, $moduleIndex)
    {
        $modules = $cdaccRegistration->registered_modules ?? [];

        if (!isset($modules[$moduleIndex])) {
            return redirect()->back()
                ->with('error', 'Module not found.');
        }

        array_splice($modules, $moduleIndex, 1);

        $cdaccRegistration->update([
            'registered_modules' => $modules,
            'total_modules' => count($modules),
            'core_modules' => count(array_filter($modules, fn($m) => ($m['type'] ?? '') === 'core')),
            'elective_modules' => count(array_filter($modules, fn($m) => ($m['type'] ?? '') === 'elective')),
        ]);

        return redirect()->back()
            ->with('success', 'Module removed successfully.');
    }

    /**
     * Record module result
     */
    public function recordResult(Request $request, CdaccRegistration $cdaccRegistration)
    {
        $validator = Validator::make($request->all(), [
            'module_code' => 'required|string|max:50',
            'score' => 'required|numeric|min:0|max:100',
            'grade' => 'required|string|in:A,B,C,D,E',
            'remarks' => 'nullable|string|max:500',
            'assessment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $cdaccRegistration->recordModuleResult(
            $request->module_code,
            $request->score,
            $request->grade,
            $request->remarks,
            $request->assessment_date
        );

        return redirect()->back()
            ->with('success', 'Module result recorded successfully.');
    }

    /**
     * Calculate overall results
     */
    public function calculateResults(CdaccRegistration $cdaccRegistration)
    {
        $cdaccRegistration->calculateOverallResults();

        return redirect()->back()
            ->with('success', 'Overall results calculated successfully.');
    }

    /**
     * Generate certificate
     */
    public function generateCertificate(CdaccRegistration $cdaccRegistration)
    {
        if ($cdaccRegistration->competency_level !== 'Competent') {
            return redirect()->back()
                ->with('error', 'Student must be competent to generate certificate.');
        }

        // Here you would generate the certificate PDF
        // For now, just update the status

        $cdaccRegistration->update([
            'certification_status' => 'awarded',
            'cdacc_certification_date' => now(),
            'cdacc_status' => 'certified',
        ]);

        return redirect()->back()
            ->with('success', 'Certificate generated successfully.');
    }

    /**
     * Award certificate
     */
    public function awardCertificate(CdaccRegistration $cdaccRegistration)
    {
        $cdaccRegistration->update([
            'certification_status' => 'awarded',
            'cdacc_certification_date' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Certificate awarded successfully.');
    }

    /**
     * Revoke certificate
     */
    public function revokeCertificate(CdaccRegistration $cdaccRegistration)
    {
        $cdaccRegistration->update([
            'certification_status' => 'revoked',
            'cdacc_status' => 'suspended',
        ]);

        return redirect()->back()
            ->with('success', 'Certificate revoked successfully.');
    }

    /**
     * Download certificate
     */
    public function downloadCertificate(CdaccRegistration $cdaccRegistration)
    {
        if ($cdaccRegistration->certification_status !== 'awarded') {
            return redirect()->back()
                ->with('error', 'Certificate not available for download.');
        }

        // Here you would return the certificate PDF
        // For now, redirect back

        return redirect()->back()
            ->with('info', 'Certificate download functionality will be implemented soon.');
    }

    /**
     * CDACC registration report
     */
    public function registrationReport(Request $request)
    {
        $query = CdaccRegistration::with(['student', 'course'])
            ->orderBy('cdacc_registration_date', 'desc');

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('cdacc_registration_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('cdacc_registration_date', '<=', $request->end_date);
        }

        if ($request->filled('program_code')) {
            $query->where('cdacc_program_code', $request->program_code);
        }

        if ($request->filled('qualification_level')) {
            $query->where('cdacc_qualification_level', $request->qualification_level);
        }

        $registrations = $query->get();

        // Group by program
        $byProgram = $registrations->groupBy('cdacc_program_code')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'program_name' => $group->first()->cdacc_program_name,
                ];
            });

        // Group by status
        $byStatus = $registrations->groupBy('cdacc_status')
            ->map->count();

        return view('admin.cdacc.reports.registrations', compact(
            'registrations',
            'byProgram',
            'byStatus'
        ));
    }

    /**
     * Result report
     */
    public function resultReport(Request $request)
    {
        $query = CdaccRegistration::with(['student', 'course'])
            ->whereNotNull('module_results')
            ->orderBy('updated_at', 'desc');

        // Apply filters
        if ($request->filled('program_code')) {
            $query->where('cdacc_program_code', $request->program_code);
        }

        if ($request->filled('competency_level')) {
            $query->where('competency_level', $request->competency_level);
        }

        $registrations = $query->get();

        // Calculate statistics
        $totalStudents = $registrations->count();
        $competentStudents = $registrations->where('competency_level', 'Competent')->count();
        $competencyRate = $totalStudents > 0 ? ($competentStudents / $totalStudents) * 100 : 0;

        // Grade distribution
        $gradeDistribution = $registrations->groupBy('overall_grade')
            ->map->count()
            ->sortDesc();

        return view('admin.cdacc.reports.results', compact(
            'registrations',
            'totalStudents',
            'competentStudents',
            'competencyRate',
            'gradeDistribution'
        ));
    }

    /**
     * Certification report
     */
    public function certificationReport(Request $request)
    {
        $query = CdaccRegistration::with(['student', 'course'])
            ->where('certification_status', '!=', 'not_applicable')
            ->orderBy('cdacc_certification_date', 'desc');

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('cdacc_certification_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('cdacc_certification_date', '<=', $request->end_date);
        }

        if ($request->filled('certification_status')) {
            $query->where('certification_status', $request->certification_status);
        }

        $registrations = $query->get();

        // Statistics
        $totalCertificates = $registrations->where('certification_status', 'awarded')->count();
        $pendingCertificates = $registrations->where('certification_status', 'pending')->count();
        $revokedCertificates = $registrations->where('certification_status', 'revoked')->count();

        // By program
        $byProgram = $registrations->groupBy('cdacc_program_code')
            ->map(function ($group) {
                return [
                    'awarded' => $group->where('certification_status', 'awarded')->count(),
                    'pending' => $group->where('certification_status', 'pending')->count(),
                    'total' => $group->count(),
                ];
            });

        return view('admin.cdacc.reports.certifications', compact(
            'registrations',
            'totalCertificates',
            'pendingCertificates',
            'revokedCertificates',
            'byProgram'
        ));
    }

    /**
     * Program performance report
     */
    public function programPerformanceReport(Request $request)
    {
        $query = CdaccRegistration::with(['course'])
            ->whereNotNull('overall_score')
            ->orderBy('cdacc_program_code');

        if ($request->filled('program_code')) {
            $query->where('cdacc_program_code', $request->program_code);
        }

        $registrations = $query->get();

        // Group by program
        $programStats = $registrations->groupBy('cdacc_program_code')
            ->map(function ($group) {
                $scores = $group->pluck('overall_score')->filter();
                $competentCount = $group->where('competency_level', 'Competent')->count();

                return [
                    'program_name' => $group->first()->cdacc_program_name,
                    'total_students' => $group->count(),
                    'competent_students' => $competentCount,
                    'competency_rate' => $group->count() > 0 ? ($competentCount / $group->count()) * 100 : 0,
                    'average_score' => $scores->isNotEmpty() ? round($scores->avg(), 2) : null,
                    'highest_score' => $scores->isNotEmpty() ? $scores->max() : null,
                    'lowest_score' => $scores->isNotEmpty() ? $scores->min() : null,
                ];
            });

        // Overall statistics
        $overallStats = [
            'total_students' => $registrations->count(),
            'total_competent' => $registrations->where('competency_level', 'Competent')->count(),
            'overall_competency_rate' => $registrations->count() > 0
                ? ($registrations->where('competency_level', 'Competent')->count() / $registrations->count()) * 100
                : 0,
            'average_score' => $registrations->pluck('overall_score')->filter()->isNotEmpty()
                ? round($registrations->pluck('overall_score')->filter()->avg(), 2)
                : null,
        ];

        return view('admin.cdacc.reports.program-performance', compact(
            'programStats',
            'overallStats'
        ));
    }

    /**
     * Sync with CDACC API
     */
    public function sync(Request $request)
    {
        $pendingSync = CdaccRegistration::where('sync_status', 'pending')
            ->orWhere('sync_status', 'failed')
            ->orderBy('created_at')
            ->get();

        $successfulSync = CdaccRegistration::where('sync_status', 'success')
            ->orderBy('last_sync_with_cdacc_at', 'desc')
            ->take(10)
            ->get();

        $syncStats = [
            'pending' => $pendingSync->count(),
            'success' => CdaccRegistration::where('sync_status', 'success')->count(),
            'failed' => CdaccRegistration::where('sync_status', 'failed')->count(),
            'total' => CdaccRegistration::count(),
        ];

        return view('admin.cdacc.sync.index', compact(
            'pendingSync',
            'successfulSync',
            'syncStats'
        ));
    }

    /**
     * Submit batch to CDACC
     */
    public function submitBatch(Request $request)
    {
        $registrationIds = $request->input('registration_ids', []);

        if (empty($registrationIds)) {
            return redirect()->back()
                ->with('error', 'No registrations selected.');
        }

        $registrations = CdaccRegistration::whereIn('id', $registrationIds)
            ->where('cdacc_status', 'approved')
            ->whereIn('sync_status', ['pending', 'failed'])
            ->get();

        if ($registrations->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No eligible registrations found for sync.');
        }

        foreach ($registrations as $registration) {
            // Here you would call the CDACC API
            // For now, simulate success
            $registration->update([
                'sync_status' => 'success',
                'last_sync_with_cdacc_at' => now(),
                'cdacc_api_reference' => 'CDACC-' . time() . '-' . $registration->id,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Batch submitted to CDACC successfully.');
    }

    /**
     * Check sync status
     */
    public function checkStatus(Request $request)
    {
        $registrationIds = $request->input('registration_ids', []);

        if (empty($registrationIds)) {
            return redirect()->back()
                ->with('error', 'No registrations selected.');
        }

        $registrations = CdaccRegistration::whereIn('id', $registrationIds)
            ->where('sync_status', 'pending')
            ->get();

        foreach ($registrations as $registration) {
            // Here you would check status from CDACC API
            // For now, simulate status check
            $registration->update([
                'sync_status' => 'success',
                'last_sync_with_cdacc_at' => now(),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Sync status checked successfully.');
    }

    /**
     * Update results from CDACC
     */
    public function updateResults(Request $request)
    {
        $registrationIds = $request->input('registration_ids', []);

        if (empty($registrationIds)) {
            return redirect()->back()
                ->with('error', 'No registrations selected.');
        }

        $registrations = CdaccRegistration::whereIn('id', $registrationIds)
            ->where('cdacc_status', 'under_assessment')
            ->get();

        foreach ($registrations as $registration) {
            // Here you would fetch results from CDACC API
            // For now, simulate results update
            $registration->update([
                'cdacc_status' => 'completed',
                'certification_status' => 'eligible',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Results updated successfully.');
    }

    /**
     * View sync logs
     */
    public function syncLogs(Request $request)
    {
        $query = CdaccRegistration::with(['student', 'course'])
            ->whereNotNull('last_sync_with_cdacc_at')
            ->orderBy('last_sync_with_cdacc_at', 'desc');

        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->sync_status);
        }

        if ($request->filled('start_date')) {
            $query->where('last_sync_with_cdacc_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('last_sync_with_cdacc_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(20);

        return view('admin.cdacc.sync.logs', compact('logs'));
    }
}
