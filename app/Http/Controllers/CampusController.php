<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CampusController extends Controller
{
    /**
     * Display a listing of the campuses.
     */
    public function index()
    {
        $campuses = Campus::latest()->paginate(10);
        return view('ktvtc.website.campus.index', compact('campuses'));
    }

    /**
     * Store a newly created campus in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:campuses,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'google_map_link' => 'nullable|url',
            'description' => 'nullable|string',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i|after:opening_time',
            'timezone' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            // Set is_active default if not provided
            $validated['is_active'] = $request->has('is_active') ? true : false;

            // Auto-generate slug from name
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;

            while (Campus::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $validated['slug'] = $slug;

            // Add metadata
            $validated['created_by'] = Auth::id();
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();

            // Create the campus
            Campus::create($validated);

            return redirect()->back()->with('success', 'Campus created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create campus: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the specified campus in storage.
     */
    public function update(Request $request, Campus $campus)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:campuses,code,' . $campus->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'google_map_link' => 'nullable|url',
            'description' => 'nullable|string',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i|after:opening_time',
            'timezone' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            // Set is_active based on checkbox
            $validated['is_active'] = $request->has('is_active') ? true : false;

            // Update slug only if name changed
            if ($request->name !== $campus->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $counter = 1;

                while (Campus::where('slug', $slug)->where('id', '!=', $campus->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $validated['slug'] = $slug;
            }

            // Add metadata
            $validated['updated_by'] = Auth::id();
            $validated['ip_address'] = $request->ip();
            $validated['user_agent'] = $request->userAgent();

            // Update the campus
            $campus->update($validated);

            return redirect()->back()->with('success', 'Campus updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update campus: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified campus from storage.
     */
    public function destroy(Campus $campus)
    {
        try {
            // Check if campus has any relationships before deleting
            // You can add checks here for related records if needed

            $campusName = $campus->name;
            $campus->delete();

            return redirect()->back()->with('success', "Campus '{$campusName}' deleted successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete campus: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of a campus.
     */
    public function toggleStatus(Campus $campus)
    {
        try {
            $campus->update([
                'is_active' => !$campus->is_active,
                'updated_by' => Auth::id()
            ]);

            $status = $campus->is_active ? 'activated' : 'deactivated';

            return redirect()->back()->with('success', "Campus '{$campus->name}' {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to toggle campus status: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified campus.
     */
    public function show(Campus $campus)
    {
        return view('ktvtc.website.campus.show', compact('campus'));
    }

    /**
     * Show the form for editing the specified campus.
     */
    public function edit(Campus $campus)
    {
        return view('ktvtc.website.campus.edit', compact('campus'));
    }

    /**
     * Show the form for creating a new campus.
     */
    public function create()
    {
        return view('ktvtc.website.campus.create');
    }

    /**
     * Restore a soft-deleted campus.
     */
    public function restore($id)
    {
        try {
            $campus = Campus::withTrashed()->findOrFail($id);
            $campus->restore();

            return redirect()->back()->with('success', "Campus '{$campus->name}' restored successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to restore campus: ' . $e->getMessage());
        }
    }

    /**
     * Force delete a campus permanently.
     */
    public function forceDelete($id)
    {
        try {
            $campus = Campus::withTrashed()->findOrFail($id);
            $campusName = $campus->name;

            // Delete any related records here if needed

            $campus->forceDelete();

            return redirect()->back()->with('success', "Campus '{$campusName}' permanently deleted.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to permanently delete campus: ' . $e->getMessage());
        }
    }
}
