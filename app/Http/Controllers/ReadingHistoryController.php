<?php

namespace App\Http\Controllers;

use App\Models\ReadingHistory;
use App\Models\Member;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReadingHistory::with(['member', 'book'])
            ->orderBy('created_at', 'desc');

        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        if ($request->has('status')) {
            $query->where('reading_status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%");
            })->orWhereHas('book', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $readingHistories = $query->paginate(20);
        $members = Member::active()->get();
        $books = Book::available()->get();

        // Statistics
        $activeReaders = ReadingHistory::where('reading_status', 'reading')
            ->distinct('member_id')->count('member_id');
        $completedBooks = ReadingHistory::where('reading_status', 'completed')->count();
        $currentlyReading = ReadingHistory::where('reading_status', 'reading')->count();
        $averageRating = ReadingHistory::whereNotNull('rating')->avg('rating');

        return view('ktvtc.library.reading.index', compact(
            'readingHistories',
            'members',
            'books',
            'activeReaders',
            'completedBooks',
            'currentlyReading',
            'averageRating'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'pages_read' => 'nullable|integer|min:0',
            'reading_status' => 'required|in:reading,completed,on_hold,dropped',
            'rating' => 'nullable|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);

        ReadingHistory::create([
            'member_id' => $validated['member_id'],
            'book_id' => $validated['book_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'pages_read' => $validated['pages_read'] ?? 0,
            'reading_status' => $validated['reading_status'],
            'rating' => $validated['rating'],
            'review' => $validated['review'],
            'created_by' => Auth::id()
        ]);

        return redirect()->route('reading-histories.index')
            ->with('success', 'Reading history added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReadingHistory $readingHistory)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'pages_read' => 'nullable|integer|min:0',
            'reading_status' => 'required|in:reading,completed,on_hold,dropped',
            'rating' => 'nullable|integer|min:1|max:5',
            'review' => 'nullable|string'
        ]);

        $readingHistory->update($validated);

        return redirect()->route('reading-histories.index')
            ->with('success', 'Reading history updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReadingHistory $readingHistory)
    {
        $readingHistory->delete();

        return redirect()->route('reading-histories.index')
            ->with('success', 'Reading history deleted successfully.');
    }
}
