<?php

namespace App\Http\Controllers;

use App\Models\MobileSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MobileSchoolController extends Controller
{
    public function index()
    {
        $mschools = MobileSchool::latest()->get();
        return view('ktvtc.website.mschool.index', compact('mschools'));
    }
/**
 * Display mobile schools for public website
 */
public function publicIndex()
{
    $mschools = MobileSchool::where('is_active', true)
        ->orderBy('name', 'asc')
        ->get();

    return view('ktvtc.website.mschool.mschool', compact('mschools'));
}
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'google_map_link' => 'nullable|url',
            'coordinator_name' => 'nullable|string|max:255',
            'coordinator_email' => 'nullable|email|max:255',
            'coordinator_phone' => 'nullable|string|max:20',
            'cover_image' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        // Generate unique slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Check if slug exists and make it unique
        while (MobileSchool::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        MobileSchool::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'google_map_link' => $request->google_map_link,
            'coordinator_name' => $request->coordinator_name,
            'coordinator_email' => $request->coordinator_email,
            'coordinator_phone' => $request->coordinator_phone,
            'cover_image' => $request->cover_image,
            'is_active' => $request->is_active ?? true,
            'created_by' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Mobile School added successfully');
    }

    public function update(Request $request, $id)
    {
        $mschool = MobileSchool::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'google_map_link' => 'nullable|url',
            'coordinator_name' => 'nullable|string|max:255',
            'coordinator_email' => 'nullable|email|max:255',
            'coordinator_phone' => 'nullable|string|max:20',
            'cover_image' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        // Generate new slug only if name changed
        $slug = $mschool->slug;
        if ($request->name !== $mschool->name) {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;

            // Check if slug exists and make it unique
            while (MobileSchool::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $mschool->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'google_map_link' => $request->google_map_link,
            'coordinator_name' => $request->coordinator_name,
            'coordinator_email' => $request->coordinator_email,
            'coordinator_phone' => $request->coordinator_phone,
            'cover_image' => $request->cover_image,
            'is_active' => $request->is_active ?? $mschool->is_active,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Mobile School updated successfully');
    }

    public function destroy($id)
    {
        $mschool = MobileSchool::findOrFail($id);
        $mschool->delete();

        return back()->with('success', 'Mobile School deleted successfully');
    }
}
