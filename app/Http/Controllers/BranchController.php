<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::withCount(['members', 'books', 'items', 'transactions'])
            ->orderBy('name')
            ->get();

        $totalMembers = Branch::sum('members_count');
        $totalBooks = Branch::sum('books_count');

        return view('branches.index', compact('branches', 'totalMembers', 'totalBooks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'code' => 'nullable|string|max:10|unique:branches,code',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        $branch->load(['members', 'books', 'items', 'transactions']);

        return view('branches.show', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'code' => 'nullable|string|max:10|unique:branches,code,' . $branch->id,
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        // Check if branch has any associated records
        if ($branch->members()->exists() || $branch->books()->exists() || $branch->items()->exists()) {
            return redirect()->route('branches.index')
                ->with('error', 'Cannot delete branch that has members, books, or items.');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
