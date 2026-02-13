<?php

namespace App\Http\Controllers;

use App\Models\BusinessSection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BusinessSectionController extends Controller
{
    // Index - Returns Blade view with sections
    // In BusinessSectionController.php
// In BusinessSectionController.php index method
public function index(Request $request)
{
    $query = BusinessSection::with(['manager', 'creator', 'updater']);

    if ($request->has('is_active')) {
        $query->where('is_active', $request->boolean('is_active'));
    }

    if ($request->has('section_type')) {
        $query->where('section_type', $request->section_type);
    }

    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('section_code', 'like', "%{$search}%")
              ->orWhere('section_name', 'like', "%{$search}%");
        });
    }

    $sections = $query->paginate($request->get('per_page', 15));

    // Get users with role 6 (Cafeteria)
    $cafeteriaUsers = User::where('role', 6)
        ->orderBy('name')
        ->get(['id', 'name']);

    return view('ktvtc.cafeteria.businesssection.index', [
        'sections' => $sections,
        'cafeteriaUsers' => $cafeteriaUsers,
        'filters' => $request->only(['search', 'is_active', 'section_type'])
    ]);
}

    // API Index - For AJAX calls (keep this for your API routes)
    public function apiIndex(Request $request)
    {
        $query = BusinessSection::with(['manager', 'creator', 'updater']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('section_type')) {
            $query->where('section_type', $request->section_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('section_code', 'like', "%{$search}%")
                  ->orWhere('section_name', 'like', "%{$search}%");
            });
        }

        $sections = $query->paginate($request->get('per_page', 15));

        return response()->json($sections);
    }

    // Store - Handles form submission
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_code' => 'required|string|unique:business_sections|max:50',
            'section_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'section_type' => 'required|string|max:50',
            'manager_user_id' => 'nullable|exists:users,id',
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
        $data['slug'] = Str::slug($data['section_name']);
        $data['created_by'] = auth()->id();

        $businessSection = BusinessSection::create($data);

        if ($request->ajax()) {
            return response()->json($businessSection->load(['manager', 'creator']), 201);
        }

        return redirect()->route('cafeteria.business-sections.index')
            ->with('success', 'Business section created successfully!');
    }

    // Show - Returns single section view
    public function show($id)
    {
        $businessSection = BusinessSection::with(['manager', 'creator', 'updater'])->findOrFail($id);

        // Return Blade view
        return view('cafeteria.business-sections.show', [
            'section' => $businessSection
        ]);
    }

    // Update - Handles update form submission
    public function update(Request $request, $id)
    {
        $businessSection = BusinessSection::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'section_code' => 'sometimes|string|unique:business_sections,section_code,' . $id . '|max:50',
            'section_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'section_type' => 'sometimes|string|max:50',
            'manager_user_id' => 'nullable|exists:users,id',
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
        if ($request->has('section_name')) {
            $data['slug'] = Str::slug($data['section_name']);
        }
        $data['updated_by'] = auth()->id();

        $businessSection->update($data);

        if ($request->ajax()) {
            return response()->json($businessSection->fresh()->load(['manager', 'creator', 'updater']));
        }

        return redirect()->route('cafeteria.business-sections.index')
            ->with('success', 'Business section updated successfully!');
    }

    // Destroy - Handles deletion
    public function destroy(Request $request, $id)
    {
        $businessSection = BusinessSection::findOrFail($id);
        $businessSection->delete();

        if ($request->ajax()) {
            return response()->json(['message' => 'Business section deleted successfully']);
        }

        return redirect()->route('cafeteria.business-sections.index')
            ->with('success', 'Business section deleted successfully!');
    }

    public function restore($id)
    {
        $businessSection = BusinessSection::withTrashed()->findOrFail($id);
        $businessSection->restore();

        return response()->json(['message' => 'Business section restored successfully']);
    }
}
