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
        $query = Item::with(['book', 'branch', 'currentTransaction']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('barcode', 'like', "%$search%")
                  ->orWhereHas('book', function($q) use ($search) {
                      $q->where('title', 'like', "%$search%")
                        ->orWhere('isbn', 'like', "%$search%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by condition
        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter by branch
        if ($request->has('branch')) {
            $query->where('branch_id', $request->branch);
        }

        $items = $query->orderBy('barcode')->paginate(20);
        $books = Book::where('available_copies', '>', 0)->get();
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
            'status' => 'required|in:available,borrowed,reserved,maintenance,lost',
            'acquisition_date' => 'nullable|date',
            'acquisition_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = Item::create($validated);

        // Update book available copies if item is available
        if ($item->status === 'available') {
            $item->book->increment('available_copies');
        }

        return redirect()->route('items.index')
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
            'status' => 'required|in:available,borrowed,reserved,maintenance,lost',
            'acquisition_date' => 'nullable|date',
            'acquisition_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Handle status change for book available copies
        if ($item->status !== 'available' && $validated['status'] === 'available') {
            $item->book->increment('available_copies');
        } elseif ($item->status === 'available' && $validated['status'] !== 'available') {
            $item->book->decrement('available_copies');
        }

        $item->update($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        if ($item->status === 'borrowed') {
            return redirect()->route('items.index')
                ->with('error', 'Cannot delete item that is currently borrowed.');
        }

        // Update book available copies if item was available
        if ($item->status === 'available') {
            $item->book->decrement('available_copies');
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }
}
