<?php

namespace App\Http\Controllers;

use App\Models\BookPopularity;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookPopularityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $popularities = BookPopularity::with('book')
            ->orderBy('popularity_score', 'desc')
            ->paginate(20);
        $stats = [
            'total_books' => Book::count(),
            'books_tracked' => BookPopularity::count(),
            'average_score' => BookPopularity::avg('popularity_score') ?? 0,
            'top_score' => BookPopularity::max('popularity_score') ?? 0,
        ];
        return view('ktvtc.library.popularities.index', compact('popularities', 'stats'));
    }

    /**
     * Refresh all popularity scores.
     */
    public function refresh()
    {
        try {
            // Get all book popularities
            $popularities = BookPopularity::all();

            foreach ($popularities as $popularity) {
                $popularity->calculatePopularityScore();
            }

            return redirect()->route('book-popularities.index')
                ->with('success', 'All popularity scores have been refreshed successfully!');

        } catch (\Exception $e) {
            return redirect()->route('book-popularities.index')
                ->with('error', 'Error refreshing popularity scores: ' . $e->getMessage());
        }
    }

    /**
     * Generate popularity report.
     */
    public function report(Request $request)
    {
        $timeframe = $request->get('timeframe', 'all');

        $query = BookPopularity::with('book')
            ->orderBy('popularity_score', 'desc');

        // Apply timeframe filter if needed
        if ($timeframe === 'month') {
            $query->where('created_at', '>=', now()->subMonth());
        } elseif ($timeframe === 'week') {
            $query->where('created_at', '>=', now()->subWeek());
        }
        $popularities = $query->get();
        // Generate report data
        $reportData = [
            'top_10_popular' => $popularities->take(10),
            'lowest_10_popular' => $popularities->sortBy('popularity_score')->take(10),
            'most_borrowed' => BookPopularity::orderBy('borrow_count', 'desc')->take(10)->get(),
            'most_viewed' => BookPopularity::orderBy('view_count', 'desc')->take(10)->get(),
            'most_reserved' => BookPopularity::orderBy('reservation_count', 'desc')->take(10)->get(),
        ];
        $stats = [
            'total_books' => $popularities->count(),
            'avg_borrow_count' => $popularities->avg('borrow_count') ?? 0,
            'avg_view_count' => $popularities->avg('view_count') ?? 0,
            'avg_reservation_count' => $popularities->avg('reservation_count') ?? 0,
            'timeframe' => $timeframe,
        ];

        return view('ktvtc.library.popularities.report', compact('reportData', 'stats'));
    }

    /**
     * Update popularity for a specific book.
     */
    public function updatePopularity(Request $request, BookPopularity $bookPopularity)
    {
        $request->validate([
            'borrow_count' => 'nullable|integer|min:0',
            'reservation_count' => 'nullable|integer|min:0',
            'view_count' => 'nullable|integer|min:0',
        ]);
        try {
            $bookPopularity->update($request->only(['borrow_count', 'reservation_count', 'view_count']));
            $bookPopularity->calculatePopularityScore();

            return redirect()->route('book-popularities.index')
                ->with('success', 'Popularity data updated successfully!');

        } catch (\Exception $e) {
            return redirect()->route('book-popularities.index')
                ->with('error', 'Error updating popularity: ' . $e->getMessage());
        }
    }
    /**
     * Show form to manually add popularity tracking for a book.
     */
    public function create(Book $book)
    {
        $booksWithoutTracking = Book::whereNotIn('id', function($query) {
            $query->select('book_id')->from('book_popularities');
        })->get();
        return view('ktvtc.library.popularities.create', compact('booksWithoutTracking', 'book'));
    }
    /**
     * Store new popularity tracking for a book.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id|unique:book_popularities,book_id',
            'borrow_count' => 'nullable|integer|min:0',
            'reservation_count' => 'nullable|integer|min:0',
            'view_count' => 'nullable|integer|min:0',
        ]);

        try {
            $popularity = BookPopularity::create([
                'book_id' => $request->book_id,
                'borrow_count' => $request->borrow_count ?? 0,
                'reservation_count' => $request->reservation_count ?? 0,
                'view_count' => $request->view_count ?? 0,
            ]);

            $popularity->calculatePopularityScore();

            return redirect()->route('book-popularities.index')
                ->with('success', 'Book popularity tracking added successfully!');

        } catch (\Exception $e) {
            return redirect()->route('book-popularities.index')
                ->with('error', 'Error adding popularity tracking: ' . $e->getMessage());
        }
    }
}
