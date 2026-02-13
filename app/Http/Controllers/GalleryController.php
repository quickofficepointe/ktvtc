<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    // List all galleries
    /**
 * Display galleries for public website
 */
public function publicIndex()
{
    $galleries = Gallery::with(['images' => function($query) {
            $query->where('is_active', true)->orderBy('order');
        }])
        ->where('is_active', true)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('ktvtc.website.galleries.galleries', compact('galleries'));
}

/**
 * Display single gallery for public website
 */
public function publicShow($id)
{
    $gallery = Gallery::with(['images' => function($query) {
            $query->where('is_active', true)->orderBy('order');
        }])
        ->where('is_active', true)
        ->findOrFail($id);

    return view('ktvtc.website.galleries.show', compact('gallery'));
}
    public function index()
    {
        $galleries = Gallery::with('images')->latest()->get();
        return view('ktvtc.website.galleries.index', compact('galleries'));
    }
// Show single gallery with images (for AJAX)
public function show($id)
{
    $gallery = Gallery::with('images')->findOrFail($id);
    return response()->json($gallery);
}
    // Store a new gallery
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'is_active'   => 'nullable|boolean',
        ]);

        $coverPath = $request->file('cover_image')
            ? $request->file('cover_image')->store('galleries/covers', 'public')
            : null;

        Gallery::create([
            'title'       => $request->title,
            'description' => $request->description,
            'cover_image' => $coverPath,
            'is_active'   => $request->is_active ?? true,
            'created_by'  => Auth::id(),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return back()->with('success', 'Gallery created successfully.');
    }

    // Update gallery
    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|max:2048',
            'is_active'   => 'nullable|boolean',
        ]);

        $coverPath = $gallery->cover_image;
        if ($request->hasFile('cover_image')) {
            if ($gallery->cover_image) {
                Storage::disk('public')->delete($gallery->cover_image);
            }
            $coverPath = $request->file('cover_image')->store('galleries/covers', 'public');
        }

        $gallery->update([
            'title'       => $request->title,
            'description' => $request->description,
            'cover_image' => $coverPath,
            'is_active'   => $request->is_active ?? $gallery->is_active,

        ]);

        return back()->with('success', 'Gallery updated successfully.');
    }

    // Delete gallery
    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);

        if ($gallery->cover_image) {
            Storage::disk('public')->delete($gallery->cover_image);
        }
        foreach ($gallery->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $gallery->delete();

        return back()->with('success', 'Gallery deleted successfully.');
    }

    // Store images for a gallery
    public function storeImages(Request $request, Gallery $gallery)
    {
        $request->validate([
            'images.*'  => 'required|image|max:4096',
            'captions.*'=> 'nullable|string|max:255',
        ]);

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('galleries/images', 'public');
            GalleryImages::create([
                'gallery_id' => $gallery->id,
                'image_path' => $path,
                'caption'    => $request->captions[$index] ?? null,
                'order'      => $index,
                'created_by' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return back()->with('success', 'Images uploaded successfully.');
    }

    // Delete a single image
    public function destroyImage($id)
    {
        $image = GalleryImages::findOrFail($id);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }
}

