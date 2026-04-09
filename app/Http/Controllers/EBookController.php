<?php

namespace App\Http\Controllers;

use App\Models\EBook;
use App\Models\EBookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EBookController extends Controller
{
    public function index(Request $request)
    {
        $query = EBook::with('category')->query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filter by category (using category_id)
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'featured') {
                $query->where('is_featured', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'active') {
                $query->where('is_active', true);
            }
        }

        $eBooks = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get categories from EBookCategory model, not from distinct string values
        $categories = EBookCategory::active()->ordered()->get();

        $totalEBooks = EBook::count();
        $activeCount = EBook::where('is_active', true)->count();
        $totalDownloads = EBook::sum('download_count');
        $totalViews = EBook::sum('view_count');
        $featuredCount = EBook::where('is_featured', true)->count();

        return view('ktvtc.library.ebooks.index', compact(
            'eBooks',
            'categories',
            'totalEBooks',
            'activeCount',
            'totalDownloads',
            'totalViews',
            'featuredCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:e_books,isbn',
            'description' => 'nullable|string',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'language' => 'nullable|string|max:50',
            'category_id' => 'required|exists:e_book_categories,id', // Changed from 'category' to 'category_id'
            'ebook_file' => 'required|file|mimes:pdf,epub|max:20480',
            'cover_image' => 'nullable|image|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Upload eBook file
        if ($request->hasFile('ebook_file')) {
            $file = $request->file('ebook_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('ebooks', $fileName, 'public');
            $validated['file_path'] = $filePath;
            $validated['file_format'] = strtoupper($file->getClientOriginalExtension());
            $validated['file_size'] = round($file->getSize() / 1024, 2);
        }

        // Upload cover image
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('ebooks/covers', 'public');
        }

        $validated['uploaded_by'] = Auth::id();

        // Set defaults if not provided
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        EBook::create($validated);

        return redirect()->route('library.ebooks.index')
            ->with('success', 'eBook added successfully.');
    }

    public function show(EBook $ebook)
    {
        $ebook->incrementViewCount();
        $ebook->load('uploader', 'category');

        // Get related eBooks (same category)
        $relatedEBooks = EBook::where('category_id', $ebook->category_id)
            ->where('id', '!=', $ebook->id)
            ->where('is_active', true)
            ->with('category')
            ->limit(5)
            ->get();

        return view('ktvtc.library.ebooks.show', compact('ebook', 'relatedEBooks'));
    }

    public function edit(EBook $ebook)
    {
        return response()->json($ebook);
    }

    public function download(EBook $ebook)
    {
        $ebook->incrementDownloadCount();

        if (!Storage::disk('public')->exists($ebook->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($ebook->file_path, $ebook->title . '.' . strtolower($ebook->file_format));
    }

    public function update(Request $request, EBook $ebook)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:e_books,isbn,' . $ebook->id,
            'description' => 'nullable|string',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'language' => 'nullable|string|max:50',
            'category_id' => 'required|exists:e_book_categories,id', // Changed from 'category' to 'category_id'
            'ebook_file' => 'nullable|file|mimes:pdf,epub|max:20480',
            'cover_image' => 'nullable|image|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Upload new eBook file if provided
        if ($request->hasFile('ebook_file')) {
            // Delete old file
            if ($ebook->file_path && Storage::disk('public')->exists($ebook->file_path)) {
                Storage::disk('public')->delete($ebook->file_path);
            }

            $file = $request->file('ebook_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('ebooks', $fileName, 'public');
            $validated['file_path'] = $filePath;
            $validated['file_format'] = strtoupper($file->getClientOriginalExtension());
            $validated['file_size'] = round($file->getSize() / 1024, 2);
        }

        // Upload new cover image if provided
        if ($request->hasFile('cover_image')) {
            if ($ebook->cover_image && Storage::disk('public')->exists($ebook->cover_image)) {
                Storage::disk('public')->delete($ebook->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('ebooks/covers', 'public');
        }

        // Set boolean values
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        $ebook->update($validated);

        return redirect()->route('library.ebooks.show', $ebook)
            ->with('success', 'eBook updated successfully.');
    }

    public function destroy(EBook $ebook)
    {
        // Delete files
        if ($ebook->file_path && Storage::disk('public')->exists($ebook->file_path)) {
            Storage::disk('public')->delete($ebook->file_path);
        }

        if ($ebook->cover_image && Storage::disk('public')->exists($ebook->cover_image)) {
            Storage::disk('public')->delete($ebook->cover_image);
        }

        $ebook->delete();

        return redirect()->route('library.ebooks.index')
            ->with('success', 'eBook deleted successfully.');
    }

    public function toggleFeatured(EBook $ebook)
    {
        $ebook->update(['is_featured' => !$ebook->is_featured]);

        return back()->with('success', 'eBook featured status updated.');
    }

    public function toggleActive(EBook $ebook)
    {
        $ebook->update(['is_active' => !$ebook->is_active]);

        return back()->with('success', 'eBook status updated.');
    }
}
