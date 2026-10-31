<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Book;
use App\Models\Branch;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  
    public function index(Request $request)
    {
        // Remove 'currentTransaction' from with() - it's not a relationship
        $query = Item::with(['book', 'branch']);

        // Filter by branch
        if ($request->has('branch')) {
            $query->where('branch_id', $request->branch);
        }

        $items = $query->orderBy('barcode')->paginate(20);
        $books = Book::orderBy('title')->get();
        $branches = Branch::where('is_active', true)->get();

        // Stats
        $availableCount = Item::where('status', 'available')->count();
        $borrowedCount = Item::where('status', 'borrowed')->count();
        $maintenanceCount = Item::where('status', 'maintenance')->count();

        return view('ktvtc.library.items.index', compact(
            'items',
            'books',
            'branches',
            'availableCount',
            'borrowedCount',
            'maintenanceCount'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:50|unique:items,barcode',
            'book_id' => 'required|exists:books,id',
            'branch_id' => 'required|exists:branches,id',
            'condition' => 'required|in:new,good,fair,poor',
            'status' => 'required|in:available,borrowed,reserved,maintenance,lost,missing',
            'acquisition_date' => 'nullable|date',
            'acquisition_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['status'] === 'borrowed') {
            return back()->withInput()->with('error', 'A new copy cannot start as borrowed; lend it from Transactions.');
        }

        $item = Item::create($validated);
        $item->book->syncCopyCounts();

        return redirect()->route('library.items.index')
            ->with('success', 'Item added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:50|unique:items,barcode,' . $item->id,
            'book_id' => 'required|exists:books,id',
            'branch_id' => 'required|exists:branches,id',
            'condition' => 'required|in:new,good,fair,poor',
            'status' => 'required|in:available,borrowed,reserved,maintenance,lost,missing',
            'acquisition_date' => 'nullable|date',
            'acquisition_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Loans and waiting-list holds change status through lending, not by hand
        if (in_array($item->status, ['borrowed', 'reserved']) && $validated['status'] !== $item->status) {
            return back()->withInput()->with('error', "Copy {$item->barcode} is {$item->status}; return it or cancel the reservation first.");
        }
        if ($validated['status'] === 'borrowed' && $item->status !== 'borrowed') {
            return back()->withInput()->with('error', 'Lend copies from Transactions so the loan is recorded.');
        }

        $oldBook = $item->book;
        $item->update($validated);
        $item->book->syncCopyCounts();
        if ($oldBook && $oldBook->id !== $item->book_id) {
            $oldBook->syncCopyCounts();
        }

        return redirect()->route('library.items.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Stock-take and repairs: put a copy back on the shelf, or mark it in repair,
     * missing or lost. Copies on loan or held for a reader change through lending.
     */
    public function setStatus(Request $request, Item $item)
    {
        $validated = $request->validate(['status' => 'required|in:available,maintenance,missing,lost']);

        if (in_array($item->status, ['borrowed', 'reserved'])) {
            return back()->with('error', "Copy {$item->barcode} is {$item->status}; return it or cancel the reservation first.");
        }

        $item->update(['status' => $validated['status']]);
        $item->book?->syncCopyCounts();

        return back()->with('success', "Copy {$item->barcode} set to {$validated['status']}.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        if ($item->status === 'borrowed') {
            return redirect()->route('library.items.index')
                ->with('error', 'Cannot delete item that is currently borrowed.');
        }

        if ($item->status === 'reserved') {
            return redirect()->route('library.items.index')
                ->with('error', 'Cannot delete a copy held for a reader on the waiting list.');
        }

        $book = $item->book;
        $item->delete();
        $book?->syncCopyCounts();

        return redirect()->route('library.items.index')
            ->with('success', 'Item deleted successfully.');
    }
}
