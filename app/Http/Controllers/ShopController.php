<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\BusinessSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    // Index - Returns Blade view with shops
    public function index(Request $request)
    {
        $query = Shop::with(['businessSection', 'creator', 'updater']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('business_section_id')) {
            $query->where('business_section_id', $request->business_section_id);
        }

        if ($request->has('branch')) {
            $query->where('branch', $request->branch);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('shop_code', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }

        $shops = $query->orderBy('shop_name')->paginate($request->get('per_page', 15));

        // Get active business sections for dropdowns
        $businessSections = BusinessSection::where('is_active', true)
            ->orderBy('section_name')
            ->get(['id', 'section_name']);

        // Get unique branches for filter
        $branches = Shop::whereNotNull('branch')
            ->distinct()
            ->pluck('branch')
            ->filter()
            ->sort()
            ->values();

        // Return Blade view
        return view('ktvtc.cafeteria.shop.index', [
            'shops' => $shops,
            'businessSections' => $businessSections,
            'branches' => $branches,
            'filters' => $request->only(['search', 'is_active', 'business_section_id', 'branch'])
        ]);
    }

    // API Index - For AJAX calls
    public function apiIndex(Request $request)
    {
        $query = Shop::with(['businessSection', 'creator', 'updater']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('business_section_id')) {
            $query->where('business_section_id', $request->business_section_id);
        }

        if ($request->has('branch')) {
            $query->where('branch', $request->branch);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('shop_code', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }

        $shops = $query->orderBy('shop_name')->paginate($request->get('per_page', 15));

        return response()->json($shops);
    }

    // Get unique branches for filter dropdown
    public function branches()
    {
        $branches = Shop::whereNotNull('branch')
            ->distinct()
            ->pluck('branch')
            ->filter()
            ->sort()
            ->values();

        return response()->json($branches);
    }

    // Store - Handles form submission
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_code' => 'required|string|unique:shops|max:50',
            'shop_name' => 'required|string|max:255',
            'business_section_id' => 'required|exists:business_sections,id',
            'location' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'room_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['slug'] = Str::slug($data['shop_name']);
        $data['created_by'] = auth()->id();

        $shop = Shop::create($data);

        if ($request->ajax()) {
            return response()->json($shop->load(['businessSection', 'creator']), 201);
        }

        return redirect()->route('cafeteria.shops.index')
            ->with('success', 'Shop created successfully!');
    }

    // Show - Returns single shop view
    public function show($id)
    {
        $shop = Shop::with(['businessSection', 'creator', 'updater'])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($shop);
        }

        // Return Blade view for non-AJAX requests
        return view('cafeteria.shops.show', [
            'shop' => $shop
        ]);
    }

    // Update - Handles update form submission
    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'shop_code' => 'sometimes|string|unique:shops,shop_code,' . $id . '|max:50',
            'shop_name' => 'sometimes|string|max:255',
            'business_section_id' => 'sometimes|exists:business_sections,id',
            'location' => 'nullable|string|max:255',
            'branch' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'room_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        if ($request->has('shop_name')) {
            $data['slug'] = Str::slug($data['shop_name']);
        }
        $data['updated_by'] = auth()->id();

        $shop->update($data);

        if ($request->ajax()) {
            return response()->json($shop->fresh()->load(['businessSection', 'creator', 'updater']));
        }

        return redirect()->route('cafeteria.shops.index')
            ->with('success', 'Shop updated successfully!');
    }

    // Destroy - Handles deletion
    public function destroy(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);
        $shop->delete();

        if ($request->ajax()) {
            return response()->json(['message' => 'Shop deleted successfully']);
        }

        return redirect()->route('cafeteria.shops.index')
            ->with('success', 'Shop deleted successfully!');
    }

    // Restore soft deleted shop
    public function restore($id)
    {
        $shop = Shop::withTrashed()->findOrFail($id);
        $shop->restore();

        return response()->json(['message' => 'Shop restored successfully']);
    }
}
