<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Author;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with(['category', 'authors'])
            ->withCount('items');
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('isbn', 'like', "%$search%")
                  ->orWhere('publisher', 'like', "%$search%")
                  ->orWhereHas('authors', function($q) use ($search) {
                      $q->where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%");
                  });
            });
        }
        // Filter by category
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }
        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'available') {
                $query->where('available_copies', '>', 0);
            } elseif ($request->status === 'borrowed') {
                $query->where('available_copies', '<', DB::raw('total_copies'));
            }
        }
        $books = $query->orderBy('title')->paginate(20);
        $categories = BookCategory::where('is_active', true)->get();
        $availableCount = Book::where('available_copies', '>', 0)->count();
        $borrowedCount = Book::where('available_copies', '<', DB::raw('total_copies'))->count();
        $newThisMonth = Book::where('created_at', '>=', now()->subMonth())->count();
        return view('ktvtc.library.book.index', compact(
            'books',
            'categories',
            'availableCount',
            'borrowedCount',
            'newThisMonth'
        ));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn',
            'category_id' => 'required|exists:book_categories,id',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'publisher' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:50',
            'page_count' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'total_copies' => 'required|integer|min:1',
            'available_copies' => 'required|integer|min:0|lte:total_copies',
            'cover_image' => 'nullable|image|max:2048',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
        }
        $validated['is_available'] = $validated['available_copies'] > 0;
        $book = Book::create($validated);
        // Attach authors
        if ($request->has('author_ids')) {
            $book->authors()->attach($request->author_ids);
        }
        return redirect()->route('books.index')
            ->with('success', 'Book added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load(['category', 'authors', 'items.branch', 'items.transactions' => function($query) {
            $query->whereNull('return_date')->latest();
        }]);

        return view('books.show', compact('book'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn,' . $book->id,
            'category_id' => 'required|exists:book_categories,id',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'publisher' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:50',
            'page_count' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'total_copies' => 'required|integer|min:1',
            'available_copies' => 'required|integer|min:0|lte:total_copies',
            'cover_image' => 'nullable|image|max:2048',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old cover if exists
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
        }

        $validated['is_available'] = $validated['available_copies'] > 0;

        $book->update($validated);

        // Sync authors
        if ($request->has('author_ids')) {
            $book->authors()->sync($request->author_ids);
        }

        return redirect()->route('books.index')
            ->with('success', 'Book updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        if ($book->items()->exists()) {
            return redirect()->route('books.index')
                ->with('error', 'Cannot delete book that has items. Delete items first.');
        }
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        $book->delete();
        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully.');
    }
}
