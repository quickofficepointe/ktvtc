<?php

namespace App\Http\Controllers;

use App\Models\CourseFeeTemplate;
use App\Models\Course;
use App\Models\Campus;
use App\Models\FeeCategory;
use App\Models\FeeTemplateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseFeeTemplateController extends Controller
{
    /**
     * ============ ADMIN INDEX ============
     */
    public function index (Request $request)
    {
        $user = auth()->user();

        $query = CourseFeeTemplate::with(['course', 'campus', 'creator'])
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where(function($sub) use ($user) {
                    $sub->where('campus_id', $user->campus_id)
                        ->orWhereNull('campus_id');
                });
            });

        // Apply filters
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'yes');
        }

        if ($request->filled('is_default')) {
            $query->where('is_default', $request->is_default === 'yes');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('course', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        // Statistics
        $totalTemplates = (clone $query)->count();
        $activeTemplates = (clone $query)->where('is_active', true)->count();
        $defaultTemplates = (clone $query)->where('is_default', true)->count();
        $publicTemplates = (clone $query)->where('is_public', true)->count();

        $examTypeBreakdown = [
            'nita' => (clone $query)->where('exam_type', 'nita')->count(),
            'cdacc' => (clone $query)->where('exam_type', 'cdacc')->count(),
            'school_assessment' => (clone $query)->where('exam_type', 'school_assessment')->count(),
            'mixed' => (clone $query)->where('exam_type', 'mixed')->count(),
        ];

        $templates = $query->orderBy('course_id')
            ->orderBy('exam_type')
            ->orderBy('is_default', 'desc')
            ->paginate(15);

        // Filter dropdown data
        $courses = Course::where('is_active', true)->orderBy('name')->get();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $examTypes = [
            'nita' => 'NITA',
            'cdacc' => 'CDACC',
            'school_assessment' => 'School Assessment',
            'mixed' => 'Mixed'
        ];

        return view('ktvtc.admin.course-fee-templates.index', compact(
            'templates',
            'courses',
            'campuses',
            'examTypes',
            'totalTemplates',
            'activeTemplates',
            'defaultTemplates',
            'publicTemplates',
            'examTypeBreakdown'
        ));
    }

    /**
     * ============ CREATE ============
     */
    public function create()
    {
        $user = auth()->user();

        $courses = Course::where('is_active', true)->orderBy('name')->get();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $examTypes = [
            'nita' => 'NITA',
            'cdacc' => 'CDACC',
            'school_assessment' => 'School Assessment',
            'mixed' => 'Mixed'
        ];

        $intakePeriods = ['Jan', 'May', 'Sept'];
        $totalTerms = [1, 2, 3, 4];
        $durations = [1, 2, 3, 6, 9, 12, 18, 24];

        return view('ktvtc.admin.course-fee-templates.create', compact(
            'courses',
            'campuses',
            'examTypes',
            'intakePeriods',
            'totalTerms',
            'durations'
        ));
    }

    /**
     * ============ STORE ============
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:course_fee_templates,code',
            'course_id' => 'required|exists:courses,id',
            'exam_type' => 'required|in:nita,cdacc,school_assessment,mixed',
            'total_terms' => 'required|integer|min:1|max:4',
            'duration_months' => 'nullable|integer|min:1',
            'intake_periods' => 'nullable|array',
            'intake_periods.*' => 'in:Jan,May,Sept',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'campus_id' => 'nullable|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();

            // Generate code if not provided
            if (empty($data['code'])) {
                $course = Course::find($request->course_id);
                $data['code'] = Str::upper($course->code . '-' . $request->exam_type . '-' . date('Y'));
            }

            $data['created_by'] = auth()->id();
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();

            $template = CourseFeeTemplate::create($data);

            // If set as default, update other templates
            if ($request->boolean('is_default')) {
                $template->makeDefault();
            }

            DB::commit();

            return redirect()->route('admin.tvet.course-fee-templates.edit', $template)
                ->with('success', 'Fee template created successfully. Now add fee items.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create fee template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ SHOW ============
     */
    public function show(CourseFeeTemplate $courseFeeTemplate)
    {
        $courseFeeTemplate->load([
            'course',
            'campus',
            'creator',
            'updater',
            'feeItems.feeCategory'
        ]);

        // Group fee items by category
        $groupedItems = $courseFeeTemplate->feeItems
            ->groupBy(function($item) {
                return $item->feeCategory->name ?? 'Other';
            });

        return view('ktvtc.admin.course-fee-templates.show', compact('courseFeeTemplate', 'groupedItems'));
    }

    /**
     * ============ EDIT ============
     */
    public function edit(CourseFeeTemplate $courseFeeTemplate)
    {
        $user = auth()->user();

        $courses = Course::where('is_active', true)->orderBy('name')->get();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $examTypes = [
            'nita' => 'NITA',
            'cdacc' => 'CDACC',
            'school_assessment' => 'School Assessment',
            'mixed' => 'Mixed'
        ];

        $intakePeriods = ['Jan', 'May', 'Sept'];
        $totalTerms = [1, 2, 3, 4];
        $durations = [1, 2, 3, 6, 9, 12, 18, 24];

        // Load fee items for this template
        $courseFeeTemplate->load(['feeItems.feeCategory']);

        // Get fee categories for adding new items
        $feeCategories = FeeCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('ktvtc.admin.course-fee-templates.edit', compact(
            'courseFeeTemplate',
            'courses',
            'campuses',
            'examTypes',
            'intakePeriods',
            'totalTerms',
            'durations',
            'feeCategories'
        ));
    }

    /**
     * ============ UPDATE ============
     */
    public function update(Request $request, CourseFeeTemplate $courseFeeTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:course_fee_templates,code,' . $courseFeeTemplate->id,
            'course_id' => 'required|exists:courses,id',
            'exam_type' => 'required|in:nita,cdacc,school_assessment,mixed',
            'total_terms' => 'required|integer|min:1|max:4',
            'duration_months' => 'nullable|integer|min:1',
            'intake_periods' => 'nullable|array',
            'intake_periods.*' => 'in:Jan,May,Sept',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'campus_id' => 'nullable|exists:campuses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $courseFeeTemplate->update($data);

            // If set as default, update other templates
            if ($request->boolean('is_default')) {
                $courseFeeTemplate->makeDefault();
            }

            DB::commit();

            return redirect()->route('admin.tvet.course-fee-templates.index')
                ->with('success', 'Fee template updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update fee template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ DESTROY ============
     */
    public function destroy(CourseFeeTemplate $courseFeeTemplate)
    {
        // Check if template is used in enrollments
        if ($courseFeeTemplate->enrollments()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete fee template that is used in enrollments.');
        }

        DB::beginTransaction();

        try {
            // Delete associated fee items first
            $courseFeeTemplate->feeItems()->delete();

            // Delete template
            $courseFeeTemplate->delete();

            DB::commit();

            return redirect()->route('admin.tvet.course-fee-templates.index')
                ->with('success', 'Fee template deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete fee template: ' . $e->getMessage());
        }
    }

    /**
     * ============ FEE ITEM MANAGEMENT ============
     */
    public function addFeeItem(Request $request, CourseFeeTemplate $courseFeeTemplate)
    {
        $validator = Validator::make($request->all(), [
            'fee_category_id' => 'required|exists:fee_categories,id',
            'item_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'applicable_terms' => 'required|string',
            'is_required' => 'boolean',
            'is_refundable' => 'boolean',
            'due_day_offset' => 'nullable|integer|min:0',
            'is_advance_payment' => 'boolean',
            'sort_order' => 'nullable|integer',
            'is_visible_to_student' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();
            $data['quantity'] = $request->quantity ?? 1;
            $data['total_amount'] = $data['amount'] * $data['quantity'];
            $data['created_by'] = auth()->id();

            // Set default sort order
            if (empty($data['sort_order'])) {
                $data['sort_order'] = $courseFeeTemplate->feeItems()->count() + 1;
            }

            $courseFeeTemplate->feeItems()->create($data);

            // Recalculate template total
            $courseFeeTemplate->calculateTotalAmount();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Fee item added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to add fee item: ' . $e->getMessage());
        }
    }

    public function updateFeeItem(Request $request, CourseFeeTemplate $courseFeeTemplate, FeeTemplateItem $feeItem)
    {
        $validator = Validator::make($request->all(), [
            'fee_category_id' => 'required|exists:fee_categories,id',
            'item_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:1',
            'applicable_terms' => 'required|string',
            'is_required' => 'boolean',
            'is_refundable' => 'boolean',
            'due_day_offset' => 'nullable|integer|min:0',
            'is_advance_payment' => 'boolean',
            'sort_order' => 'nullable|integer',
            'is_visible_to_student' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $request->all();
            $data['quantity'] = $request->quantity ?? 1;
            $data['total_amount'] = $data['amount'] * $data['quantity'];
            $data['updated_by'] = auth()->id();

            $feeItem->update($data);

            // Recalculate template total
            $courseFeeTemplate->calculateTotalAmount();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Fee item updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update fee item: ' . $e->getMessage());
        }
    }

    public function deleteFeeItem(CourseFeeTemplate $courseFeeTemplate, FeeTemplateItem $feeItem)
    {
        DB::beginTransaction();

        try {
            $feeItem->delete();

            // Recalculate template total
            $courseFeeTemplate->calculateTotalAmount();

            // Reorder remaining items
            $remainingItems = $courseFeeTemplate->feeItems()->orderBy('sort_order')->get();
            foreach ($remainingItems as $index => $item) {
                $item->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Fee item deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete fee item: ' . $e->getMessage());
        }
    }

    public function duplicateFeeItem(CourseFeeTemplate $courseFeeTemplate, FeeTemplateItem $feeItem)
    {
        DB::beginTransaction();

        try {
            $newItem = $feeItem->duplicate();
            $newItem->fee_template_id = $courseFeeTemplate->id;
            $newItem->sort_order = $courseFeeTemplate->feeItems()->count() + 1;
            $newItem->save();

            // Recalculate template total
            $courseFeeTemplate->calculateTotalAmount();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Fee item duplicated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to duplicate fee item: ' . $e->getMessage());
        }
    }

    /**
     * ============ STATUS ACTIONS ============
     */
    public function activate(CourseFeeTemplate $courseFeeTemplate)
    {
        $courseFeeTemplate->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Fee template activated successfully.');
    }

    public function deactivate(CourseFeeTemplate $courseFeeTemplate)
    {
        $courseFeeTemplate->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Fee template deactivated successfully.');
    }

    public function setDefault(CourseFeeTemplate $courseFeeTemplate)
    {
        $courseFeeTemplate->makeDefault();

        return redirect()->back()
            ->with('success', 'Fee template set as default successfully.');
    }

    /**
     * ============ API ENDPOINTS ============
     */
    public function getByCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'exam_type' => 'nullable|in:nita,cdacc,school_assessment,mixed',
        ]);

        $query = CourseFeeTemplate::with(['feeItems.feeCategory'])
            ->where('course_id', $request->course_id)
            ->where('is_active', true);

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        $templates = $query->get();

        return response()->json($templates);
    }

    public function getDefaultByCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'exam_type' => 'nullable|in:nita,cdacc,school_assessment,mixed',
        ]);

        $query = CourseFeeTemplate::with(['feeItems.feeCategory'])
            ->where('course_id', $request->course_id)
            ->where('is_active', true);

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        $template = $query->where('is_default', true)->first();

        if (!$template) {
            $template = $query->first();
        }

        return response()->json($template);
    }
}
