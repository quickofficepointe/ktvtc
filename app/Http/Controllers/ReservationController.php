<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Member;
use App\Models\Book;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['member', 'book', 'branch'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $reservations = $query->paginate(20);
        $members = Member::active()->get();
        $books = Book::available()->get();
        $branches = Branch::active()->get();

        // Statistics
        $activeReservations = Reservation::where('status', 'active')->count();
        $fulfilledToday = Reservation::where('status', 'fulfilled')
            ->whereDate('updated_at', today())->count();
        $expiringSoon = Reservation::where('status', 'active')
            ->where('expiry_date', '<=', now()->addDays(2))
            ->count();
        $uniqueMembers = Reservation::distinct('member_id')->count('member_id');

        return view('ktvtc.library.reservations.index', compact(
            'reservations',
            'members',
            'books',
            'branches',
            'activeReservations',
            'fulfilledToday',
            'expiringSoon',
            'uniqueMembers'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'branch_id' => 'required|exists:branches,id',
            'reservation_date' => 'required|date',
            'expiry_date' => 'required|date|after:reservation_date',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,fulfilled,cancelled,expired'
        ]);

        // Calculate queue position
        $queuePosition = Reservation::where('book_id', $validated['book_id'])
            ->where('branch_id', $validated['branch_id'])
            ->where('status', 'active')
            ->count() + 1;

        $reservation = Reservation::create([
            'member_id' => $validated['member_id'],
            'book_id' => $validated['book_id'],
            'branch_id' => $validated['branch_id'],
            'reservation_date' => $validated['reservation_date'],
            'expiry_date' => $validated['expiry_date'],
            'queue_position' => $queuePosition,
            'notes' => $validated['notes'],
            'status' => $validated['status'],
            'created_by' => Auth::id()
        ]);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'reservation_date' => 'required|date',
            'expiry_date' => 'required|date|after:reservation_date',
            'queue_position' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,fulfilled,cancelled,expired'
        ]);

        $reservation->update($validated);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation deleted successfully.');
    }

    /**
     * Fulfill a reservation
     */
    public function fulfill(Reservation $reservation)
    {
        $reservation->update([
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
            'fulfilled_by' => Auth::id()
        ]);

        // Update queue positions for remaining reservations
        $remainingReservations = Reservation::where('book_id', $reservation->book_id)
            ->where('branch_id', $reservation->branch_id)
            ->where('status', 'active')
            ->orderBy('queue_position')
            ->get();

        foreach ($remainingReservations as $index => $res) {
            $res->update(['queue_position' => $index + 1]);
        }

        return redirect()->back()->with('success', 'Reservation fulfilled successfully.');
    }

    /**
     * Cancel a reservation
     */
    public function cancel(Reservation $reservation)
    {
        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Reservation cancelled successfully.');
    }
}
