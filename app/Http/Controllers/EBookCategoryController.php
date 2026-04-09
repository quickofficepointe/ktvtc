<?php

namespace App\Http\Controllers;

use App\Models\EBookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EBookCategoryController extends Controller
{
    public function index()
    {
        $categories = EBookCategory::withCount('eBooks')
            ->ordered()
            ->get();

        $totalCategories = $categories->count();
        $activeCategories = $categories->where('is_active', true)->count();
        $totalEBooks = $categories->sum('e_books_count');

        return view('ktvtc.library.ebook-categories.index', compact(
            'categories',
            'totalCategories',
            'activeCategories',
            'totalEBooks'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:e_book_categories,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        EBookCategory::create($validated);

        return redirect()->route('library.ebook-categories.index')
            ->with('success', 'E-Book category created successfully.');
    }

    public function update(Request $request, EBookCategory $eBookCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:e_book_categories,name,' . $eBookCategory->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $eBookCategory->update($validated);

        return redirect()->route('library.ebook-categories.index')
            ->with('success', 'E-Book category updated successfully.');
    }

    public function destroy(EBookCategory $eBookCategory)
    {
        // Check if category has eBooks
        if ($eBookCategory->eBooks()->exists()) {
            return redirect()->route('library.ebook-categories.index')
                ->with('error', 'Cannot delete category that has eBooks. Move or delete the eBooks first.');
        }

        $eBookCategory->delete();

        return redirect()->route('library.ebook-categories.index')
            ->with('success', 'E-Book category deleted successfully.');
    }

    public function toggleStatus(EBookCategory $eBookCategory)
    {
        $eBookCategory->update(['is_active' => !$eBookCategory->is_active]);

        $status = $eBookCategory->is_active ? 'activated' : 'deactivated';

        return redirect()->route('library.ebook-categories.index')
            ->with('success', "Category {$status} successfully.");
    }
}
