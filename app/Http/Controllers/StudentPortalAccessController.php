<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Services\Students\EnrollmentCreator;
use App\Services\Students\StudentAccountService;
use Illuminate\Http\Request;

/**
 * Admin: give students access to the portal (create the account if missing and
 * text the login details), one student or many at a time.
 */
class StudentPortalAccessController extends Controller
{
    /** Texts per click: each one is an SMS call, and a web request must finish in time. */
    public const BATCH = 50;

    public function __construct(private StudentAccountService $accounts)
    {
    }

    /** One student: create the account if missing and text the login details. */
    public function send(Request $request, Student $student)
    {
        $result = $this->accounts->sendLoginDetails($student, $request->boolean('reset'));

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Several students: the ticked ones, or every enrolled student who has no account
     * or has never signed in. Students who already sign in are skipped.
     */
    public function bulkSend(Request $request)
    {
        $request->validate([
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer',
            'scope' => 'nullable|in:never_signed_in',
        ]);

        $everyone = $request->input('scope') === 'never_signed_in';
        $students = $everyone
            // Next batch of those not texted in the last week, so each click moves on
            ? self::neverSignedIn(true)->orderBy('id')->limit(self::BATCH)->get()
            : Student::whereIn('id', $request->input('student_ids', []))->limit(self::BATCH * 4)->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students selected.');
        }

        $sent = $skipped = 0;
        $problems = [];
        foreach ($students as $student) {
            $result = $this->accounts->sendLoginDetails($student);
            if ($result['ok']) {
                $sent++;
            } else {
                $skipped++;
                if (count($problems) < 5) {
                    $problems[] = $result['message'];
                }
            }
        }

        $message = "Login details sent to {$sent} student(s)." . ($skipped ? " {$skipped} skipped: " . implode(' ', $problems) . ($skipped > 5 ? ' …' : '') : '');
        if ($everyone && ($left = self::neverSignedIn(true)->count()) > 0) {
            $message .= " {$left} still to go: click the button again to send the next " . min($left, self::BATCH) . '.';
        }

        return back()->with($sent ? 'success' : 'error', $message);
    }

    /**
     * Enrolled students (open enrollment, with a phone) who have no account or have never signed in.
     * $notTextedThisWeek leaves out those whose details were texted in the last 7 days.
     */
    public static function neverSignedIn(bool $notTextedThisWeek = false)
    {
        return Student::whereNull('merged_into_id')
            ->whereNotNull('phone')->where('phone', '!=', '')
            ->whereHas('enrollments', fn ($q) => $q->whereIn('status', EnrollmentCreator::OPEN_STATUSES))
            ->where(function ($q) use ($notTextedThisWeek) {
                $q->whereDoesntHave('user')
                  ->orWhereHas('user', fn ($u) => $u->whereNull('last_login_at')
                      ->when($notTextedThisWeek, fn ($w) => $w->where(fn ($t) => $t->whereNull('login_details_sent_at')
                          ->orWhere('login_details_sent_at', '<', now()->subDays(7)))));
            });
    }
}
