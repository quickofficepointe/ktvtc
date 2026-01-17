<?php

namespace App\Http\Controllers;

use App\Models\WeedingCandidate;
use App\Models\Book;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeedingCandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = WeedingCandidate::with(['book', 'branch'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        $candidates = $query->paginate(20);
        $books = Book::all();
        $branches = Branch::active()->get();

        // Statistics
        $pendingCount = WeedingCandidate::where('status', 'pending')->count();
        $approvedCount = WeedingCandidate::where('status', 'approved')->count();
        $rejectedCount = WeedingCandidate::where('status', 'rejected')->count();
        $processedCount = WeedingCandidate::where('status', 'processed')->count();

        return view('ktvtc.library.weeding.index', compact(
            'candidates',
            'books',
            'branches',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'processedCount'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'branch_id' => 'required|exists:branches,id',
            'reason' => 'required|string',
            'condition' => 'required|in:poor,fair,damaged,obsolete',
            'calculate_metrics' => 'boolean'
        ]);

        // Calculate usage metrics if requested
        $metrics = [];
        if ($request->boolean('calculate_metrics')) {
            $metrics = $this->calculateBookMetrics($validated['book_id'], $validated['branch_id']);
        }

        $candidate = WeedingCandidate::create([
            'book_id' => $validated['book_id'],
            'branch_id' => $validated['branch_id'],
            'reason' => $validated['reason'],
            'condition' => $validated['condition'],
            'last_borrowed_date' => $metrics['last_borrowed_date'] ?? null,
            'days_since_last_borrow' => $metrics['days_since_last_borrow'] ?? null,
            'total_borrows' => $metrics['total_borrows'] ?? 0,
            'status' => 'pending',
            'created_by' => Auth::id()
        ]);

        return redirect()->route('weeding-candidates.index')
            ->with('success', 'Weeding candidate added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WeedingCandidate $weedingCandidate)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'condition' => 'required|in:poor,fair,damaged,obsolete',
            'review_notes' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected,processed'
        ]);

        $weedingCandidate->update([
            'reason' => $validated['reason'],
            'condition' => $validated['condition'],
            'review_notes' => $validated['review_notes'] ?? null,
            'status' => $validated['status'],
            'reviewed_by' => Auth::id(),
            'review_date' => now()
        ]);

        return redirect()->route('weeding-candidates.index')
            ->with('success', 'Weeding candidate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WeedingCandidate $weedingCandidate)
    {
        $weedingCandidate->delete();

        return redirect()->route('weeding-candidates.index')
            ->with('success', 'Weeding candidate deleted successfully.');
    }

    /**
     * Show review form
     */
    public function review(WeedingCandidate $weedingCandidate)
    {
        return response()->json([
            'book_title' => $weedingCandidate->book->title,
            'book_isbn' => $weedingCandidate->book->isbn,
            'branch_name' => $weedingCandidate->branch->name,
            'reason' => $weedingCandidate->reason,
            'condition' => $weedingCandidate->condition,
            'last_borrowed_date' => $weedingCandidate->last_borrowed_date
                ? $weedingCandidate->last_borrowed_date->format('M d, Y')
                : 'Never',
            'days_since_last_borrow' => $weedingCandidate->days_since_last_borrow,
            'total_borrows' => $weedingCandidate->total_borrows
        ]);
    }

    /**
     * Process review
     */
    public function processReview(Request $request, WeedingCandidate $weedingCandidate)
    {
        $validated = $request->validate([
            'review_notes' => 'required|string',
            'action' => 'required|in:approve,reject'
        ]);

        $status = $request->action === 'approve' ? 'approved' : 'rejected';

        $weedingCandidate->update([
            'status' => $status,
            'review_notes' => $validated['review_notes'],
            'reviewed_by' => Auth::id(),
            'review_date' => now()
        ]);

        return redirect()->route('weeding-candidates.index')
            ->with('success', 'Weeding candidate ' . $status . ' successfully.');
    }

    /**
     * Process candidate (mark as removed)
     */
    public function process(WeedingCandidate $weedingCandidate)
    {
        $weedingCandidate->update([
            'status' => 'processed',
            'processed_at' => now(),
            'processed_by' => Auth::id()
        ]);

        // Here you would also update the book/item status in your system
        // For example: mark book as removed from inventory

        return redirect()->back()->with('success', 'Weeding candidate processed successfully.');
    }

    /**
     * Calculate book usage metrics
     */
    private function calculateBookMetrics($bookId, $branchId)
    {
        // This is a simplified example - you'll need to adjust based on your actual models
        $lastTransaction = \App\Models\Transaction::whereHas('item', function ($query) use ($bookId) {
                $query->where('book_id', $bookId);
            })
            ->where('branch_id', $branchId)
            ->latest('borrow_date')
            ->first();

        $totalBorrows = \App\Models\Transaction::whereHas('item', function ($query) use ($bookId) {
                $query->where('book_id', $bookId);
            })
            ->where('branch_id', $branchId)
            ->count();

        return [
            'last_borrowed_date' => $lastTransaction ? $lastTransaction->borrow_date : null,
            'days_since_last_borrow' => $lastTransaction ? now()->diffInDays($lastTransaction->borrow_date) : null,
            'total_borrows' => $totalBorrows
        ];
    }
}
