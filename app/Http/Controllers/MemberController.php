<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Branch;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Member::with('branch');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('member_id', 'like', "%$search%");
            });
        }

        // Filter by membership type
        if ($request->has('type')) {
            $query->where('membership_type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $members = $query->orderBy('last_name')->orderBy('first_name')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        // Stats
        $activeCount = Member::where('is_active', true)->count();
        $totalFines = Member::sum('outstanding_fines');
        $expiringSoon = Member::where('membership_end_date', '<=', now()->addDays(30))
            ->where('membership_end_date', '>=', now())
            ->count();

        return view('ktvtc.library.members.index', compact(
            'members',
            'branches',
            'activeCount',
            'totalFines',
            'expiringSoon'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'branch_id' => 'required|exists:branches,id',
            'address' => 'nullable|string|max:500',
            'membership_type' => 'required|in:regular,premium,student,faculty',
            'max_borrow_limit' => 'required|integer|min:1|max:20',
            'membership_start_date' => 'required|date',
            'membership_end_date' => 'required|date|after:membership_start_date',
            'is_active' => 'boolean',
        ]);

        // Generate member ID
        $validated['member_id'] = $this->generateMemberId($validated['branch_id']);

        Member::create($validated);

        return redirect()->route('members.index')
            ->with('success', 'Member added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        $member->load([
            'branch',
            'transactions' => function($query) {
                $query->with(['item.book'])->orderBy('borrow_date', 'desc');
            },
            'readingHistories' => function($query) {
                $query->with('book')->orderBy('start_date', 'desc');
            }
        ]);

        return view('members.show', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:members,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'branch_id' => 'required|exists:branches,id',
            'address' => 'nullable|string|max:500',
            'membership_type' => 'required|in:regular,premium,student,faculty',
            'max_borrow_limit' => 'required|integer|min:1|max:20',
            'membership_start_date' => 'required|date',
            'membership_end_date' => 'required|date|after:membership_start_date',
            'is_active' => 'boolean',
        ]);

        $member->update($validated);

        return redirect()->route('members.index')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        if ($member->transactions()->whereNull('return_date')->exists()) {
            return redirect()->route('members.index')
                ->with('error', 'Cannot delete member with active borrowings.');
        }

        $member->delete();

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully.');
    }

    /**
     * Generate unique member ID.
     */
    private function generateMemberId($branchId)
    {
        $branch = Branch::find($branchId);
        $prefix = strtoupper(substr($branch->name, 0, 3));
        $year = date('y');
        $sequence = Member::where('branch_id', $branchId)->count() + 1;

        return $prefix . '-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
