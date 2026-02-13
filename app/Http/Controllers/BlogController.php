<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function publicindex()
    {
        $blogs = Blog::with('category')
            ->where('is_active', true)
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        $categories = BlogCategory::where('is_active', true)
            ->has('blogs')
            ->withCount(['blogs' => function($query) {
                $query->where('is_active', true)->where('is_published', true);
            }])
            ->get();

        $featuredBlogs = Blog::with('category')
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('featured', true)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('ktvtc.website.blog.public-index', compact('blogs', 'categories', 'featuredBlogs'));
    }

    /**
     * Display blogs by category
     */
    public function byCategory($categorySlug)
    {
        $category = BlogCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $blogs = Blog::with('category')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        $categories = BlogCategory::where('is_active', true)
            ->has('blogs')
            ->withCount(['blogs' => function($query) {
                $query->where('is_active', true)->where('is_published', true);
            }])
            ->get();

        return view('ktvtc.website.blog.by-category', compact('blogs', 'categories', 'category'));
    }

    /**
     * Display a single blog post
     */
    public function publicshow($categorySlug, $blogSlug)
    {
        $blog = Blog::with('category')
            ->where('slug', $blogSlug)
            ->where('is_active', true)
            ->where('is_published', true)
            ->firstOrFail();

        if ($blog->category->slug !== $categorySlug) {
            abort(404);
        }

        $relatedBlogs = Blog::with('category')
            ->where('category_id', $blog->category_id)
            ->where('id', '!=', $blog->id)
            ->where('is_active', true)
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        $categories = BlogCategory::where('is_active', true)
            ->has('blogs')
            ->withCount(['blogs' => function($query) {
                $query->where('is_active', true)->where('is_published', true);
            }])
            ->get();

        return view('ktvtc.website.blog.show', compact('blog', 'relatedBlogs', 'categories'));
    }

    /**
     * Display admin listing
     */
    public function index()
    {
        $blogs = Blog::with('category')->latest()->get();
        $categories = BlogCategory::where('is_active', true)->get();

        return view('ktvtc.website.blog.index', compact('blogs', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'       => 'required|exists:blog_categories,id',
            'title'             => 'required|string|max:255|unique:blogs,title',
            'content'           => 'required|string',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:255',
            'is_active'         => 'boolean',
            'is_published'      => 'boolean',
            'featured'          => 'boolean',
            'published_at'      => 'nullable|date|after_or_equal:now', // Scheduling field
        ]);

        $validated['slug'] = Str::slug($request->title);
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = 'blog-' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('blogs', $imageName, 'public');
            $validated['cover_image'] = $imagePath;
        }

        // Handle scheduling logic
        if ($request->is_published) {
            if ($request->published_at) {
                $validated['published_at'] = $request->published_at;
            } else {
                $validated['published_at'] = now();
            }
        } else {
            $validated['published_at'] = null;
        }

        Blog::create($validated);

        return redirect()->back()->with('success', 'Blog created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) // Changed from Blog $blog to $id
    {
        $blog = Blog::findOrFail($id); // Find blog manually

        $validated = $request->validate([
            'category_id'       => 'required|exists:blog_categories,id',
            'title'             => 'required|string|max:255|unique:blogs,title,' . $id,
            'content'           => 'required|string',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:255',
            'is_active'         => 'boolean',
            'is_published'      => 'boolean',
            'featured'          => 'boolean',
            'published_at'      => 'nullable|date|after_or_equal:now', // Scheduling field
        ]);

        $validated['slug'] = Str::slug($request->title);
        $validated['updated_by'] = Auth::id();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            if ($blog->cover_image && Storage::disk('public')->exists($blog->cover_image)) {
                Storage::disk('public')->delete($blog->cover_image);
            }

            $image = $request->file('cover_image');
            $imageName = 'blog-' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('blogs', $imageName, 'public');
            $validated['cover_image'] = $imagePath;
        }

        // Handle scheduling logic
        if ($request->is_published) {
            if ($request->published_at) {
                $validated['published_at'] = $request->published_at;
            } elseif (!$blog->published_at) {
                $validated['published_at'] = now();
            }
        } else {
            $validated['published_at'] = null;
        }

        $blog->update($validated);

        return redirect()->back()->with('success', 'Blog updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        if ($blog->cover_image && Storage::disk('public')->exists($blog->cover_image)) {
            Storage::disk('public')->delete($blog->cover_image);
        }

        $blog->delete();
        return redirect()->back()->with('success', 'Blog deleted successfully!');
    }

    /**
     * Get blog data for editing (for AJAX call)
     */
    public function show($id) // Changed from Blog $blog to $id
    {
        $blog = Blog::findOrFail($id);
        return response()->json($blog);
    }
}
