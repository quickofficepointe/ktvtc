<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampusController extends Controller
{
    public function index()
    {
        $campuses = Campus::latest()->paginate(10);
        return view('ktvtc.website.campus.index', compact('campuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:campuses',
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
            'closing_time' => 'nullable|date_format:H:i',
            'timezone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Auto-generate slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        while (Campus::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;

        Campus::create($validated);

        return redirect()->route('campuses.index')
            ->with('success', 'Campus created successfully.');
    }

    public function update(Request $request, Campus $campus)
    {
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
            'closing_time' => 'nullable|date_format:H:i',
            'timezone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Update slug if name changed
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

        $campus->update($validated);

        return redirect()->route('campuses.index')
            ->with('success', 'Campus updated successfully.');
    }

    public function destroy(Campus $campus)
    {
        $campus->delete();

        return redirect()->route('campuses.index')
            ->with('success', 'Campus deleted successfully.');
    }
}
