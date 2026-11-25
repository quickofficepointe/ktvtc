<?php

namespace App\Http\Controllers;

use App\Models\PortalQuizAttempt;
use App\Models\User;
use App\Services\Students\PortalQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin: how students did on the portal quiz, who has not taken it, which questions are
 * answered wrongly most often, and who has watched the how-to video.
 */
class AdminQuizScoresController extends Controller
{
    public function index(Request $request, PortalQuiz $quiz)
    {
        $tab = in_array($request->tab, ['taken', 'not-taken'], true) ? $request->tab : 'taken';
        $search = trim((string) $request->search);

        $scores = PortalQuizAttempt::where('quiz', PortalQuiz::KEY)
            ->selectRaw('user_id, COUNT(*) AS attempts, MAX(score) AS best, MAX(total) AS total, MAX(created_at) AS last_at, MIN(created_at) AS first_at')
            ->groupBy('user_id');

        // Students with a portal login (role 5, linked to a student record)
        $students = User::query()
            ->where('users.role', User::ROLE_STUDENT)->whereNotNull('users.student_id')
            ->leftJoin('students', 'students.id', '=', 'users.student_id')
            ->leftJoinSub($scores, 'q', 'q.user_id', '=', 'users.id')
            ->when($search !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('users.name', 'like', "%{$search}%")
                ->orWhere('students.student_number', 'like', "%{$search}%")
                ->orWhere('students.phone', 'like', "%{$search}%")));

        $summary = [
            'students' => (clone $students)->count(),
            'taken'    => (clone $students)->whereNotNull('q.user_id')->count(),
            'average'  => (clone $students)->whereNotNull('q.user_id')->avg(DB::raw('q.best * 100 / q.total')),
            'perfect'  => (clone $students)->whereNotNull('q.user_id')->whereColumn('q.best', 'q.total')->count(),
            'video'    => (clone $students)->whereNotNull('users.welcome_video_watched_at')->count(),
        ];

        $rows = $students
            ->when($tab === 'taken', fn ($w) => $w->whereNotNull('q.user_id')->orderByDesc('q.last_at'))
            ->when($tab === 'not-taken', fn ($w) => $w->whereNull('q.user_id')->orderByDesc('users.last_login_at'))
            ->select('users.id', 'users.name', 'users.last_login_at', 'users.welcome_video_watched_at',
                'students.student_number', 'students.phone', 'q.attempts', 'q.best', 'q.total', 'q.last_at')
            ->paginate(50)->withQueryString();

        // Latest score per student for the rows on this page
        $latest = PortalQuizAttempt::whereIn('user_id', $rows->pluck('id'))->where('quiz', PortalQuiz::KEY)
            ->orderByDesc('id')->get(['user_id', 'score'])->unique('user_id')->pluck('score', 'user_id');

        // How often each question is answered correctly (each student's latest attempt)
        $questions = $quiz->questions();
        $right = array_fill(0, count($questions), 0);
        $counted = 0;
        PortalQuizAttempt::where('quiz', PortalQuiz::KEY)
            ->whereIn('id', PortalQuizAttempt::where('quiz', PortalQuiz::KEY)->selectRaw('MAX(id)')->groupBy('user_id'))
            ->select('answers')->chunk(500, function ($chunk) use (&$right, &$counted, $questions) {
                foreach ($chunk as $a) {
                    $counted++;
                    foreach ($questions as $i => $q) {
                        if (isset($a->answers[$i]) && (int) $a->answers[$i] === $q['answer']) {
                            $right[$i]++;
                        }
                    }
                }
            });
        $byQuestion = collect($questions)->map(fn ($q, $i) => [
            'q' => $q['q'],
            'percent' => $counted ? (int) round($right[$i] * 100 / $counted) : null,
        ]);

        return view('ktvtc.admin.quiz-scores.index', compact('tab', 'search', 'summary', 'rows', 'latest', 'byQuestion', 'counted'));
    }
}
