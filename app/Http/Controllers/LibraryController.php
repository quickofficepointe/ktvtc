<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\Reservation;
use App\Models\AcquisitionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    public function dashboard()
    {
        // Get stats using DB facade to avoid soft delete issues
        $stats = [
            'total_books' => DB::table('books')->count(),
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

        // Get recent members using raw query
        $recent_members = DB::table('members')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Simple approach: Get books and manually add transaction counts
        $books = Book::all();

        $popular_books = $books->map(function ($book) {
            // Count transactions for this book through its items
            $book->transactions_count = DB::table('transactions')
                ->join('items', 'transactions.item_id', '=', 'items.id')
                ->where('items.book_id', $book->id)
                ->count();
            return $book;
        })->sortByDesc('transactions_count')
          ->take(5);

        return view('ktvtc.library.dashboard', compact(
            'stats',
            'recent_transactions',
            'recent_members',
            'popular_books'
        ));
    }
}
