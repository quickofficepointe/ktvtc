<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\EBook;
use App\Models\EBookCategory;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\AcquisitionRequest;
use App\Models\FineRule;
use App\Models\BookDonation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    /**
     * Public library catalog page (for website visitors)
     */
    public function index(Request $request)
    {
        // Fix: Use 'authors' (plural) not 'author'
        $query = Book::with(['category', 'items.branch', 'authors'])
            ->where('is_available', true);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('isbn', 'like', '%' . $search . '%')
                  ->orWhere('publisher', 'like', '%' . $search . '%')
                  ->orWhereHas('authors', function ($q) use ($search) {
                      $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('book_category_id', $request->category);
        }

        // Filter by availability
        if ($request->has('availability') && $request->availability === 'available') {
            $query->where('available_copies', '>', 0);
        }

        // Get books with pagination
        $books = $query->orderBy('title')->paginate(12);

        // Get categories for filter
        $categories = BookCategory::where('is_active', true)->orderBy('name')->get();

        // Get featured books
        $featuredBooks = Book::with(['category', 'authors'])
            ->where('is_available', true)
            ->where('available_copies', '>', 0)
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Get new arrivals
        $newArrivals = Book::with(['category', 'authors'])
            ->where('is_available', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get eBooks
        $eBooks = EBook::with('category')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get branches with book counts
        $branches = Branch::withCount(['items' => function ($query) {
            $query->where('status', 'available');
        }])->where('is_active', true)->get();

        return view('library.index', compact(
            'books',
            'categories',
            'featuredBooks',
            'newArrivals',
            'eBooks',
            'branches',
            'request'
        ));
    }

    public function dashboard()
    {
        // --------------------------------------------------------
        // Core counts
        // --------------------------------------------------------
        $totalBooks   = Book::count();
        $totalMembers = Member::count();

        // --------------------------------------------------------
        // Borrowing stats
        // --------------------------------------------------------
        $activeBorrows = Transaction::where('status', 'borrowed')->count();

        $overdueBooks = Transaction::where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->count();

        // --------------------------------------------------------
        // Pending workflow items
        // --------------------------------------------------------
        $pendingReservations = Reservation::where('status', 'pending')->count();

        $pendingAcquisitions = AcquisitionRequest::whereIn('status', ['pending', 'submitted'])
            ->count();

        // --------------------------------------------------------
        // Fines
        // Total uncollected fines across all borrowed/overdue items.
        // --------------------------------------------------------
        $defaultFinePerDay = 50; // KES per day — override if you have FineRule rows

        $overdueTransactions = Transaction::with(['item.book', 'member'])
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->get();

        $totalFines = $overdueTransactions->sum(function ($t) use ($defaultFinePerDay) {
            // FIX: use the transaction's due_date as the base and now() as the target
            $daysLate = \Carbon\Carbon::parse($t->due_date)->diffInDays(now());
            return $daysLate * $defaultFinePerDay;
        });

        // --------------------------------------------------------
        // Popular books (most transactions in last 30 days)
        // --------------------------------------------------------
        // FIX 1: qualify created_at with the transactions table to avoid
        //        the "ambiguous column" SQL error caused by the join with items.
        // FIX 2: use snake_case variable name to match the Blade view.
        $popular_books = Book::withCount(['transactions' => function ($q) {
                $q->where('transactions.created_at', '>=', now()->subDays(30));
            }])
            ->orderByDesc('transactions_count')
            ->limit(5)
            ->get();

        // --------------------------------------------------------
        // Recent transactions
        // --------------------------------------------------------
        $recent_transactions = Transaction::with(['member', 'item.book'])
            ->latest()
            ->limit(10)
            ->get();

        // --------------------------------------------------------
        // Recent members
        // --------------------------------------------------------
        $recent_members = Member::latest()
            ->limit(5)
            ->get();

        // --------------------------------------------------------
        // Bundle for the view
        // --------------------------------------------------------
        $stats = [
            'total_books'          => $totalBooks,
            'total_members'        => $totalMembers,
            'active_borrows'       => $activeBorrows,
            'overdue_books'        => $overdueBooks,
            'total_fines'          => $totalFines,
            'pending_reservations' => $pendingReservations,
            'pending_acquisitions' => $pendingAcquisitions,
        ];

        return view('ktvtc.library.dashboard', compact(
            'stats',
            'popular_books',
            'recent_transactions',
            'recent_members'
        ));
    }

    /**
     * Show single book details
     */
    public function show($id)
    {
        $book = Book::with(['category', 'items.branch', 'authors'])
            ->findOrFail($id);

        // Get available copies per branch
        $copiesByBranch = $book->items()
            ->with('branch')
            ->where('status', 'available')
            ->get()
            ->groupBy('branch.name')
            ->map(function ($items) {
                return $items->count();
            });

        // Get related books (same category)
        $relatedBooks = Book::with(['category', 'authors'])
            ->where('book_category_id', $book->book_category_id)
            ->where('id', '!=', $book->id)
            ->where('is_available', true)
            ->limit(4)
            ->get();

        return view('library.book-show', compact('book', 'copiesByBranch', 'relatedBooks'));
    }

    /**
     * Show single eBook details
     */
    public function showEBook(EBook $ebook)
    {
        $ebook->incrementViewCount();

        $relatedEBooks = EBook::where('category_id', $ebook->category_id)
            ->where('id', '!=', $ebook->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return view('library.ebook-show', compact('ebook', 'relatedEBooks'));
    }

    /**
     * Download eBook
     */
    public function downloadEBook(EBook $ebook)
    {
        $ebook->incrementDownloadCount();

        return response()->download(
            storage_path('app/public/' . $ebook->file_path),
            $ebook->title . '.' . strtolower($ebook->file_format)
        );
    }

    /**
     * Show donation form
     */
    public function donationForm()
    {
        return view('library.donation-form');
    }

    /**
     * Submit donation request
     */
    public function submitDonation(Request $request)
    {
        $validated = $request->validate([
            'full_name'       => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'country'         => 'required|string|max:100',
            'book_title'      => 'required|string|max:255',
            'author'          => 'nullable|string|max:255',
            'isbn'            => 'nullable|string|max:20',
            'quantity'        => 'required|integer|min:1|max:100',
            'condition'       => 'required|in:new,like-new,good,fair',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        BookDonation::create($validated);

        return redirect()->route('library.donation-form')
            ->with('success', 'Thank you for your donation request! Our library team will contact you within 3-5 business days.');
    }
}