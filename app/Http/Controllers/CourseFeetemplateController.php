<?php

namespace App\Http\Controllers;

use App\Models\CourseFeeTemplate;
use App\Models\Course;
use App\Models\Campus;
use App\Models\ExamType;
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
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = CourseFeeTemplate::with(['course', 'campus', 'creator', 'examType.examBody'])
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

        if ($request->filled('exam_type_id')) {
            $query->where('exam_type_id', $request->exam_type_id);
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

        // Exam type breakdown
        $examTypeBreakdown = ExamType::with('examBody')->get()->mapWithKeys(function($type) use ($query) {
            $count = (clone $query)->where('exam_type_id', $type->id)->count();
            return [$type->full_name => $count];
        })->toArray();

        $templates = $query->orderBy('course_id')
            ->orderBy('exam_type_id')
            ->orderBy('is_default', 'desc')
            ->paginate(15);

        // Filter dropdown data
        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $examTypes = ExamType::with('examBody')->orderBy('name')->get();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        // Get fee categories for the add fee item modal
        $feeCategories = FeeCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $intakePeriods = ['Jan', 'May', 'Sept'];
        $totalTerms = [1, 2, 3, 4];
        $durations = [1, 2, 3, 6, 9, 12, 18, 24];

        return view('ktvtc.admin.course-fee-templates.index', compact(
            'templates',
            'courses',
            'examTypes',
            'campuses',
            'totalTemplates',
            'activeTemplates',
            'defaultTemplates',
            'publicTemplates',
            'examTypeBreakdown',
            'feeCategories',
            'intakePeriods',
            'totalTerms',
            'durations'
        ));
    }

    /**
     * ============ GET SUGGESTED ITEMS ============
     */
    public function getSuggestedItems(FeeCategory $category)
    {
        return response()->json([
            'items' => $category->suggested_items ?? []
        ]);
    }

    /**
     * ============ GET FEE ITEMS (AJAX) ============
     */
    public function getFeeItems(CourseFeeTemplate $courseFeeTemplate)
    {
        $items = $courseFeeTemplate->feeItems()
            ->with('feeCategory')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * ============ CREATE ============
     */
    public function create()
    {
        $user = auth()->user();

        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $examTypes = ExamType::with('examBody')->orderBy('name')->get();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $intakePeriods = ['Jan', 'May', 'Sept'];
        $totalTerms = [1, 2, 3, 4];
        $durations = [1, 2, 3, 6, 9, 12, 18, 24];

        return view('ktvtc.admin.course-fee-templates.create', compact(
            'courses',
            'examTypes',
            'campuses',
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
            'exam_type_id' => 'required|exists:exam_types,id',
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
                $examType = ExamType::find($request->exam_type_id);
                $data['code'] = Str::upper($course->code . '-' . $examType->code . '-' . date('Y'));
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
            'examType.examBody',
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
        $examTypes = ExamType::with('examBody')->orderBy('name')->get();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $intakePeriods = ['Jan', 'May', 'Sept'];
        $totalTerms = [1, 2, 3, 4];
        $durations = [1, 2, 3, 6, 9, 12, 18, 24];

        // Load fee items for this template
        $courseFeeTemplate->load(['feeItems.feeCategory', 'examType']);

        // Get fee categories for adding new items
        $feeCategories = FeeCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Return JSON data for the frontend to build the form
        return response()->json([
            'success' => true,
            'template' => $courseFeeTemplate,
            'courses' => $courses,
            'examTypes' => $examTypes,
            'campuses' => $campuses,
            'intakePeriods' => $intakePeriods,
            'totalTerms' => $totalTerms,
            'durations' => $durations,
            'feeCategories' => $feeCategories,
            'selectedIntakes' => $courseFeeTemplate->intake_periods ?? []
        ]);
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
            'exam_type_id' => 'required|exists:exam_types,id',
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
     * ============ FEE ITEM MANAGEMENT (AJAX) ============
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
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
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

            $feeItem = $courseFeeTemplate->feeItems()->create($data);

            // Recalculate template total
            $courseFeeTemplate->calculateTotalAmount();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fee item added successfully',
                'item' => $feeItem->load('feeCategory')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to add fee item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============ EDIT FEE ITEM (AJAX) ============
     */
    public function editFeeItem(FeeTemplateItem $feeItem)
    {
        return response()->json([
            'success' => true,
            'item' => $feeItem->load('feeCategory')
        ]);
    }

    public function updateFeeItem(Request $request, FeeTemplateItem $feeItem)
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
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->all();
            $data['quantity'] = $request->quantity ?? 1;
            $data['total_amount'] = $data['amount'] * $data['quantity'];
            $data['updated_by'] = auth()->id();

            $feeItem->update($data);

            // Recalculate template total
            $feeItem->feeTemplate->calculateTotalAmount();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fee item updated successfully',
                'item' => $feeItem->fresh('feeCategory')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update fee item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteFeeItem(FeeTemplateItem $feeItem)
    {
        DB::beginTransaction();

        try {
            $template = $feeItem->feeTemplate;

            $feeItem->delete();

            // Recalculate template total
            $template->calculateTotalAmount();

            // Reorder remaining items
            $remainingItems = $template->feeItems()->orderBy('sort_order')->get();
            foreach ($remainingItems as $index => $item) {
                $item->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fee item deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete fee item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duplicateFeeItem(FeeTemplateItem $feeItem)
    {
        DB::beginTransaction();

        try {
            $template = $feeItem->feeTemplate;

            $newItem = $feeItem->replicate();
            $newItem->fee_template_id = $template->id;
            $newItem->sort_order = $template->feeItems()->count() + 1;
            $newItem->save();

            // Recalculate template total
            $template->calculateTotalAmount();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fee item duplicated successfully',
                'item' => $newItem->load('feeCategory')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate fee item: ' . $e->getMessage()
            ], 500);
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
            'exam_type_id' => 'nullable|exists:exam_types,id',
        ]);

        $query = CourseFeeTemplate::with(['feeItems.feeCategory'])
            ->where('course_id', $request->course_id)
            ->where('is_active', true);

        if ($request->filled('exam_type_id')) {
            $query->where('exam_type_id', $request->exam_type_id);
        }

        $templates = $query->get()->map(function($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'code' => $template->code,
                'total_amount' => $template->total_amount,
                'formatted_total' => $template->formatted_total,
                'exam_type' => $template->examType->full_name ?? 'N/A',
                'total_terms' => $template->total_terms,
                'is_default' => $template->is_default,
                'fee_items' => $template->feeItems
            ];
        });

        return response()->json($templates);
    }

    public function getDefaultByCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'exam_type_id' => 'nullable|exists:exam_types,id',
        ]);

        $query = CourseFeeTemplate::with(['feeItems.feeCategory'])
            ->where('course_id', $request->course_id)
            ->where('is_active', true);

        if ($request->filled('exam_type_id')) {
            $query->where('exam_type_id', $request->exam_type_id);
        }

        $template = $query->where('is_default', true)->first();

        if (!$template) {
            $template = $query->first();
        }

        return response()->json($template);
    }
}
