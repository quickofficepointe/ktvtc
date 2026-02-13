<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeeCategoryController extends Controller
{
    /**
     * ============ ADMIN INDEX ============
     */
    public function index (Request $request)
    {
        $user = auth()->user();

        $query = FeeCategory::with('campus')
            ->when($user->role != 2, function ($q) use ($user) {
                return $q->where(function($sub) use ($user) {
                    $sub->where('campus_id', $user->campus_id)
                        ->orWhereNull('campus_id'); // Global categories
                });
            });

        // Apply filters
        if ($request->filled('campus_id') && $user->role == 2) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'yes');
        }

        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        if ($request->filled('is_mandatory')) {
            $query->where('is_mandatory', $request->is_mandatory === 'yes');
        }

        if ($request->filled('is_refundable')) {
            $query->where('is_refundable', $request->is_refundable === 'yes');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Statistics
        $totalCategories = (clone $query)->count();
        $activeCategories = (clone $query)->where('is_active', true)->count();
        $mandatoryCategories = (clone $query)->where('is_mandatory', true)->count();
        $refundableCategories = (clone $query)->where('is_refundable', true)->count();

        $frequencyBreakdown = [
            'once' => (clone $query)->where('frequency', 'once')->count(),
            'per_term' => (clone $query)->where('frequency', 'per_term')->count(),
            'per_year' => (clone $query)->where('frequency', 'per_year')->count(),
            'per_month' => (clone $query)->where('frequency', 'per_month')->count(),
            'per_course' => (clone $query)->where('frequency', 'per_course')->count(),
        ];

        $feeCategories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        // Filter dropdown data
        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $frequencies = ['once', 'per_term', 'per_year', 'per_month', 'per_course'];

        return view('ktvtc.admin.fee-categories.index', compact(
            'feeCategories',
            'campuses',
            'frequencies',
            'totalCategories',
            'activeCategories',
            'mandatoryCategories',
            'refundableCategories',
            'frequencyBreakdown'
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

        $frequencies = [
            'once' => 'One Time',
            'per_term' => 'Per Term',
            'per_year' => 'Per Year',
            'per_month' => 'Per Month',
            'per_course' => 'Per Course'
        ];

        $colors = [
            '#3B82F6' => 'Blue',
            '#10B981' => 'Green',
            '#EF4444' => 'Red',
            '#F59E0B' => 'Yellow',
            '#8B5CF6' => 'Purple',
            '#EC4899' => 'Pink',
            '#6B7280' => 'Gray',
        ];

        $icons = [
            'fa-money-bill' => 'Money Bill',
            'fa-book' => 'Book',
            'fa-graduation-cap' => 'Graduation',
            'fa-id-card' => 'ID Card',
            'fa-hospital' => 'Medical',
            'fa-home' => 'Hostel',
            'fa-tshirt' => 'T-Shirt',
            'fa-file-alt' => 'Exam',
            'fa-tools' => 'Materials',
            'fa-cogs' => 'Other',
        ];

        return view('ktvtc.admin.fee-categories.create', compact(
            'campuses',
            'frequencies',
            'colors',
            'icons'
        ));
    }

    /**
     * ============ STORE ============
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:fee_categories,code',
            'description' => 'nullable|string',
            'frequency' => 'required|in:once,per_term,per_year,per_month,per_course',
            'is_refundable' => 'boolean',
            'is_mandatory' => 'boolean',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'suggested_items' => 'nullable|array',
            'suggested_items.*' => 'string|max:255',
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

            // Generate code from name if not provided
            if (empty($data['code'])) {
                $data['code'] = Str::upper(Str::slug($data['name'], '_'));
            }

            $data['created_by'] = auth()->id();
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();

            $feeCategory = FeeCategory::create($data);

            DB::commit();

            return redirect()->route('admin.tvet.fee-categories.index')
                ->with('success', 'Fee category created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create fee category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ SHOW ============
     */
    public function show(FeeCategory $feeCategory)
    {
        $feeCategory->load(['campus', 'creator', 'updater', 'templateItems.feeTemplate.course']);

        return view('ktvtc.admin.fee-categories.show', compact('feeCategory'));
    }

    /**
     * ============ EDIT ============
     */
    public function edit(FeeCategory $feeCategory)
    {
        $user = auth()->user();

        if ($user->role == 2) {
            $campuses = Campus::orderBy('name')->get();
        } else {
            $campuses = Campus::where('id', $user->campus_id)->orderBy('name')->get();
        }

        $frequencies = [
            'once' => 'One Time',
            'per_term' => 'Per Term',
            'per_year' => 'Per Year',
            'per_month' => 'Per Month',
            'per_course' => 'Per Course'
        ];

        $colors = [
            '#3B82F6' => 'Blue',
            '#10B981' => 'Green',
            '#EF4444' => 'Red',
            '#F59E0B' => 'Yellow',
            '#8B5CF6' => 'Purple',
            '#EC4899' => 'Pink',
            '#6B7280' => 'Gray',
        ];

        $icons = [
            'fa-money-bill' => 'Money Bill',
            'fa-book' => 'Book',
            'fa-graduation-cap' => 'Graduation',
            'fa-id-card' => 'ID Card',
            'fa-hospital' => 'Medical',
            'fa-home' => 'Hostel',
            'fa-tshirt' => 'T-Shirt',
            'fa-file-alt' => 'Exam',
            'fa-tools' => 'Materials',
            'fa-cogs' => 'Other',
        ];

        return view('ktvtc.admin.fee-categories.edit', compact(
            'feeCategory',
            'campuses',
            'frequencies',
            'colors',
            'icons'
        ));
    }

    /**
     * ============ UPDATE ============
     */
    public function update(Request $request, FeeCategory $feeCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:fee_categories,code,' . $feeCategory->id,
            'description' => 'nullable|string',
            'frequency' => 'required|in:once,per_term,per_year,per_month,per_course',
            'is_refundable' => 'boolean',
            'is_mandatory' => 'boolean',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'suggested_items' => 'nullable|array',
            'suggested_items.*' => 'string|max:255',
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

            $feeCategory->update($data);

            DB::commit();

            return redirect()->route('admin.tvet.fee-categories.index')
                ->with('success', 'Fee category updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to update fee category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ============ DESTROY ============
     */
    public function destroy(FeeCategory $feeCategory)
    {
        // Check if category is used in template items
        if ($feeCategory->templateItems()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete fee category that is used in fee templates.');
        }

        // Check if category is used in enrollment fee items
        if ($feeCategory->enrollmentFeeItems()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete fee category that has been used in enrollments.');
        }

        $feeCategory->delete();

        return redirect()->route('admin.tvet.fee-categories.index')
            ->with('success', 'Fee category deleted successfully.');
    }

    /**
     * ============ STATUS ACTIONS ============
     */
    public function activate(FeeCategory $feeCategory)
    {
        $feeCategory->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Fee category activated successfully.');
    }

    public function deactivate(FeeCategory $feeCategory)
    {
        $feeCategory->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Fee category deactivated successfully.');
    }

    /**
     * ============ BULK ACTIONS ============
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:fee_categories,id'
        ]);

        $count = FeeCategory::whereIn('id', $request->category_ids)
            ->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', "{$count} fee categories activated successfully.");
    }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:fee_categories,id'
        ]);

        $count = FeeCategory::whereIn('id', $request->category_ids)
            ->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', "{$count} fee categories deactivated successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:fee_categories,id'
        ]);

        $count = 0;
        $errors = [];

        foreach ($request->category_ids as $id) {
            $category = FeeCategory::find($id);

            if ($category->templateItems()->count() > 0) {
                $errors[] = "{$category->name} is used in fee templates";
                continue;
            }

            if ($category->enrollmentFeeItems()->count() > 0) {
                $errors[] = "{$category->name} has been used in enrollments";
                continue;
            }

            $category->delete();
            $count++;
        }

        $message = "{$count} fee categories deleted successfully.";
        if (!empty($errors)) {
            $message .= " Skipped: " . implode(', ', $errors);
        }

        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * ============ API ENDPOINTS ============
     */
    public function getForSelect(Request $request)
    {
        $query = FeeCategory::where('is_active', true);

        if ($request->filled('campus_id')) {
            $query->where(function($q) use ($request) {
                $q->whereNull('campus_id')
                  ->orWhere('campus_id', $request->campus_id);
            });
        }

        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'frequency']);

        return response()->json($categories);
    }

    public function getSuggestedItems(FeeCategory $feeCategory)
    {
        return response()->json([
            'suggested_items' => $feeCategory->suggested_items ?? []
        ]);
    }
}
