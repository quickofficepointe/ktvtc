<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Reservation;
use App\Models\Student;
use App\Services\Library\LendingException;
use App\Services\Library\LendingService;
use Illuminate\Http\Request;

/**
 * "My library" in the student portal: books on loan, fines, the waiting list,
 * and a catalogue search to join the waiting list for a book that is out.
 */
class StudentLibraryController extends Controller
{
    public function __construct(private LendingService $lending)
    {
    }

    public function index(Request $request)
    {
        $member = $this->lending->memberForStudent($this->student());
        $member->load([
            'transactions' => fn ($q) => $q->with('item.book')->orderByDesc('borrow_date')->orderByDesc('id'),
            'reservations' => fn ($q) => $q->with(['book', 'heldItem'])->where('status', 'active')->orderBy('queue_position'),
        ]);

        $onLoan = $member->transactions->whereIn('status', ['borrowed', 'overdue']);
        $history = $member->transactions->where('status', 'returned')->take(20);
        $blockers = $this->lending->borrowBlockers($member);

        $books = collect();
        if ($search = trim((string) $request->query('q'))) {
            $books = Book::where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('isbn', 'like', "%{$search}%"))
                ->withCount(['items as shelf_count' => fn ($q) => $q->where('status', 'available')])
                ->orderBy('title')->limit(20)->get();
        }

        return view('ktvtc.students.library.index', compact('member', 'onLoan', 'history', 'blockers', 'books', 'search'));
    }

    public function reserve(Book $book)
    {
        try {
            $reservation = $this->lending->reserve($this->lending->memberForStudent($this->student()), $book->id, auth()->id());
        } catch (LendingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "You are number {$reservation->queue_position} on the waiting list for \"{$book->title}\". We hold a copy for you for "
            . LendingService::HOLD_DAYS . ' days when one comes back.');
    }

    public function cancel(Reservation $reservation)
    {
        $member = $this->lending->memberForStudent($this->student());
        abort_unless($reservation->member_id === $member->id, 403);

        $this->lending->cancelReservation($reservation);

        return back()->with('success', 'Reservation cancelled.');
    }

    private function student(): Student
    {
        $student = Student::find(auth()->user()->student_id);
        abort_unless($student, 404, 'No student record linked to this account.');

        return $student;
    }
}
