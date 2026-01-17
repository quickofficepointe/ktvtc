<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\BusinessSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    // Index - Returns Blade view
    public function index(Request $request)
    {
        $query = ProductCategory::with(['businessSection', 'parent', 'creator', 'updater']);

        if ($request->has('business_section_id')) {
            $query->where('business_section_id', $request->business_section_id);
        }

        if ($request->has('parent_category_id')) {
            $query->where('parent_category_id', $request->parent_category_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category_code', 'like', "%{$search}%")
                  ->orWhere('category_name', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('sort_order')->paginate($request->get('per_page', 15));

        // Get business sections for filter dropdown
        $businessSections = BusinessSection::where('is_active', true)
            ->orderBy('section_name')
            ->get(['id', 'section_name']);

        // Get root categories for parent filter
        $rootCategories = ProductCategory::whereNull('parent_category_id')
            ->where('is_active', true)
            ->orderBy('category_name')
            ->get(['id', 'category_name', 'business_section_id']);

        // Get categories for tree view (root categories only)
        $treeCategories = ProductCategory::whereNull('parent_category_id')
            ->with(['children' => function($query) {
                $query->with(['children'])->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        // Return Blade view
        return view('ktvtc.cafeteria.product.productcategory.index', [
            'categories' => $categories,
            'businessSections' => $businessSections,
            'rootCategories' => $rootCategories,
            'treeCategories' => $treeCategories,
            'filters' => $request->only(['search', 'is_active', 'business_section_id', 'parent_category_id'])
        ]);
    }

    // API Index - For AJAX calls
    public function apiIndex(Request $request)
    {
        $query = ProductCategory::with(['businessSection', 'parent', 'creator', 'updater']);

        if ($request->has('business_section_id')) {
            $query->where('business_section_id', $request->business_section_id);
        }

        if ($request->has('parent_category_id')) {
            $query->where('parent_category_id', $request->parent_category_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category_code', 'like', "%{$search}%")
                  ->orWhere('category_name', 'like', "%{$search}%");
            });
        }

        // Get tree structure if requested
        if ($request->boolean('tree')) {
            $categories = $query->whereNull('parent_category_id')->get();
            $categories->each(function ($category) {
                $this->loadChildren($category);
            });
            return response()->json($categories);
        }

        $categories = $query->orderBy('sort_order')->paginate($request->get('per_page', 15));

        return response()->json($categories);
    }

    private function loadChildren($category)
    {
        $category->load('children');
        foreach ($category->children as $child) {
            $this->loadChildren($child);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_code' => 'required|string|max:50',
            'category_name' => 'required|string|max:255',
            'business_section_id' => 'required|exists:business_sections,id',
            'description' => 'nullable|string',
            'parent_category_id' => 'nullable|exists:product_categories,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check unique combination of business_section_id and category_code
        $exists = ProductCategory::where('business_section_id', $request->business_section_id)
            ->where('category_code', $request->category_code)
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => [
                    'category_code' => ['Category code must be unique within this business section']
                ]
            ], 422);
        }

        $data = $request->all();
        $data['slug'] = Str::slug($data['category_name']);
        $data['created_by'] = auth()->id();
        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $category = ProductCategory::create($data);

        return response()->json($category->load(['businessSection', 'parent', 'creator']), 201);
    }

    public function show($id)
    {
        $category = ProductCategory::with([
            'businessSection',
            'parent',
            'children',
            'creator',
            'updater'
        ])->findOrFail($id);

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_code' => 'sometimes|string|max:50',
            'category_name' => 'sometimes|string|max:255',
            'business_section_id' => 'sometimes|exists:business_sections,id',
            'description' => 'nullable|string',
            'parent_category_id' => 'nullable|exists:product_categories,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for circular reference in parent_category_id
        if ($request->has('parent_category_id') && $request->parent_category_id) {
            if ($request->parent_category_id == $id) {
                return response()->json([
                    'errors' => [
                        'parent_category_id' => ['Category cannot be its own parent']
                    ]
                ], 422);
            }

            // Check if the parent is a descendant of this category
            $descendantIds = $this->getDescendantIds($id);
            if (in_array($request->parent_category_id, $descendantIds)) {
                return response()->json([
                    'errors' => [
                        'parent_category_id' => ['Cannot set a descendant as parent']
                    ]
                ], 422);
            }
        }

        // Check unique combination of business_section_id and category_code
        if ($request->has('category_code') || $request->has('business_section_id')) {
            $businessSectionId = $request->business_section_id ?? $category->business_section_id;
            $categoryCode = $request->category_code ?? $category->category_code;

            $exists = ProductCategory::where('business_section_id', $businessSectionId)
                ->where('category_code', $categoryCode)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'errors' => [
                        'category_code' => ['Category code must be unique within this business section']
                    ]
                ], 422);
            }
        }

        $data = $request->all();
        if ($request->has('category_name')) {
            $data['slug'] = Str::slug($data['category_name']);
        }
        $data['updated_by'] = auth()->id();

        $category->update($data);

        return response()->json($category->fresh()->load(['businessSection', 'parent', 'creator', 'updater']));
    }

    private function getDescendantIds($categoryId)
    {
        $descendantIds = [];
        $categories = ProductCategory::where('parent_category_id', $categoryId)->get();

        foreach ($categories as $cat) {
            $descendantIds[] = $cat->id;
            $descendantIds = array_merge($descendantIds, $this->getDescendantIds($cat->id));
        }

        return $descendantIds;
    }

    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);

        // Check if category has products
        if ($category->products()->exists()) {
            return response()->json([
                'error' => 'Cannot delete category with associated products'
            ], 422);
        }

        // Check if category has children
        if ($category->children()->exists()) {
            return response()->json([
                'error' => 'Cannot delete category with sub-categories'
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function restore($id)
    {
        $category = ProductCategory::withTrashed()->findOrFail($id);
        $category->restore();

        return response()->json(['message' => 'Category restored successfully']);
    }
}
