<?php

namespace App\Http\Controllers;

use App\Models\ReadingChallenge;
use App\Models\Member;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReadingChallenge::with(['member', 'books'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $challenges = $query->paginate(20);
        $members = Member::active()->get();
        $books = Book::available()->get();

        // Statistics
        $activeChallenges = ReadingChallenge::where('status', 'active')->count();
        $completedChallenges = ReadingChallenge::where('status', 'completed')->count();
        $averageBooks = ReadingChallenge::where('status', 'completed')->avg('completed_books');

        return view('ktvtc.library.reading-challenges.index', compact(
            'challenges',
            'members',
            'books',
            'activeChallenges',
            'completedChallenges',
            'averageBooks'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_books' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reward_points' => 'nullable|integer|min:0',
            'status' => 'required|in:active,completed,abandoned'
        ]);

        $challenge = ReadingChallenge::create([
            'member_id' => $validated['member_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'target_books' => $validated['target_books'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reward_points' => $validated['reward_points'] ?? 0,
            'status' => $validated['status'],
            'created_by' => Auth::id()
        ]);

        return redirect()->route('reading-challenges.index')
            ->with('success', 'Reading challenge created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReadingChallenge $readingChallenge)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_books' => 'required|integer|min:1|max:100',
            'completed_books' => 'nullable|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'reward_points' => 'nullable|integer|min:0',
            'status' => 'required|in:active,completed,abandoned'
        ]);

        $readingChallenge->update($validated);

        return redirect()->route('reading-challenges.index')
            ->with('success', 'Reading challenge updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReadingChallenge $readingChallenge)
    {
        $readingChallenge->delete();

        return redirect()->route('reading-challenges.index')
            ->with('success', 'Reading challenge deleted successfully.');
    }

    /**
     * Add book to challenge
     */
    public function addBook(Request $request, ReadingChallenge $readingChallenge)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id'
        ]);

        $readingChallenge->books()->attach($validated['book_id'], [
            'added_at' => now(),
            'added_by' => Auth::id()
        ]);

        // Update completed books count
        $readingChallenge->update([
            'completed_books' => $readingChallenge->books()->count()
        ]);

        return redirect()->back()->with('success', 'Book added to challenge.');
    }

    /**
     * Remove book from challenge
     */
    public function removeBook(Request $request, ReadingChallenge $readingChallenge)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id'
        ]);

        $readingChallenge->books()->detach($validated['book_id']);

        // Update completed books count
        $readingChallenge->update([
            'completed_books' => $readingChallenge->books()->count()
        ]);

        return redirect()->back()->with('success', 'Book removed from challenge.');
    }
}
