<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Member;
use App\Models\Item;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\Library\LendingException;
use App\Services\Library\LendingService;
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

        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->whereIn('status', ['borrowed', 'overdue'])
                    ->whereDate('due_date', '<', today());
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date')) {
            switch ($request->date) {
                case 'today':
                    $query->whereDate('borrow_date', today());
                    break;
                case 'week':
                    $query->whereBetween('borrow_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('borrow_date', now()->month)->whereYear('borrow_date', now()->year);
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($w) use ($search) {
                $w->whereHas('member', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('member_id', 'like', "%{$search}%");
                })->orWhereHas('item', fn ($q) => $q->where('barcode', 'like', "%{$search}%"))
                  ->orWhereHas('item.book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                });
            });
        }

        $transactions = $query->paginate(20);
        $members = Member::active()->get();
        $availableItems = Item::where('status', 'available')->get();
        $branches = Branch::active()->get();

        // Statistics
        $activeBorrows = Transaction::where('status', 'borrowed')->count();
        $overdueCount = Transaction::whereIn('status', ['borrowed', 'overdue'])
            ->whereDate('due_date', '<', today())->count();
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
     * Lend a copy. The member can be picked from the list or typed as a member or
     * student number (students become members automatically); the copy by barcode.
     */
    public function store(Request $request, LendingService $lending)
    {
        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'member_number' => 'nullable|string|max:50',
            'item_id' => 'nullable|exists:items,id',
            'barcode' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $member = $this->findMember($validated, $lending);
        $item = !empty($validated['item_id']) ? Item::find($validated['item_id'])
            : (!empty($validated['barcode']) ? Item::where('barcode', trim($validated['barcode']))->first() : null);

        if (!$member) {
            return back()->withInput()->with('error', 'No library member or active student found with that number.');
        }
        if (!$item) {
            return back()->withInput()->with('error', 'No copy found with that barcode.');
        }

        try {
            $loan = $lending->issue($member, $item, $validated['due_date'] ?? null, Auth::id(), $validated['notes'] ?? null);
        } catch (LendingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('library.transactions.index')
            ->with('success', "Lent \"{$item->book->title}\" ({$item->barcode}) to {$member->full_name}, due {$loan->due_date->format('d M Y')}.");
    }

    private function findMember(array $data, LendingService $lending): ?Member
    {
        if (!empty($data['member_id'])) {
            $member = Member::find($data['member_id']);
            return $member?->student ? $lending->memberForStudent($member->student) : $member;
        }
        $number = trim((string) ($data['member_number'] ?? ''));
        if ($number === '') {
            return null;
        }
        if ($member = Member::where('member_id', $number)->first()) {
            return $member->student ? $lending->memberForStudent($member->student) : $member;
        }
        $student = Student::whereNull('merged_into_id')->where('student_number', $number)->first()
            ?? Enrollment::where('student_number', $number)->first()?->student;

        return $student ? $lending->memberForStudent($student) : null;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('library.transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
/**
 * Display the specified resource.
 */
public function show(Transaction $transaction)
{
    $transaction->load(['member', 'item.book', 'branch']);

    return view('ktvtc.library.transactions.show', compact('transaction'));
}
    /**
     * Take a book back; the fine comes from the active fine rules.
     */
    public function returnBook(Transaction $transaction, LendingService $lending)
    {
        try {
            $loan = $lending->returnLoan($transaction, Auth::id());
        } catch (LendingException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = 'Book returned.';
        if ($loan->fine_amount > 0) {
            $message .= ' Late by ' . $loan->days_overdue . ' day(s): fine KES ' . number_format((float) $loan->fine_amount, 2) . ' added to the member.';
        }
        if ($loan->item?->status === 'reserved') {
            $message .= ' Put the copy aside: it is held for the next reader on the waiting list.';
        }

        return back()->with('success', $message);
    }
}
