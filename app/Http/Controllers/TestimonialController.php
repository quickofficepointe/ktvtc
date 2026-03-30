<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::latest()->get();
        return view('ktvtc.website.testimonials.index', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'review' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'image_path' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('testimonials', 'public');
        }

        Testimonial::create([
            'name' => $request->name,
            'review' => $request->review,
            'rating' => $request->rating,
            'image_path' => $imagePath,
            'is_approved' => false,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Testimonial submitted successfully. Pending approval.');
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'review' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'image_path' => 'nullable|image|max:2048',
        ]);

        $imagePath = $testimonial->image_path;
        if ($request->hasFile('image_path')) {
            // Delete old image
            if ($testimonial->image_path) {
                Storage::disk('public')->delete($testimonial->image_path);
            }
            $imagePath = $request->file('image_path')->store('testimonials', 'public');
        }

        $testimonial->update([
            'name' => $request->name,
            'review' => $request->review,
            'rating' => $request->rating,
            'image_path' => $imagePath,
            'is_approved' => $request->boolean('is_approved'),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Testimonial updated successfully.');
    }

    public function approve($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Testimonial approved successfully');
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        // Delete associated image if exists
        if ($testimonial->image_path) {
            Storage::disk('public')->delete($testimonial->image_path);
        }

        $testimonial->delete();

        return back()->with('success', 'Testimonial deleted successfully');
    }
}
