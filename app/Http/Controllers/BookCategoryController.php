<?php

namespace App\Http\Controllers;

use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookCategoryController extends Controller
{
    public function index()
    {
        $categories = BookCategory::withCount('books')->orderBy('name')->get();
        $totalBooks = DB::table('books')->count();

        return view('book-categories.index', compact('categories', 'totalBooks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:book_categories,name',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean'
        ]);

        BookCategory::create($validated);

        return redirect()->route('book-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(BookCategory $bookCategory)
    {
        return response()->json($bookCategory);
    }

    public function update(Request $request, BookCategory $bookCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:book_categories,name,' . $bookCategory->id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'is_active' => 'boolean'
        ]);

        $bookCategory->update($validated);

        return redirect()->route('book-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(BookCategory $bookCategory)
    {
        // Check if category has books
        if ($bookCategory->books()->exists()) {
            return redirect()->route('book-categories.index')
                ->with('error', 'Cannot delete category that has books. Please reassign books first.');
        }

        $bookCategory->delete();

        return redirect()->route('book-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
