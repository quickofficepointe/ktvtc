<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authors = Author::withCount('books')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $totalBooks = $authors->sum('books_count');
        $mostBooks = $authors->max('books_count');

        return view('ktvtc.library.authors.index', compact('authors', 'totalBooks', 'mostBooks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('authors', 'public');
        }

        Author::create($validated);

        return redirect()->route('authors.index')
            ->with('success', 'Author added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {
        $author->load(['books' => function($query) {
            $query->with('category')->orderBy('title');
        }]);

        return view('authors.show', compact('author'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($author->photo) {
                Storage::disk('public')->delete($author->photo);
            }
            $validated['photo'] = $request->file('photo')->store('authors', 'public');
        }

        $author->update($validated);

        return redirect()->route('authors.index')
            ->with('success', 'Author updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        if ($author->photo) {
            Storage::disk('public')->delete($author->photo);
        }

        $author->delete();

        return redirect()->route('authors.index')
            ->with('success', 'Author deleted successfully.');
    }
}
