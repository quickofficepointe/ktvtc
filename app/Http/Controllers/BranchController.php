<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['members', 'books', 'items', 'transactions'])
            ->orderBy('name')
            ->get();

        // Calculate totals from the collection (not from the database)
        $totalMembers = $branches->sum('members_count');
        $totalBooks = $branches->sum('books_count');

        return view('ktvtc.library.branches.index', compact('branches', 'totalMembers', 'totalBooks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'opening_time' => 'required',
            'closing_time' => 'required',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Branch::create($validated);

        return redirect()->route('library.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $branch->load(['members', 'books.category', 'items', 'transactions']);

        return view('ktvtc.library.branches.show', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'opening_time' => 'required',
            'closing_time' => 'required',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $branch->update($validated);

        return redirect()->route('library.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->members()->exists() ||
            $branch->books()->exists() ||
            $branch->items()->exists() ||
            $branch->transactions()->exists()) {
            return redirect()->route('library.branches.index')
                ->with('error', 'Cannot delete branch that has associated records.');
        }

        $branch->delete();

        return redirect()->route('library.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
