<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Member;
use App\Models\Book;
use App\Models\Branch;
use App\Services\Library\LendingException;
use App\Services\Library\LendingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['member', 'book', 'branch', 'heldItem'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $reservations = $query->paginate(20);
        $members = Member::active()->get();
        $books = Book::orderBy('title')->get();
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
     * Put a member on the waiting list for a book that is out.
     */
    public function store(Request $request, LendingService $lending)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $reservation = $lending->reserve(Member::findOrFail($validated['member_id']), (int) $validated['book_id'], Auth::id(), $validated['notes'] ?? null);
        } catch (LendingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('library.reservations.index')
            ->with('success', "Added to the waiting list at position {$reservation->queue_position}.");
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

        return redirect()->route('library.reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('library.reservations.index')
            ->with('success', 'Reservation deleted successfully.');
    }

    /**
     * The reader came for the copy held for them: lend it.
     */
    public function fulfill(Reservation $reservation, LendingService $lending)
    {
        if ($reservation->status !== 'active' || !$reservation->heldItem) {
            return back()->with('error', 'No copy is being held for this reservation yet; it is held automatically when one is returned.');
        }

        try {
            $loan = $lending->issue($reservation->member, $reservation->heldItem, null, Auth::id(), 'Collected from the waiting list');
        } catch (LendingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Lent copy {$reservation->heldItem->barcode}, due {$loan->due_date->format('d M Y')}.");
    }

    /**
     * Cancel a reservation; a held copy passes to the next reader.
     */
    public function cancel(Reservation $reservation, LendingService $lending)
    {
        $lending->cancelReservation($reservation);

        return back()->with('success', 'Reservation cancelled.');
    }
}
