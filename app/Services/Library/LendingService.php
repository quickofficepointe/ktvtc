<?php

namespace App\Services\Library;

use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\FineRule;
use App\Models\Item;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Library lending rules, used by the librarian screens and the student portal.
 *
 * - Every student with an active enrollment is a member (member number = student number).
 * - A loan needs: an active membership, no unpaid fines, room under the member's limit,
 *   and a copy on the shelf (or held for this member's reservation).
 * - On return the fine comes from the active fine rules and is added to the member's
 *   balance; the copy is held for the next reader on the waiting list, if any.
 */
class LendingService
{
    public const DEFAULT_LOAN_DAYS = 14;
    public const STUDENT_BORROW_LIMIT = 3;
    public const HOLD_DAYS = 3;

    /** The student's library membership, created or refreshed from their enrollments. */
    public function memberForStudent(Student $student): Member
    {
        $active = $this->activeEnrollments($student);
        $endsOn = $active->max(fn ($e) => $e->expected_end_date) ?? now()->addYear();

        $member = Member::firstOrNew(['student_id' => $student->id]);
        if (!$member->exists) {
            $member->fill([
                'member_id' => $this->freeMemberNumber($student->student_number ?: 'STU-' . $student->id),
                'membership_type' => 'student',
                'max_borrow_limit' => self::STUDENT_BORROW_LIMIT,
                'membership_start_date' => now()->toDateString(),
                'branch_id' => $this->defaultBranchId(),
                'outstanding_fines' => 0,
            ]);
        }

        $member->fill([
            'first_name' => $student->first_name,
            'last_name' => trim(($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
            'email' => $member->email ?: $this->freeEmail($student->email),
            'phone' => $student->phone,
            'membership_end_date' => Carbon::parse($endsOn)->toDateString(),
            'is_active' => $active->isNotEmpty(),
        ]);
        $member->save();

        return $member;
    }

    /** Reasons this member cannot take another book now (empty = they can). */
    public function borrowBlockers(Member $member): array
    {
        $reasons = [];

        if ($member->student_id) {
            $student = $member->student;
            if (!$student || $this->activeEnrollments($student)->isEmpty()) {
                $reasons[] = 'The student has no active enrollment.';
            }
        } elseif (!$member->is_active || ($member->membership_end_date && $member->membership_end_date->lt(today()))) {
            $reasons[] = 'Membership is inactive or expired' . ($member->membership_end_date ? ' (ended ' . $member->membership_end_date->format('d M Y') . ')' : '') . '.';
        }

        if ((float) $member->outstanding_fines > 0) {
            $reasons[] = 'Unpaid fines of KES ' . number_format((float) $member->outstanding_fines, 2) . ' must be paid first.';
        }

        $onLoan = $member->transactions()->whereIn('status', ['borrowed', 'overdue'])->count();
        if ($onLoan >= (int) $member->max_borrow_limit) {
            $reasons[] = "Already has {$onLoan} of {$member->max_borrow_limit} books allowed.";
        }

        return $reasons;
    }

    /** Lend one copy. */
    public function issue(Member $member, Item $item, ?string $dueDate, ?int $userId, ?string $notes = null): Transaction
    {
        return DB::transaction(function () use ($member, $item, $dueDate, $userId, $notes) {
            $member = Member::lockForUpdate()->findOrFail($member->id);
            $item = Item::lockForUpdate()->findOrFail($item->id);

            if ($member->student) {
                $member = $this->memberForStudent($member->student);
            }
            if ($reasons = $this->borrowBlockers($member)) {
                throw new LendingException(implode(' ', $reasons));
            }

            $hold = Reservation::where('held_item_id', $item->id)->where('status', 'active')->first();
            if ($item->status === 'reserved') {
                if (!$hold || $hold->member_id !== $member->id) {
                    throw new LendingException("Copy {$item->barcode} is being held for another reader on the waiting list.");
                }
            } elseif ($item->status !== 'available') {
                throw new LendingException("Copy {$item->barcode} is not on the shelf (status: {$item->status}).");
            }

            $due = $dueDate ? Carbon::parse($dueDate) : now()->addDays(self::DEFAULT_LOAN_DAYS);
            if ($due->lte(today())) {
                throw new LendingException('The due date must be after today.');
            }

            $loan = Transaction::create([
                'member_id' => $member->id,
                'item_id' => $item->id,
                'branch_id' => $item->branch_id,
                'borrow_date' => today(),
                'due_date' => $due->toDateString(),
                'status' => 'borrowed',
                'fine_amount' => 0,
                'notes' => $notes,
                'issued_by' => $userId,
            ]);

            $item->update(['status' => 'borrowed']);

            // Collecting a book they were waiting for closes their reservation
            Reservation::where('member_id', $member->id)->where('book_id', $item->book_id)->where('status', 'active')
                ->update(['status' => 'fulfilled', 'fulfilled_at' => now()]);
            $this->renumberQueue($item->book_id);
            $item->book?->syncCopyCounts();

            return $loan;
        });
    }

    /** Take a copy back, charge any fine and pass the copy to the waiting list. */
    public function returnLoan(Transaction $loan, ?int $userId, $returnedOn = null): Transaction
    {
        return DB::transaction(function () use ($loan, $userId, $returnedOn) {
            $loan = Transaction::lockForUpdate()->findOrFail($loan->id);
            if ($loan->status === 'returned' || $loan->return_date) {
                throw new LendingException('This book was already returned on ' . $loan->return_date?->format('d M Y') . '.');
            }

            $on = $returnedOn ? Carbon::parse($returnedOn) : now();
            $fine = $loan->calculateFine(FineRule::where('is_active', true)->get(), $on);

            $loan->update([
                'status' => 'returned',
                'return_date' => $on->toDateString(),
                'fine_amount' => $fine,
                'returned_by' => $userId,
            ]);

            if ($fine > 0) {
                Member::whereKey($loan->member_id)->increment('outstanding_fines', $fine);
            }

            $item = Item::lockForUpdate()->find($loan->item_id);
            if ($item) {
                $next = Reservation::where('book_id', $item->book_id)->where('status', 'active')->whereNull('held_item_id')
                    ->where('expiry_date', '>=', today())->orderBy('queue_position')->orderBy('id')->first();
                if ($next) {
                    $next->update(['held_item_id' => $item->id, 'held_until' => now()->addDays(self::HOLD_DAYS)]);
                    $item->update(['status' => 'reserved']);
                } else {
                    $item->update(['status' => 'available']);
                }
                $item->book?->syncCopyCounts();
            }

            return $loan->fresh();
        });
    }

    /** Put a member on the waiting list for a book. */
    public function reserve(Member $member, int $bookId, ?int $userId, ?string $notes = null): Reservation
    {
        return DB::transaction(function () use ($member, $bookId, $userId, $notes) {
            if ($member->student) {
                $member = $this->memberForStudent($member->student);
            }
            if (!$member->student_id && !$member->is_active) {
                throw new LendingException('Membership is inactive.');
            }
            if ($member->student_id && $this->activeEnrollments($member->student)->isEmpty()) {
                throw new LendingException('The student has no active enrollment.');
            }
            if (Reservation::where('member_id', $member->id)->where('book_id', $bookId)->where('status', 'active')->exists()) {
                throw new LendingException('Already on the waiting list for this book.');
            }
            if (Transaction::where('member_id', $member->id)->whereIn('status', ['borrowed', 'overdue'])
                ->whereHas('item', fn ($q) => $q->where('book_id', $bookId))->exists()) {
                throw new LendingException('They already have this book on loan.');
            }
            if (Item::where('book_id', $bookId)->where('status', 'available')->exists()) {
                throw new LendingException('A copy is on the shelf; borrow it at the library desk instead.');
            }

            $position = Reservation::where('book_id', $bookId)->where('status', 'active')->lockForUpdate()->count() + 1;

            return Reservation::create([
                'member_id' => $member->id,
                'book_id' => $bookId,
                'branch_id' => $member->branch_id,
                'reservation_date' => today(),
                'expiry_date' => today()->addDays(30),
                'status' => 'active',
                'queue_position' => $position,
                'notes' => $notes,
                'created_by' => $userId,
            ]);
        });
    }

    /** Cancel a reservation; a held copy goes to the next reader or back on the shelf. */
    public function cancelReservation(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $reservation = Reservation::lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'active') {
                return;
            }
            $heldItem = $reservation->held_item_id ? Item::lockForUpdate()->find($reservation->held_item_id) : null;
            $reservation->update(['status' => 'cancelled', 'cancelled_at' => now(), 'held_item_id' => null, 'held_until' => null]);
            $this->renumberQueue($reservation->book_id);

            if ($heldItem && $heldItem->status === 'reserved') {
                $next = Reservation::where('book_id', $heldItem->book_id)->where('status', 'active')->whereNull('held_item_id')
                    ->orderBy('queue_position')->first();
                if ($next) {
                    $next->update(['held_item_id' => $heldItem->id, 'held_until' => now()->addDays(self::HOLD_DAYS)]);
                } else {
                    $heldItem->update(['status' => 'available']);
                }
                $heldItem->book?->syncCopyCounts();
            }
        });
    }

    /** Record money paid against a member's fines. */
    public function payFine(Member $member, float $amount, string $method, ?string $reference, ?string $receivedBy): Payment
    {
        return DB::transaction(function () use ($member, $amount, $method, $reference, $receivedBy) {
            $member = Member::lockForUpdate()->findOrFail($member->id);
            $owed = round((float) $member->outstanding_fines, 2);
            if ($amount <= 0 || round($amount, 2) > $owed) {
                throw new LendingException('Amount must be between 1 and the KES ' . number_format($owed, 2) . ' owed.');
            }

            $payment = Payment::create([
                'member_id' => $member->id,
                'payment_method' => $method,
                'amount' => round($amount, 2),
                'payment_type' => 'fine',
                'reference_number' => $reference ?: 'LIB-' . now()->format('ymdHis') . '-' . $member->id,
                'payment_date' => today(),
                'received_by' => $receivedBy,
            ]);
            $member->decrement('outstanding_fines', round($amount, 2));

            return $payment;
        });
    }

    private function activeEnrollments(Student $student)
    {
        return Enrollment::where('student_id', $student->id)->where('status', 'active')->get();
    }

    private function renumberQueue(int $bookId): void
    {
        Reservation::where('book_id', $bookId)->where('status', 'active')->orderBy('queue_position')->orderBy('id')->get()
            ->each(fn ($r, $i) => $r->queue_position !== $i + 1 ? $r->update(['queue_position' => $i + 1]) : null);
    }

    private function defaultBranchId(): int
    {
        $id = Branch::where('is_active', true)->orderBy('id')->value('id') ?? Branch::orderBy('id')->value('id');
        if (!$id) {
            throw new LendingException('The library has no branch set up yet; ask the librarian to add one.');
        }

        return $id;
    }

    private function freeMemberNumber(string $wanted): string
    {
        $number = $wanted;
        for ($i = 2; Member::where('member_id', $number)->exists(); $i++) {
            $number = "{$wanted}-{$i}";
        }
        return $number;
    }

    private function freeEmail(?string $email): ?string
    {
        return $email && !Member::where('email', $email)->exists() ? $email : null;
    }
}
