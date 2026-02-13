<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Member;
use App\Models\Item;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['member', 'item.book', 'branch'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            if ($request->status === 'overdue') {
                $query->where('status', 'borrowed')
                    ->where('due_date', '<', now());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('date')) {
            switch ($request->date) {
                case 'today':
                    $query->whereDate('borrow_date', today());
                    break;
                case 'week':
                    $query->whereBetween('borrow_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('borrow_date', now()->month);
                    break;
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%");
            })->orWhereHas('item.book', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(20);
        $members = Member::active()->get();
        $availableItems = Item::where('status', 'available')->get();
        $branches = Branch::active()->get();

        // Statistics
        $activeBorrows = Transaction::where('status', 'borrowed')->count();
        $overdueCount = Transaction::where('status', 'borrowed')
            ->where('due_date', '<', now())->count();
        $returnedToday = Transaction::where('status', 'returned')
            ->whereDate('return_date', today())->count();
        $totalFines = Transaction::where('fine_amount', '>', 0)->sum('fine_amount');

        return view('ktvtc.library.transactions.index', compact(
            'transactions',
            'members',
            'availableItems',
            'branches',
            'activeBorrows',
            'overdueCount',
            'returnedToday',
            'totalFines'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'item_id' => 'required|exists:items,id',
            'branch_id' => 'required|exists:branches,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
            'notes' => 'nullable|string'
        ]);

        // Check if member can borrow more books
        $activeBorrows = Transaction::where('member_id', $validated['member_id'])
            ->where('status', 'borrowed')->count();

        if ($activeBorrows >= 5) { // Assuming max 5 books per member
            return redirect()->back()->with('error', 'Member has reached maximum borrowing limit.');
        }

        // Check if item is available
        $item = Item::find($validated['item_id']);
        if ($item->status !== 'available') {
            return redirect()->back()->with('error', 'Book is not available for borrowing.');
        }

        // Create transaction
        $transaction = Transaction::create([
            'member_id' => $validated['member_id'],
            'item_id' => $validated['item_id'],
            'branch_id' => $validated['branch_id'],
            'borrow_date' => $validated['borrow_date'],
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'],
            'status' => 'borrowed',
            'created_by' => Auth::id()
        ]);

        // Update item status
        $item->update(['status' => 'borrowed']);

        return redirect()->route('transactions.index')
            ->with('success', 'Book borrowed successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
            'return_date' => 'nullable|date',
            'fine_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:borrowed,returned,overdue,lost'
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Return a book
     */
    public function returnBook(Transaction $transaction)
    {
        // Calculate fine if overdue
        $fineAmount = 0;
        if ($transaction->due_date < now()) {
            $daysOverdue = now()->diffInDays($transaction->due_date);
            $fineAmount = $daysOverdue * 0.50; // $0.50 per day fine
        }

        $transaction->update([
            'status' => 'returned',
            'return_date' => now(),
            'fine_amount' => $fineAmount,
            'returned_by' => Auth::id()
        ]);

        // Update item status back to available
        $transaction->item->update(['status' => 'available']);

        return redirect()->back()->with('success', 'Book returned successfully.');
    }
}
