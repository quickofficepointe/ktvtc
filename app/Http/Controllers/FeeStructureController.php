<?php
// app/Http/Controllers/FeeStructureController.php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Campus;
use App\Models\Course;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FeeStructureController extends Controller
{
    // ============================================================
    // INDEX — list all fee structures grouped by course
    // ============================================================

    public function index(Request $request)
    {
        // Determine the scope of the query
        $query = FeeStructure::with(['course', 'campus', 'term'])
            ->orderBy('course_id')
            ->orderBy('term_id');

        // Filter by term
        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter by campus
        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'yes');
        }

        // Search by course name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('course', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $feeStructures = $query->paginate(25)->withQueryString();

        // Stats
        $stats = [
            'total'      => FeeStructure::count(),
            'active'     => FeeStructure::where('is_active', true)->count(),
            'per_term'   => FeeStructure::whereNotNull('term_id')->count(),
            'legacy'     => FeeStructure::whereNull('term_id')->count(),
        ];

        // Filter dropdowns
        $courses = Course::orderBy('name')->get(['id', 'name', 'code']);
        $terms   = AcademicTerm::ordered()->get(['id', 'name', 'code', 'academic_year', 'term_number']);
        $campuses = Campus::orderBy('name')->get(['id', 'name']);

        return view('ktvtc.finance.fee-structure.index', compact(
            'feeStructures',
            'stats',
            'courses',
            'terms',
            'campuses'
        ));
    }

    // ============================================================
    // COURSE TERMS — all terms for a single course
    // ============================================================

    public function courseTerms(Course $course)
    {
        $feeStructures = FeeStructure::with(['term', 'campus'])
            ->where('course_id', $course->id)
            ->orderBy('term_id')
            ->get();

        $termsWithFees = $feeStructures->keyBy('term_id');
        $allTerms = AcademicTerm::ordered()->active()->get();

        return view('ktvtc.finance.fee-structure.course-terms', compact(
            'course',
            'feeStructures',
            'termsWithFees',
            'allTerms'
        ));
    }

    // ============================================================
    // CREATE — create a new fee structure for a course + term
    // ============================================================

    public function create(Request $request, Course $course)
    {
        if (!Course::canUserModifyFees(auth()->user())) {
            abort(403, 'You do not have permission to modify fee structures.');
        }

        // Which term is selected (from query string)
        $termId = $request->get('term_id');

        $terms   = AcademicTerm::ordered()->active()->get();
        $campuses = Campus::orderBy('name')->get();

        return view('ktvtc.finance.fee-structure.create', compact(
            'course',
            'terms',
            'campuses',
            'termId'
        ));
    }

    // ============================================================
    // STORE — save a new fee structure
    // ============================================================

    public function store(Request $request)
    {
        if (!Course::canUserModifyFees(auth()->user())) {
            abort(403, 'You do not have permission to modify fee structures.');
        }

        $validator = Validator::make($request->all(), [
            'course_id'                => 'required|exists:courses,id',
            'term_id'                  => 'required|exists:academic_terms,id',
            'campus_id'                => 'nullable|exists:campuses,id',
            'tuition_per_month'        => 'required|numeric|min:0',
            'registration_fee'         => 'nullable|numeric|min:0',
            'caution_money'            => 'nullable|numeric|min:0',
            'student_id_fee'           => 'nullable|numeric|min:0',
            'library_fee'              => 'nullable|numeric|min:0',
            'medical_fee'              => 'nullable|numeric|min:0',
            'sports_fee'               => 'nullable|numeric|min:0',
            'activity_fee'             => 'nullable|numeric|min:0',
            'workshop_levy'            => 'nullable|numeric|min:0',
            'practical_materials'      => 'nullable|numeric|min:0',
            'tool_kit_deposit'         => 'nullable|numeric|min:0',
            'protective_clothing'      => 'nullable|numeric|min:0',
            'industrial_attachment_fee'=> 'nullable|numeric|min:0',
            'cdacc_registration_fee'   => 'nullable|numeric|min:0',
            'cdacc_examination_fee'    => 'nullable|numeric|min:0',
            'cdacc_certification_fee'  => 'nullable|numeric|min:0',
            'tvet_authority_levy'      => 'nullable|numeric|min:0',
            'trade_test_fee'           => 'nullable|numeric|min:0',
            'total_course_months'      => 'required|integer|min:1',
            'has_government_sponsorship' => 'boolean',
            'government_subsidy_amount'  => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Prevent duplicate: one fee structure per (course, term, campus)
        $existing = FeeStructure::where('course_id', $request->course_id)
            ->where('term_id', $request->term_id)
            ->where('campus_id', $request->campus_id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', 'A fee structure already exists for this course and term. Edit the existing one instead.')
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->only([
                'course_id', 'term_id', 'campus_id', 'total_course_months',
                'tuition_per_month', 'registration_fee', 'caution_money',
                'student_id_fee', 'library_fee', 'medical_fee', 'sports_fee',
                'activity_fee', 'workshop_levy', 'practical_materials',
                'tool_kit_deposit', 'protective_clothing', 'industrial_attachment_fee',
                'cdacc_registration_fee', 'cdacc_examination_fee',
                'cdacc_certification_fee', 'tvet_authority_levy', 'trade_test_fee',
                'government_subsidy_amount',
            ]);

            // Booleans + defaults
            $data['has_government_sponsorship'] = $request->boolean('has_government_sponsorship');
            $data['is_active']  = true;
            $data['is_approved'] = true; // Finance creates it directly
            $data['approved_by'] = auth()->id();
            $data['academic_year'] = AcademicTerm::find($request->term_id)?->academic_year;

            // Fill in any missing numeric fields with 0
            $numericFields = [
                'registration_fee', 'caution_money', 'student_id_fee',
                'library_fee', 'medical_fee', 'sports_fee', 'activity_fee',
                'workshop_levy', 'practical_materials', 'tool_kit_deposit',
                'protective_clothing', 'industrial_attachment_fee',
                'cdacc_registration_fee', 'cdacc_examination_fee',
                'cdacc_certification_fee', 'tvet_authority_levy', 'trade_test_fee',
                'government_subsidy_amount',
            ];
            foreach ($numericFields as $field) {
                if (!isset($data[$field]) || $data[$field] === null) {
                    $data[$field] = 0;
                }
            }

            // Create and calculate
            $feeStructure = FeeStructure::create($data);
            $feeStructure->updateCalculatedFields();

            DB::commit();

            Log::info('Fee structure created', [
                'fee_structure_id' => $feeStructure->id,
                'course_id'        => $feeStructure->course_id,
                'term_id'          => $feeStructure->term_id,
                'total_course_fee' => $feeStructure->total_course_fee,
                'created_by'       => auth()->id(),
            ]);

            return redirect()->route('finance.fee-structure.course-terms', $feeStructure->course_id)
                ->with('success', 'Fee structure created for ' . $feeStructure->term->name . '.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Fee structure creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ============================================================
    // SHOW
    // ============================================================

    public function show(FeeStructure $feeStructure)
    {
        $feeStructure->load(['course', 'term', 'campus', 'approver']);

        $formattedBreakdown = $this->buildBreakdown($feeStructure);

        // How many enrollments use this fee?
        $activeEnrollments = \App\Models\Enrollment::where('course_id', $feeStructure->course_id)
            ->where('term_id', $feeStructure->term_id)
            ->where('status', 'active')
            ->count();

        return view('ktvtc.finance.fee-structure.show', compact(
            'feeStructure',
            'formattedBreakdown',
            'activeEnrollments'
        ));
    }

    // ============================================================
    // EDIT
    // ============================================================

    public function edit(FeeStructure $feeStructure)
    {
        if (!Course::canUserModifyFees(auth()->user())) {
            abort(403, 'You do not have permission to modify fee structures.');
        }

        $feeStructure->load(['course', 'term', 'campus']);
        $terms = AcademicTerm::ordered()->active()->get();
        $campuses = Campus::orderBy('name')->get();

        return view('ktvtc.finance.fee-structure.edit', compact(
            'feeStructure',
            'terms',
            'campuses'
        ));
    }

    // ============================================================
    // UPDATE
    // ============================================================

    public function update(Request $request, FeeStructure $feeStructure)
    {
        if (!Course::canUserModifyFees(auth()->user())) {
            abort(403, 'You do not have permission to modify fee structures.');
        }

        $validator = Validator::make($request->all(), [
            'term_id'                  => 'required|exists:academic_terms,id',
            'campus_id'                => 'nullable|exists:campuses,id',
            'tuition_per_month'        => 'required|numeric|min:0',
            'registration_fee'         => 'nullable|numeric|min:0',
            'caution_money'            => 'nullable|numeric|min:0',
            'student_id_fee'           => 'nullable|numeric|min:0',
            'library_fee'              => 'nullable|numeric|min:0',
            'medical_fee'              => 'nullable|numeric|min:0',
            'sports_fee'               => 'nullable|numeric|min:0',
            'activity_fee'             => 'nullable|numeric|min:0',
            'workshop_levy'            => 'nullable|numeric|min:0',
            'practical_materials'      => 'nullable|numeric|min:0',
            'tool_kit_deposit'         => 'nullable|numeric|min:0',
            'protective_clothing'      => 'nullable|numeric|min:0',
            'industrial_attachment_fee'=> 'nullable|numeric|min:0',
            'cdacc_registration_fee'   => 'nullable|numeric|min:0',
            'cdacc_examination_fee'    => 'nullable|numeric|min:0',
            'cdacc_certification_fee'  => 'nullable|numeric|min:0',
            'tvet_authority_levy'      => 'nullable|numeric|min:0',
            'trade_test_fee'           => 'nullable|numeric|min:0',
            'total_course_months'      => 'required|integer|min:1',
            'has_government_sponsorship' => 'boolean',
            'government_subsidy_amount'  => 'nullable|numeric|min:0',
            'modification_reason'      => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->only([
                'term_id', 'campus_id', 'total_course_months',
                'tuition_per_month', 'registration_fee', 'caution_money',
                'student_id_fee', 'library_fee', 'medical_fee', 'sports_fee',
                'activity_fee', 'workshop_levy', 'practical_materials',
                'tool_kit_deposit', 'protective_clothing', 'industrial_attachment_fee',
                'cdacc_registration_fee', 'cdacc_examination_fee',
                'cdacc_certification_fee', 'tvet_authority_levy', 'trade_test_fee',
                'government_subsidy_amount',
            ]);

            $data['has_government_sponsorship'] = $request->boolean('has_government_sponsorship');
            $data['academic_year'] = AcademicTerm::find($request->term_id)?->academic_year;

            // Store previous state for audit
            $oldTotal = $feeStructure->total_course_fee;
            $oldTermId = $feeStructure->term_id;

            $feeStructure->update($data);
            $feeStructure->updateCalculatedFields();

            DB::commit();

            Log::info('Fee structure updated', [
                'fee_structure_id' => $feeStructure->id,
                'course_id'        => $feeStructure->course_id,
                'old_term_id'      => $oldTermId,
                'new_term_id'      => $feeStructure->term_id,
                'old_total'        => $oldTotal,
                'new_total'        => $feeStructure->total_course_fee,
                'reason'           => $request->modification_reason,
                'updated_by'       => auth()->id(),
            ]);

            return redirect()->route('finance.fee-structure.course-terms', $feeStructure->course_id)
                ->with('success', 'Fee structure updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Fee structure update failed', [
                'fee_structure_id' => $feeStructure->id,
                'error'            => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ============================================================
    // DESTROY
    // ============================================================

    public function destroy(FeeStructure $feeStructure)
    {
        if (!Course::canUserModifyFees(auth()->user())) {
            abort(403);
        }

        // Don't allow deletion if enrollments exist for this course + term
        $enrollmentCount = \App\Models\Enrollment::where('course_id', $feeStructure->course_id)
            ->where('term_id', $feeStructure->term_id)
            ->count();

        if ($enrollmentCount > 0) {
            return redirect()->back()
                ->with('error', "Cannot delete this fee. {$enrollmentCount} enrollment(s) reference it.");
        }

        $feeStructure->delete();

        Log::warning('Fee structure deleted', [
            'fee_structure_id' => $feeStructure->id,
            'course_id'        => $feeStructure->course_id,
            'term_id'          => $feeStructure->term_id,
            'deleted_by'       => auth()->id(),
        ]);

        return redirect()->route('finance.fee-structure.course-terms', $feeStructure->course_id)
            ->with('success', 'Fee structure deleted successfully.');
    }

    // ============================================================
    // APPROVE / REJECT (kept, still operate on feeStructure)
    // ============================================================

    public function approve(Request $request, FeeStructure $feeStructure)
    {
        $user = auth()->user();

        if (!in_array($user->role, [0, 7])) {
            abort(403, 'Only Finance or Super Admin can approve fee structure changes.');
        }

        $feeStructure->update([
            'is_approved'  => true,
            'approved_by'  => $user->id,
        ]);

        Log::info('Fee structure approved', [
            'fee_structure_id' => $feeStructure->id,
            'approved_by'      => $user->id,
        ]);

        return redirect()->back()->with('success', 'Fee structure approved.');
    }

    public function reject(Request $request, FeeStructure $feeStructure)
    {
        $user = auth()->user();

        if (!in_array($user->role, [0, 7])) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        $feeStructure->update([
            'is_approved' => false,
        ]);

        Log::warning('Fee structure rejected', [
            'fee_structure_id' => $feeStructure->id,
            'rejected_by'      => $user->id,
            'reason'           => $request->rejection_reason,
        ]);

        return redirect()->back()->with('warning', 'Fee structure rejected.');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function buildBreakdown(FeeStructure $fs): array
    {
        return [
            'Tuition (Monthly)' => (float) $fs->tuition_per_month,
            'Registration'      => (float) $fs->registration_fee,
            'Caution Money'     => (float) $fs->caution_money,
            'Student ID'        => (float) $fs->student_id_fee,
            'Library'           => (float) $fs->library_fee,
            'Medical'           => (float) $fs->medical_fee,
            'Sports'            => (float) $fs->sports_fee,
            'Activity'          => (float) $fs->activity_fee,
            'Workshop Levy'     => (float) $fs->workshop_levy,
            'Practical Materials' => (float) $fs->practical_materials,
            'Tool Kit Deposit'  => (float) $fs->tool_kit_deposit,
            'Protective Clothing' => (float) $fs->protective_clothing,
            'Industrial Attachment' => (float) $fs->industrial_attachment_fee,
            'CDACC Registration' => (float) $fs->cdacc_registration_fee,
            'CDACC Examination' => (float) $fs->cdacc_examination_fee,
            'CDACC Certification' => (float) $fs->cdacc_certification_fee,
            'TVET Authority Levy' => (float) $fs->tvet_authority_levy,
            'Trade Test'        => (float) $fs->trade_test_fee,
        ];
    }
}