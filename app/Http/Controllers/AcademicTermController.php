<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AcademicTermController extends Controller
{
    /**
     * ============ ADMIN INDEX ============
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = AcademicTerm::with('campus')
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where(function($sub) use ($user) {
                    $sub->where('campus_id', $user->campus_id)
                        ->orWhereNull('campus_id'); // Global terms
                });
            });

        // Apply filters
        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'yes');
        }

        if ($request->filled('is_current')) {
            $query->where('is_current', $request->is_current === 'yes');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('academic_year', 'like', "%{$search}%")
                  ->orWhere('academic_year_name', 'like', "%{$search}%");
            });
        }

        // Statistics
        $totalTerms = (clone $query)->count();
        $currentTerms = (clone $query)->where('is_current', true)->count();
        $activeTerms = (clone $query)->where('is_active', true)->count();
        $registrationOpen = (clone $query)->where('is_registration_open', true)->count();

        $academicTerms = $query->orderBy('academic_year', 'desc')
            ->orderBy('term_number')
            ->paginate(15);

        // Filter dropdown data
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $academicYears = AcademicTerm::select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');

        return view('ktvtc.admin.academic-terms.index', compact(
            'academicTerms',
            'campuses',
            'academicYears',
            'totalTerms',
            'currentTerms',
            'activeTerms',
            'registrationOpen'
        ));
    }

    /**
     * ============ CREATE ============
     */
    public function create()
    {
        $user = auth()->user();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $termNumbers = [1, 2, 3, 4];
        $academicYears = range(date('Y') - 2, date('Y') + 2);

        return view('ktvtc.admin.academic-terms.create', compact(
            'campuses',
            'termNumbers',
            'academicYears'
        ));
    }

    /**
     * ============ STORE ============
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'nullable|exists:campuses,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:academic_terms,code',
            'short_code' => 'nullable|string|max:10',
            'term_number' => 'required|integer|between:1,4',
            'academic_year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'academic_year_name' => 'nullable|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'fee_due_date' => 'required|date|after_or_equal:start_date',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date|after_or_equal:registration_start_date',
            'late_registration_start_date' => 'nullable|date',
            'late_registration_end_date' => 'nullable|date|after_or_equal:late_registration_start_date',
            'exam_registration_start_date' => 'nullable|date',
            'exam_registration_end_date' => 'nullable|date|after_or_equal:exam_registration_start_date',
            'exam_start_date' => 'nullable|date',
            'exam_end_date' => 'nullable|date|after_or_equal:exam_start_date',
            'is_active' => 'boolean',
            'is_current' => 'boolean',
            'is_registration_open' => 'boolean',
            'allow_late_registration' => 'boolean',
            'late_registration_fee' => 'nullable|numeric|min:0',
            'late_payment_fee' => 'nullable|numeric|min:0',
            'late_payment_percentage' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // If setting as current, remove current from all other terms
            if ($request->boolean('is_current')) {
                AcademicTerm::where('is_current', true)->update(['is_current' => false]);
            }

            $data = $request->all();
            $data['created_by'] = auth()->id();
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();

            $academicTerm = AcademicTerm::create($data);

            DB::commit();

            return redirect()->route('admin.tvet.academic-terms.index')
                ->with('success', 'Academic term created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create academic term: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ SHOW ============
     */
    public function show(AcademicTerm $academicTerm)
    {
        $academicTerm->load(['campus', 'creator', 'updater']);

        // Get enrollments for this term
        $enrollments = $academicTerm->enrollments()
            ->with(['student', 'course'])
            ->paginate(15);

        return view('ktvtc.admin.academic-terms.show', compact('academicTerm', 'enrollments'));
    }

    /**
     * ============ EDIT ============
     */
    public function edit(AcademicTerm $academicTerm)
    {
        $user = auth()->user();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $termNumbers = [1, 2, 3, 4];
        $academicYears = range(date('Y') - 2, date('Y') + 2);

        return view('ktvtc.admin.academic-terms.edit', compact(
            'academicTerm',
            'campuses',
            'termNumbers',
            'academicYears'
        ));
    }

    /**
     * ============ UPDATE ============
     */
    public function update(Request $request, AcademicTerm $academicTerm)
    {
        $validator = Validator::make($request->all(), [
            'campus_id' => 'nullable|exists:campuses,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:academic_terms,code,' . $academicTerm->id,
            'short_code' => 'nullable|string|max:10',
            'term_number' => 'required|integer|between:1,4',
            'academic_year' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'academic_year_name' => 'nullable|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'fee_due_date' => 'required|date|after_or_equal:start_date',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date|after_or_equal:registration_start_date',
            'late_registration_start_date' => 'nullable|date',
            'late_registration_end_date' => 'nullable|date|after_or_equal:late_registration_start_date',
            'exam_registration_start_date' => 'nullable|date',
            'exam_registration_end_date' => 'nullable|date|after_or_equal:exam_registration_start_date',
            'exam_start_date' => 'nullable|date',
            'exam_end_date' => 'nullable|date|after_or_equal:exam_start_date',
            'is_active' => 'boolean',
            'is_current' => 'boolean',
            'is_registration_open' => 'boolean',
            'allow_late_registration' => 'boolean',
            'late_registration_fee' => 'nullable|numeric|min:0',
            'late_payment_fee' => 'nullable|numeric|min:0',
            'late_payment_percentage' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // If setting as current, remove current from all other terms
            if ($request->boolean('is_current') && !$academicTerm->is_current) {
                AcademicTerm::where('is_current', true)->update(['is_current' => false]);
            }

            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $academicTerm->update($data);

            DB::commit();

            return redirect()->route('admin.tvet.academic-terms.index')
                ->with('success', 'Academic term updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update academic term: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ DESTROY ============
     */
    public function destroy(AcademicTerm $academicTerm)
    {
        // Check if term has enrollments
        if ($academicTerm->enrollments()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete term with existing enrollments.');
        }

        $academicTerm->delete();

        return redirect()->route('admin.tvet.academic-terms.index')
            ->with('success', 'Academic term deleted successfully.');
    }

    /**
     * ============ STATUS ACTIONS ============
     */
    public function activate(AcademicTerm $academicTerm)
    {
        $academicTerm->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Academic term activated successfully.');
    }

    public function deactivate(AcademicTerm $academicTerm)
    {
        $academicTerm->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Academic term deactivated successfully.');
    }

    public function setCurrent(AcademicTerm $academicTerm)
    {
        DB::transaction(function () use ($academicTerm) {
            AcademicTerm::where('is_current', true)->update(['is_current' => false]);
            $academicTerm->update(['is_current' => true, 'is_active' => true]);
        });

        return redirect()->back()
            ->with('success', 'Academic term set as current.');
    }

    public function toggleRegistration(AcademicTerm $academicTerm)
    {
        $academicTerm->update([
            'is_registration_open' => !$academicTerm->is_registration_open
        ]);

        $status = $academicTerm->is_registration_open ? 'opened' : 'closed';

        return redirect()->back()
            ->with('success', "Registration {$status} for this term.");
    }

    /**
     * ============ API ENDPOINTS ============
     */
    public function getCurrent()
    {
        $term = AcademicTerm::where('is_current', true)->first();

        return response()->json($term);
    }

    public function getByCampus(Request $request)
    {
        $request->validate([
            'campus_id' => 'required|exists:campuses,id'
        ]);

        $terms = AcademicTerm::where(function($q) use ($request) {
                $q->where('campus_id', $request->campus_id)
                  ->orWhereNull('campus_id');
            })
            ->where('is_active', true)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term_number')
            ->get();

        return response()->json($terms);
    }

    public function getForSelect(Request $request)
    {
        $query = AcademicTerm::query();

        if ($request->filled('campus_id')) {
            $query->where(function($q) use ($request) {
                $q->where('campus_id', $request->campus_id)
                  ->orWhereNull('campus_id');
            });
        }

        $terms = $query->where('is_active', true)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term_number')
            ->get(['id', 'name', 'code', 'academic_year', 'term_number']);

        return response()->json($terms);
    }
}
