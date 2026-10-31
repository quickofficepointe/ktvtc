<?php

namespace App\Services\Dashboards;

use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\User;
use App\Services\Library\LendingService;
use App\Services\Students\EnrollmentCreator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Figures for every role's dashboard (finance has its own, FinanceDashboard).
 * Each method returns plain arrays/collections the standard dashboard components render.
 */
class RoleDashboards
{
    /** Counts per month for the last $months months: ['labels' => [...], 'data' => [...]]. */
    public function monthly($query, string $dateColumn, int $months = 12, ?string $sumColumn = null): array
    {
        $start = now()->subMonths($months - 1)->startOfMonth();
        $agg = $sumColumn ? "SUM({$sumColumn})" : 'COUNT(*)';
        $rows = (clone $query)->where($dateColumn, '>=', $start)
            ->selectRaw("DATE_FORMAT({$dateColumn}, '%Y-%m') as ym, {$agg} as v")
            ->groupBy('ym')->pluck('v', 'ym');

        $labels = $data = [];
        for ($m = $start->copy(); $m <= now(); $m->addMonth()) {
            $labels[] = $m->format('M y');
            $data[] = (float) ($rows[$m->format('Y-m')] ?? 0);
        }

        return compact('labels', 'data');
    }

    public function admin(?int $campusId): array
    {
        $enrollments = Enrollment::query()->when($campusId, fn ($q) => $q->where('campus_id', $campusId));
        $open = (clone $enrollments)->whereIn('status', EnrollmentCreator::OPEN_STATUSES);
        $students = Student::whereNull('merged_into_id')->when($campusId, fn ($q) => $q->where('campus_id', $campusId));

        return [
            'activeStudents' => (clone $students)->where('status', 'active')->count(),
            'allStudents' => (clone $students)->count(),
            'openEnrollments' => (clone $open)->count(),
            'newThisMonth' => (clone $enrollments)->where('created_at', '>=', now()->startOfMonth())->count(),
            'pendingApplications' => DB::table('applications')->where('status', 'pending')->count(),
            'acceptedNotAdmitted' => Schema::hasColumn('applications', 'enrollment_id')
                ? DB::table('applications')->where('status', 'accepted')->whereNull('enrollment_id')->count() : 0,
            'enrollTrend' => $this->monthly(Enrollment::query()->when($campusId, fn ($q) => $q->where('campus_id', $campusId)), 'created_at'),
            'applyTrend' => $this->monthly(DB::table('applications'), 'created_at'),
            'byCourse' => (clone $open)->selectRaw('course_name as label, COUNT(*) as value')->groupBy('course_name')
                ->orderByDesc('value')->limit(8)->get()->map(fn ($r) => ['label' => $r->label ?: 'Unknown', 'value' => (int) $r->value])->all(),
            'byCampus' => (clone $open)->leftJoin('campuses', 'campuses.id', '=', 'enrollments.campus_id')
                ->selectRaw("COALESCE(campuses.name, 'No campus set') as label, COUNT(*) as value")->groupBy('label')
                ->orderByDesc('value')->get()->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])->all(),
            'resultsToPublish' => Schema::hasTable('unit_results') ? DB::table('unit_results')->where('status', 'submitted')->count() : 0,
            'unitsWithoutTrainer' => Schema::hasTable('course_units') ? DB::table('course_units')->where('is_active', true)->whereNull('trainer_id')->count() : 0,
            'termsRunning' => Schema::hasTable('term_registrations') ? DB::table('term_registrations')->where('status', '!=', 'cancelled')
                ->whereDate('starts_on', '<=', today())->whereDate('ends_on', '>=', today())->count() : 0,
            'withoutEnrollment' => (clone $students)->whereDoesntHave('enrollments')->count(),
            'neverSignedIn' => \App\Http\Controllers\StudentPortalAccessController::neverSignedIn()
                ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))->count(),
            'recentApplications' => \App\Models\Application::with('course')->latest()->limit(6)->get(),
            'recentEnrollments' => (clone $enrollments)->with('student')->latest()->limit(6)->get(),
        ];
    }

    public function superAdmin(): array
    {
        $logins = DB::table('system_logs')->where('action', 'login')->where('created_at', '>=', today()->subDays(13))
            ->selectRaw('DATE(created_at) as d, COUNT(*) as n')->groupBy('d')->pluck('n', 'd');
        $loginTrend = ['labels' => [], 'data' => []];
        for ($d = today()->subDays(13); $d <= today(); $d->addDay()) {
            $loginTrend['labels'][] = $d->format('d M');
            $loginTrend['data'][] = (int) ($logins[$d->toDateString()] ?? 0);
        }

        return [
            'users' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'awaiting' => User::where('is_approved', false)->count(),
            'loginsToday' => DB::table('system_logs')->where('action', 'login')->whereDate('created_at', today())->count(),
            'errorsToday' => DB::table('system_logs')->whereIn('level', ['error', 'critical'])->whereDate('created_at', today())->count(),
            'loginTrend' => $loginTrend,
            'byRole' => User::select('role', DB::raw('COUNT(*) as n'))->groupBy('role')->get()
                ->map(fn ($r) => ['label' => User::getRoles()[$r->role] ?? "Role {$r->role}", 'value' => (int) $r->n])->sortByDesc('value')->values()->all(),
            'recent' => DB::table('system_logs')->leftJoin('users', 'users.id', '=', 'system_logs.user_id')
                ->select('system_logs.*', 'users.name as user_name')->orderByDesc('system_logs.id')->limit(10)->get(),
            'awaitingUsers' => User::where('is_approved', false)->latest()->limit(6)->get(),
        ];
    }

    public function library(): array
    {
        $onLoan = DB::table('transactions')->whereIn('status', ['borrowed', 'overdue']);
        $loans = ['labels' => [], 'issued' => [], 'returned' => []];
        $issued = DB::table('transactions')->where('borrow_date', '>=', today()->subDays(29))->selectRaw('borrow_date as d, COUNT(*) as n')->groupBy('d')->pluck('n', 'd');
        $returned = DB::table('transactions')->where('return_date', '>=', today()->subDays(29))->selectRaw('return_date as d, COUNT(*) as n')->groupBy('d')->pluck('n', 'd');
        for ($d = today()->subDays(29); $d <= today(); $d->addDay()) {
            $loans['labels'][] = $d->format('d M');
            $loans['issued'][] = (int) ($issued[$d->toDateString()] ?? 0);
            $loans['returned'][] = (int) ($returned[$d->toDateString()] ?? 0);
        }

        return [
            'titles' => DB::table('books')->count(),
            'copies' => DB::table('items')->whereNotIn('status', ['lost'])->count(),
            'onShelf' => DB::table('items')->where('status', 'available')->count(),
            'missing' => DB::table('items')->where('status', 'missing')->count(),
            'onLoan' => (clone $onLoan)->count(),
            'overdue' => (clone $onLoan)->whereDate('due_date', '<', today())->count(),
            'fines' => (float) DB::table('members')->sum('outstanding_fines'),
            'finesPaidMonth' => (float) DB::table('payments')->where('payment_type', 'fine')->where('payment_date', '>=', now()->startOfMonth())->sum('amount'),
            'members' => DB::table('members')->where('is_active', true)->count(),
            'readyForPickup' => DB::table('reservations')->where('status', 'active')->whereNotNull('held_item_id')->count(),
            'waiting' => DB::table('reservations')->where('status', 'active')->whereNull('held_item_id')->count(),
            'loans' => $loans,
            'overdueList' => \App\Models\Transaction::with(['member', 'item.book'])->whereIn('status', ['borrowed', 'overdue'])
                ->whereDate('due_date', '<', today())->orderBy('due_date')->limit(8)->get(),
            'popular' => DB::table('transactions')->join('items', 'items.id', '=', 'transactions.item_id')->join('books', 'books.id', '=', 'items.book_id')
                ->where('transactions.borrow_date', '>=', today()->subDays(90))
                ->selectRaw('books.title as label, COUNT(*) as value')->groupBy('books.title')->orderByDesc('value')->limit(6)->get()
                ->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])->all(),
        ];
    }

    public function cafeteria(): array
    {
        $paid = DB::table('sales')->where('payment_status', 'paid')->whereNull('deleted_at');
        $date = DB::raw('COALESCE(sale_date, created_at)');
        $daily = ['labels' => [], 'pos' => [], 'card' => []];
        $pos = (clone $paid)->where($date, '>=', today()->subDays(29))->selectRaw('DATE(COALESCE(sale_date, created_at)) as d, SUM(total_amount) as v')->groupBy('d')->pluck('v', 'd');
        $card = DB::table('card_transactions')->where('transaction_type', 'purchase')->where('status', 'completed')->where('created_at', '>=', today()->subDays(29))
            ->selectRaw('DATE(created_at) as d, SUM(amount) as v')->groupBy('d')->pluck('v', 'd');
        for ($d = today()->subDays(29); $d <= today(); $d->addDay()) {
            $daily['labels'][] = $d->format('d M');
            $daily['pos'][] = (float) ($pos[$d->toDateString()] ?? 0);
            $daily['card'][] = (float) ($card[$d->toDateString()] ?? 0);
        }
        $cardToday = (float) DB::table('card_transactions')->where('transaction_type', 'purchase')->where('status', 'completed')->whereDate('created_at', today())->sum('amount');
        $low = DB::table('products')->where('is_active', true)->where('track_inventory', true)
            ->whereRaw('current_stock <= GREATEST(COALESCE(reorder_level, 0), COALESCE(min_stock_level, 0))');

        return [
            'todayPos' => (float) (clone $paid)->whereDate($date, today())->sum('total_amount'),
            'todayCard' => $cardToday,
            'todayCount' => (clone $paid)->whereDate($date, today())->count()
                + DB::table('card_transactions')->where('transaction_type', 'purchase')->where('status', 'completed')->whereDate('created_at', today())->count(),
            'month' => (float) (clone $paid)->where($date, '>=', now()->startOfMonth())->sum('total_amount')
                + (float) DB::table('card_transactions')->where('transaction_type', 'purchase')->where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'unpaidSales' => DB::table('sales')->where('payment_status', 'pending')->whereNull('deleted_at')->whereDate('created_at', '>=', today()->subDays(7))->count(),
            'preOrdersOpen' => Schema::hasTable('pre_orders') ? DB::table('pre_orders')->whereIn('status', ['paid', 'accepted', 'ready'])->count() : 0,
            'lowStockCount' => (clone $low)->count(),
            'lowStock' => (clone $low)->orderBy('current_stock')->limit(8)->get(['product_name', 'current_stock', 'reorder_level', 'min_stock_level', 'unit']),
            'daily' => $daily,
            'topProducts' => DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sales.payment_status', 'paid')->where('sales.created_at', '>=', now()->startOfMonth())
                ->selectRaw('sale_items.product_name as label, SUM(sale_items.final_price) as value, SUM(sale_items.quantity) as qty')
                ->groupBy('sale_items.product_name')->orderByDesc('value')->limit(8)->get()
                ->map(fn ($r) => ['label' => $r->label, 'value' => (float) $r->value, 'note' => rtrim(rtrim(number_format((float) $r->qty, 2), '0'), '.') . ' sold'])->all(),
        ];
    }

    public function mschool(): array
    {
        $open = DB::table('m_enrollments')->whereNull('deleted_at')->whereIn('status', ['active', 'pending']);
        $monthAttendance = DB::table('m_attendances')->whereNull('deleted_at')->where('attendance_date', '>=', now()->startOfMonth());
        $expected = (int) (clone $monthAttendance)->sum('total_expected');
        $present = (int) (clone $monthAttendance)->sum(DB::raw('total_present + total_late'));

        return [
            'students' => DB::table('m_students')->whereNull('deleted_at')->count(),
            'active' => (clone $open)->count(),
            'completed' => DB::table('m_enrollments')->whereNull('deleted_at')->where('status', 'completed')->count(),
            'certificates' => DB::table('m_certificates')->whereNull('deleted_at')->count(),
            'sessionsMonth' => (clone $monthAttendance)->count(),
            'attendanceRate' => $expected > 0 ? round($present / $expected * 100) : null,
            'schools' => DB::table('mobile_schools')->whereNull('deleted_at')->where('is_active', true)->count(),
            'enrollTrend' => $this->monthly(DB::table('m_enrollments')->whereNull('deleted_at'), 'created_at'),
            'bySchool' => DB::table('m_enrollments')->join('mobile_schools', 'mobile_schools.id', '=', 'm_enrollments.mobile_school_id')
                ->whereNull('m_enrollments.deleted_at')->selectRaw('mobile_schools.name as label, COUNT(*) as value')
                ->groupBy('mobile_schools.name')->orderByDesc('value')->limit(8)->get()->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])->all(),
            'byCourse' => DB::table('m_enrollments')->join('m_courses', 'm_courses.course_id', '=', 'm_enrollments.course_id')
                ->whereNull('m_enrollments.deleted_at')->selectRaw('m_courses.course_name as label, COUNT(*) as value')
                ->groupBy('m_courses.course_name')->orderByDesc('value')->limit(8)->get()->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])->all(),
            'recentSessions' => DB::table('m_attendances')->leftJoin('m_courses', 'm_courses.course_id', '=', 'm_attendances.course_id')
                ->whereNull('m_attendances.deleted_at')->orderByDesc('attendance_date')->orderByDesc('attendance_id')->limit(6)
                ->get(['m_attendances.*', 'm_courses.course_name']),
        ];
    }

    public function student(Student $student): array
    {
        $enrollments = Enrollment::with('course')->where('student_id', $student->id)->orderByDesc('created_at')->get();
        $open = $enrollments->whereIn('status', EnrollmentCreator::OPEN_STATUSES);
        $ledger = app(\App\Services\Terms\TermLedger::class);
        $gate = app(\App\Services\Terms\ExamCardGate::class);

        $courses = $open->map(function ($e) use ($ledger, $gate) {
            $terms = $ledger->forEnrollment($e);
            $current = collect($terms)->first(fn ($t) => $t['registration'] && today()->between($t['registration']->starts_on, $t['registration']->ends_on))
                ?? collect($terms)->last();
            $card = $current && $current['registration'] ? $gate->check($current['registration']) : null;

            return [
                'enrollment' => $e,
                'balance' => max(0, (float) $e->total_fees - (float) $e->amount_paid),
                'term' => $current,
                'examCard' => $card,
                'next' => rescue(fn () => app(\App\Services\Terms\TermRegistrationService::class)->nextTermStatus($e), null, false),
            ];
        })->values();

        $results = Schema::hasTable('unit_results') ? DB::table('unit_results')->join('course_units', 'course_units.id', '=', 'unit_results.course_unit_id')
            ->where('unit_results.student_id', $student->id)->where('unit_results.status', 'published')
            ->orderByDesc('unit_results.published_at')->limit(5)->get(['course_units.title', 'unit_results.total', 'unit_results.grade', 'unit_results.passed']) : collect();

        $member = rescue(fn () => app(LendingService::class)->memberForStudent($student), null, false);

        return [
            'student' => $student,
            'courses' => $courses,
            'balance' => $courses->sum('balance'),
            'paidTotal' => (float) FeePayment::where('student_id', $student->id)->where('status', 'completed')->sum('amount'),
            'payments' => FeePayment::where('student_id', $student->id)->where('status', 'completed')->orderByDesc('payment_date')->limit(5)->get(),
            'results' => $results,
            'publishedCount' => Schema::hasTable('unit_results') ? DB::table('unit_results')->where('student_id', $student->id)->where('status', 'published')->count() : 0,
            'onLoan' => $member ? $member->transactions()->whereIn('status', ['borrowed', 'overdue'])->with('item.book')->get() : collect(),
            'fines' => (float) ($member->outstanding_fines ?? 0),
        ];
    }

    public function trainer(User $trainer): array
    {
        $units = \App\Models\CourseUnit::with('course')->where('trainer_id', $trainer->id)->where('is_active', true)->orderBy('course_id')->orderBy('term_number')->get();
        $results = DB::table('unit_results')->whereIn('course_unit_id', $units->pluck('id'));
        // Students registered (now or in the last month) for a term this trainer teaches
        $students = DB::table('term_registrations')->where('status', '!=', 'cancelled')
            ->whereDate('ends_on', '>=', today()->subDays(30))
            ->where(function ($q) use ($units) {
                $q->whereRaw('1 = 0');
                foreach ($units as $u) {
                    $q->orWhere(fn ($w) => $w->where('course_id', $u->course_id)->where('term_number', $u->term_number));
                }
            });

        $ratings = DB::table('trainer_evaluation_answers')->join('trainer_evaluations', 'trainer_evaluations.id', '=', 'trainer_evaluation_answers.trainer_evaluation_id')
            ->where('trainer_evaluations.trainer_id', $trainer->id)->whereNotNull('trainer_evaluation_answers.rating');
        $responses = DB::table('trainer_evaluations')->where('trainer_id', $trainer->id)->count();

        return [
            'units' => $units->map(function ($u) {
                $counts = DB::table('unit_results')->where('course_unit_id', $u->id)->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');
                $u->draft = (int) ($counts['draft'] ?? 0);
                $u->submitted = (int) ($counts['submitted'] ?? 0);
                $u->published = (int) ($counts['published'] ?? 0);
                return $u;
            }),
            'students' => (clone $students)->distinct('student_id')->count('student_id'),
            'drafts' => (clone $results)->where('status', 'draft')->count(),
            'submitted' => (clone $results)->where('status', 'submitted')->count(),
            'published' => (clone $results)->where('status', 'published')->count(),
            'responses' => $responses,
            'rating' => $responses >= \App\Services\Academics\EvaluationService::MIN_RESPONSES ? round((float) (clone $ratings)->avg('trainer_evaluation_answers.rating'), 1) : null,
        ];
    }

    public function website(): array
    {
        return [
            'applicationsMonth' => DB::table('applications')->where('created_at', '>=', now()->startOfMonth())->count(),
            'pending' => DB::table('applications')->where('status', 'pending')->count(),
            'blogs' => DB::table('blogs')->whereNull('deleted_at')->where('is_published', true)->count(),
            'upcomingEvents' => DB::table('events')->whereNull('deleted_at')->where('is_published', true)->where('event_start_date', '>=', today())->count(),
            'unreadMessages' => Schema::hasTable('messages') ? DB::table('messages')->whereNull('first_seen_by')->count() : 0,
            'applyTrend' => $this->monthly(DB::table('applications'), 'created_at'),
            'byCourse' => DB::table('applications')->leftJoin('courses', 'courses.id', '=', 'applications.course_id')
                ->where('applications.created_at', '>=', now()->subMonths(6))->selectRaw("COALESCE(courses.name, 'Unknown') as label, COUNT(*) as value")
                ->groupBy('label')->orderByDesc('value')->limit(8)->get()->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])->all(),
            'recentApplications' => \App\Models\Application::with('course')->latest()->limit(6)->get(),
            'recentMessages' => Schema::hasTable('messages') ? DB::table('messages')->orderByDesc('id')->limit(5)->get() : collect(),
            'events' => DB::table('events')->whereNull('deleted_at')->where('event_start_date', '>=', today())->orderBy('event_start_date')->limit(4)->get(),
        ];
    }

    /** Academics office: trainers, units, marks in progress, results to publish, exams. */
    public function academics(): array
    {
        $results = DB::table('unit_results');
        $published = (clone $results)->where('status', 'published');
        $publishedCount = (clone $published)->count();

        // Marks expected now: each student in a running term x the active units of that course term
        $expected = (int) DB::table('term_registrations')
            ->join('course_units', fn ($j) => $j->on('course_units.course_id', '=', 'term_registrations.course_id')
                ->on('course_units.term_number', '=', 'term_registrations.term_number'))
            ->where('course_units.is_active', true)->where('term_registrations.status', '!=', 'cancelled')
            ->whereDate('term_registrations.starts_on', '<=', today())->whereDate('term_registrations.ends_on', '>=', today())
            ->count();
        $entered = (int) DB::table('unit_results')->join('term_registrations', 'term_registrations.id', '=', 'unit_results.term_registration_id')
            ->where('term_registrations.status', '!=', 'cancelled')
            ->whereDate('term_registrations.starts_on', '<=', today())->whereDate('term_registrations.ends_on', '>=', today())
            ->whereNotNull('unit_results.total')->count();

        return [
            'trainers' => DB::table('users')->where('role', User::ROLE_TRAINERS)->whereNull('deleted_at')->where(fn ($q) => $q->whereNull('is_active')->orWhere('is_active', true))->count(),
            'units' => DB::table('course_units')->where('is_active', true)->count(),
            'unitsWithoutTrainer' => DB::table('course_units')->where('is_active', true)->whereNull('trainer_id')->count(),
            'coursesWithoutUnits' => DB::table('courses')->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('course_units')->whereColumn('course_units.course_id', 'courses.id'))
                ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('enrollments')->whereColumn('enrollments.course_id', 'courses.id')->where('enrollments.status', 'active'))->count(),
            'toPublish' => (clone $results)->where('status', 'submitted')->count(),
            'drafts' => (clone $results)->where('status', 'draft')->count(),
            'published' => $publishedCount,
            'passRate' => $publishedCount ? round((clone $published)->where('passed', true)->count() / $publishedCount * 100) : null,
            'expected' => $expected,
            'entered' => $entered,
            'byGrade' => (clone $published)->selectRaw("COALESCE(grade, 'No grade') as label, COUNT(*) as value")->groupBy('label')->orderByDesc('value')->get()
                ->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])->all(),
            'awaiting' => DB::table('unit_results')->join('course_units', 'course_units.id', '=', 'unit_results.course_unit_id')
                ->join('courses', 'courses.id', '=', 'course_units.course_id')->leftJoin('users', 'users.id', '=', 'course_units.trainer_id')
                ->where('unit_results.status', 'submitted')
                ->selectRaw('course_units.id, course_units.title, course_units.term_number, courses.name as course, users.name as trainer, COUNT(*) as n, MAX(unit_results.submitted_at) as submitted_at')
                ->groupBy('course_units.id', 'course_units.title', 'course_units.term_number', 'courses.name', 'users.name')
                ->orderByDesc('submitted_at')->limit(8)->get(),
            'workload' => DB::table('users')->where('users.role', User::ROLE_TRAINERS)->whereNull('users.deleted_at')
                ->leftJoin('course_units', fn ($j) => $j->on('course_units.trainer_id', '=', 'users.id')->where('course_units.is_active', true))
                ->selectRaw('users.name as label, COUNT(course_units.id) as value')->groupBy('users.id', 'users.name')
                ->orderByDesc('value')->limit(8)->get()->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value, 'note' => $r->value ? '' : 'No units yet'])->all(),
            'evaluations' => DB::table('trainer_evaluations')->where('created_at', '>=', now()->subMonths(3))->count(),
            'examPending' => DB::table('exam_registrations')->whereNull('deleted_at')->where('status', 'pending')->count(),
            'examAwaitingResults' => DB::table('exam_registrations')->whereNull('deleted_at')->where('status', 'registered')->count(),
        ];
    }

    public function ilo(): array
    {
        $attachments = DB::table('attachments')->whereNull('deleted_at');

        return [
            'companies' => DB::table('companies')->whereNull('deleted_at')->where('is_active', true)->count(),
            'openOpportunities' => DB::table('opportunities')->whereNull('deleted_at')->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('application_closes_at')->orWhere('application_closes_at', '>=', today()))->count(),
            'slotsLeft' => (int) DB::table('opportunities')->whereNull('deleted_at')->where('is_active', true)->sum(DB::raw('GREATEST(slots - slots_taken, 0)')),
            'applicationsPending' => DB::table('attachment_applications')->whereNull('deleted_at')->whereIn('status', ['pending', 'submitted'])->count(),
            'onAttachment' => (clone $attachments)->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->count(),
            'toReview' => (clone $attachments)->whereIn('status', ['submitted', 'pending_review'])->count(),
            'byStatus' => (clone $attachments)->selectRaw('status as label, COUNT(*) as value')->groupBy('status')->get()
                ->map(fn ($r) => ['label' => ucfirst(str_replace('_', ' ', (string) $r->label)), 'value' => (int) $r->value])->all(),
            'bySector' => (clone $attachments)->selectRaw("COALESCE(company_sector, 'Not given') as label, COUNT(*) as value")->groupBy('label')
                ->orderByDesc('value')->limit(8)->get()->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])->all(),
            'recentApplications' => DB::table('attachment_applications')->join('students', 'students.id', '=', 'attachment_applications.student_id')
                ->leftJoin('opportunities', 'opportunities.id', '=', 'attachment_applications.opportunity_id')
                ->whereNull('attachment_applications.deleted_at')->orderByDesc('attachment_applications.id')->limit(6)
                ->get(['attachment_applications.*', 'students.first_name', 'students.last_name', 'opportunities.title']),
        ];
    }
}
