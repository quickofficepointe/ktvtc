<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Services\Payments\FeeReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Finance: text fee balance reminders to students and their parents/guardians,
 * and confirm old balances so they can be reminded about.
 */
class FeeReminderController extends Controller
{
    public function __construct(private FeeReminderService $reminders)
    {
    }

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['due', 'remindable', 'unconfirmed']) ? $request->query('tab') : 'due';
        $query = match ($tab) {
            'remindable' => $this->reminders->remindable(),
            'unconfirmed' => $this->reminders->unconfirmed(),
            default => $this->reminders->dueForReminder(),
        };

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('enrollments.student_number', 'like', "%{$search}%")
                ->orWhere('enrollments.student_name', 'like', "%{$search}%")
                ->orWhere('enrollments.course_name', 'like', "%{$search}%"));
        }
        if ($request->filled('min')) {
            $query->whereRaw('enrollments.total_fees - enrollments.amount_paid >= ?', [(float) $request->query('min')]);
        }

        $enrollments = $query->with('student:id,first_name,last_name,phone,next_of_kin_phone,next_of_kin_name')
            ->orderByRaw('enrollments.total_fees - enrollments.amount_paid DESC')
            ->paginate(25)->withQueryString();

        $last = DB::table('fee_reminders')->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->selectRaw('enrollment_id, MAX(created_at) as at, COUNT(*) as n')->groupBy('enrollment_id')->get()->keyBy('enrollment_id');

        $sum = fn ($q) => (float) (clone $q)->sum(DB::raw('enrollments.total_fees - enrollments.amount_paid'));

        return view('ktvtc.finance.fee-reminders.index', [
            'tab' => $tab,
            'enrollments' => $enrollments,
            'last' => $last,
            'counts' => [
                'due' => $this->reminders->dueForReminder()->count(),
                'remindable' => $this->reminders->remindable()->count(),
                'unconfirmed' => $this->reminders->unconfirmed()->count(),
            ],
            'amounts' => [
                'due' => $sum($this->reminders->dueForReminder()),
                'unconfirmed' => $sum($this->reminders->unconfirmed()),
            ],
            'sentThisWeek' => DB::table('fee_reminders')->where('created_at', '>=', now()->subDays(7))->count(),
            'batch' => FeeReminderService::BATCH,
            'sample' => ($first = $this->reminders->remindable()->with('student')->first()) ? $this->reminders->message($first) : null,
        ]);
    }

    /** One student, any time (finance's own judgement). */
    public function send(Request $request, Enrollment $enrollment)
    {
        $result = $this->reminders->send($enrollment, $request->user()->id, true);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /** The ticked students, or the next batch of those not reminded this week. */
    public function bulkSend(Request $request)
    {
        $request->validate(['enrollment_ids' => 'nullable|array', 'enrollment_ids.*' => 'integer', 'scope' => 'nullable|in:due']);

        $everyone = $request->input('scope') === 'due';
        $enrollments = $everyone
            ? $this->reminders->dueForReminder()->with('student')->orderByRaw('enrollments.total_fees - enrollments.amount_paid DESC')->limit(FeeReminderService::BATCH)->get()
            : Enrollment::with('student')->whereIn('id', $request->input('enrollment_ids', []))->limit(FeeReminderService::BATCH * 4)->get();

        if ($enrollments->isEmpty()) {
            return back()->with('error', 'Nobody to remind.');
        }

        $sent = $skipped = 0;
        $why = [];
        foreach ($enrollments as $e) {
            $result = $this->reminders->send($e, $request->user()->id);
            if ($result['ok']) {
                $sent++;
            } else {
                $skipped++;
                if (count($why) < 4) {
                    $why[] = $result['message'];
                }
            }
        }

        $message = "Reminders sent to {$sent} student(s) and their parents.";
        if ($skipped) {
            $message .= " {$skipped} skipped: " . implode(' ', array_unique($why)) . ($skipped > count($why) ? ' …' : '');
        }
        if ($everyone && ($left = $this->reminders->dueForReminder()->count()) > 0) {
            $message .= " {$left} still to remind: click again for the next " . min($left, FeeReminderService::BATCH) . '.';
        }

        return back()->with($sent ? 'success' : 'error', $message);
    }

    /** Accounts has checked these old balances against receipts. */
    public function confirm(Request $request)
    {
        $request->validate(['enrollment_ids' => 'required|array', 'enrollment_ids.*' => 'integer']);

        $enrollments = Enrollment::whereIn('id', $request->input('enrollment_ids'))->whereNull('balance_confirmed_at')->get();
        foreach ($enrollments as $e) {
            $this->reminders->confirmBalance($e, $request->user()->id);
        }

        return back()->with('success', "{$enrollments->count()} balance(s) confirmed. They can now be reminded about.");
    }
}
