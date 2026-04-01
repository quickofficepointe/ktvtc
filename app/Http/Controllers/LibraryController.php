<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\Reservation;
use App\Models\AcquisitionRequest;
use App\Models\BookCategory;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    /**
     * Public library catalog page (for website visitors)
     */
    public function index(Request $request)
    {
        $query = Book::with(['category', 'author', 'items'])
            ->where('is_available', true); // Changed from is_active to is_available

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%')
                  ->orWhere('publisher', 'like', '%' . $request->search . '%')
                  ->orWhereHas('author', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by availability
        if ($request->has('availability') && $request->availability === 'available') {
            $query->where('available_copies', '>', 0);
        }

        // Get books with pagination
        $books = $query->orderBy('title')->paginate(12);

        // Get categories for filter
        $categories = BookCategory::orderBy('name')->get();

        // Get featured books (most popular - based on transaction count or just random)
        $featuredBooks = Book::with(['category', 'author'])
            ->where('is_available', true)
            ->where('available_copies', '>', 0)
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Get new arrivals (last 30 days)
        $newArrivals = Book::with(['category', 'author'])
            ->where('is_available', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('library.index', compact(
            'books',
            'categories',
            'featuredBooks',
            'newArrivals',
            'request'
        ));
    }

    /**
     * Show single book details
     */
    public function show($slug)
    {
        // Since you don't have slug in your books table, you might need to use ID
        // If you want to use slug, you'll need to add slug column to books table

        // For now, using ID
        $book = Book::with(['category', 'author', 'items'])
            ->findOrFail($slug);

        // Get available copies
        $availableCopies = $book->available_copies;

        // Get related books (same category)
        $relatedBooks = Book::with(['category', 'author'])
            ->where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->where('is_available', true)
            ->limit(4)
            ->get();

        return view('library.show', compact('book', 'availableCopies', 'relatedBooks'));
    }

    /**
     * Search books (AJAX endpoint)
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $books = Book::with(['category', 'author'])
            ->where('title', 'like', "%{$query}%")
            ->orWhere('isbn', 'like', "%{$query}%")
            ->orWhere('publisher', 'like', "%{$query}%")
            ->orWhereHas('author', function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->where('is_available', true)
            ->limit(10)
            ->get()
            ->map(function($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'cover_image' => $book->cover_image ? asset('storage/' . $book->cover_image) : null,
                    'author' => $book->author ? $book->author->name : 'Unknown',
                    'available_copies' => $book->available_copies,
                    'publisher' => $book->publisher,
                    'publication_year' => $book->publication_year
                ];
            });

        return response()->json($books);
    }

    /**
     * Reserve a book (requires authentication)
     */
    public function reserve(Request $request, $bookId)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        $book = Book::findOrFail($bookId);

        // Check if user is already a member
        $member = Member::find($request->member_id);

        if (!$member) {
            return back()->with('error', 'You need to be a library member to reserve books.');
        }

        // Check if member has exceeded borrowing limit (if you have this field)
        if (property_exists($member, 'borrowing_limit')) {
            $activeBorrows = Transaction::where('member_id', $member->id)
                ->where('status', 'borrowed')
                ->count();

            if ($activeBorrows >= $member->borrowing_limit) {
                return back()->with('error', 'You have reached your borrowing limit. Please return some books first.');
            }
        }

        // Check if member has unpaid fines
        $hasFines = Transaction::where('member_id', $member->id)
            ->where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->exists();

        if ($hasFines) {
            return back()->with('error', 'You have unpaid fines. Please clear them before reserving books.');
        }

        // Check if book has available copies
        if ($book->available_copies > 0) {
            // Book is available - can borrow directly
            return redirect()->route('library.borrow', $book->id)
                ->with('success', 'This book is available. You can borrow it directly.');
        } else {
            // Check if member is already in queue
            $existingReservation = Reservation::where('member_id', $member->id)
                ->where('book_id', $book->id)
                ->where('status', 'active')
                ->first();

            if ($existingReservation) {
                return back()->with('error', 'You already have an active reservation for this book.');
            }

            // Get current queue position
            $queuePosition = Reservation::where('book_id', $book->id)
                ->where('status', 'active')
                ->count() + 1;

            // Create reservation
            $reservation = Reservation::create([
                'member_id' => $member->id,
                'book_id' => $book->id,
                'branch_id' => $member->branch_id ?? 1,
                'reservation_date' => now(),
                'expiry_date' => now()->addDays(7),
                'status' => 'active',
                'queue_position' => $queuePosition,
                'notes' => $request->notes
            ]);

            return redirect()->route('library.my-reservations')
                ->with('success', "Book reserved! Your position in queue: {$queuePosition}");
        }
    }

    /**
     * Display user's reservations
     */
    public function myReservations(Request $request)
    {
        $memberId = $request->user()->member_id ?? $request->get('member_id');

        $reservations = Reservation::with(['book', 'branch'])
            ->where('member_id', $memberId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('library.reservations', compact('reservations'));
    }

    /**
     * Cancel a reservation
     */
    public function cancelReservation(Reservation $reservation)
    {
        // Check if user owns this reservation
        if (auth()->check() && $reservation->member_id != auth()->user()->member_id) {
            abort(403);
        }

        // Check if reservation is still active
        if ($reservation->status !== 'active') {
            return back()->with('error', 'This reservation cannot be cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        // Reorder queue positions for remaining reservations
        $this->reorderQueue($reservation->book_id);

        return back()->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * Reorder queue positions after cancellation
     */
    private function reorderQueue($bookId)
    {
        $reservations = Reservation::where('book_id', $bookId)
            ->where('status', 'active')
            ->orderBy('queue_position')
            ->get();

        $position = 1;
        foreach ($reservations as $reservation) {
            $reservation->update(['queue_position' => $position]);
            $position++;
        }
    }

    /**
     * Dashboard for library admin
     */
    public function dashboard()
    {
        // Get stats using DB facade
        $stats = [
            'total_books' => DB::table('books')->count(),
            'available_books' => DB::table('books')->sum('available_copies'),
            'total_members' => DB::table('members')->count(),
            'active_borrows' => Transaction::where('status', 'borrowed')->count(),
            'overdue_books' => Transaction::where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->count(),
            'pending_reservations' => Reservation::where('status', 'active')->count(),
            'pending_acquisitions' => AcquisitionRequest::where('status', 'pending')->count(),
            'total_fines' => Transaction::where('fine_amount', '>', 0)->sum('fine_amount'),
        ];

        // Recent activity
        $recent_transactions = Transaction::with(['member', 'item.book'])
            ->latest()
            ->take(10)
            ->get();

        // Get recent members
        $recent_members = DB::table('members')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Popular books (based on transaction count)
        $popular_books = Book::withCount(['transactions'])
            ->orderBy('transactions_count', 'desc')
            ->take(5)
            ->get();

        return view('ktvtc.library.dashboard', compact(
            'stats',
            'recent_transactions',
            'recent_members',
            'popular_books'
        ));
    }

    /**
     * Borrow a book directly
     */
    public function borrow($bookId)
    {
        $book = Book::findOrFail($bookId);

        if ($book->available_copies <= 0) {
            return redirect()->route('library.index')
                ->with('error', 'This book is not available for borrowing.');
        }

        return view('library.borrow', compact('book'));
    }
}
