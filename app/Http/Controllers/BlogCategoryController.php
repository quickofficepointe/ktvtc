<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = BlogCategory::latest()->get();
        return view('ktvtc.website.blogcategory.index', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255|unique:blog_categories,name',
            'description'      => 'nullable|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072', // 3MB = 3072 KB
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'is_active'        => 'boolean',
        ]);

        $validated['slug'] = Str::slug($request->name);
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = 'category-' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('blog-categories', $imageName, 'public');
            $validated['cover_image'] = $imagePath;
        }

        BlogCategory::create($validated);

        return redirect()->back()->with('success', 'Blog category created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    /**
 * Update the specified resource in storage.
 */
public function update(Request $request, $id)  // ← Change to $id
{
    $blogCategory = BlogCategory::findOrFail($id);  // ← Find the category manually

    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:blog_categories,name,' . $id,  // ← Use $id
        'description'      => 'nullable|string',
        'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:3072',
        'meta_title'       => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:255',
        'is_active'        => 'boolean',
    ]);

    $validated['slug'] = Str::slug($request->name);
    $validated['updated_by'] = Auth::id();

    // Handle cover image upload
    if ($request->hasFile('cover_image')) {
        // Delete old image if exists
        if ($blogCategory->cover_image && Storage::disk('public')->exists($blogCategory->cover_image)) {
            Storage::disk('public')->delete($blogCategory->cover_image);
        }

        $image = $request->file('cover_image');
        $imageName = 'category-' . time() . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs('blog-categories', $imageName, 'public');
        $validated['cover_image'] = $imagePath;
    }

    $blogCategory->update($validated);

    return redirect()->back()->with('success', 'Blog category updated successfully!');
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogCategory $blogCategory)
    {
        // Delete associated image
        if ($blogCategory->cover_image && Storage::disk('public')->exists($blogCategory->cover_image)) {
            Storage::disk('public')->delete($blogCategory->cover_image);
        }

        $blogCategory->delete();
        return redirect()->back()->with('success', 'Blog category deleted successfully!');
    }
}
