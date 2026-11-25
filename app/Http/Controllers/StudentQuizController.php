<?php

namespace App\Http\Controllers;

use App\Models\PortalQuizAttempt;
use App\Services\Students\PortalQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Student: 10-question quiz on paying fees, evaluating trainers and the dashboard.
 * Marked at once with explanations; every attempt is kept so the student sees their scores.
 */
class StudentQuizController extends Controller
{
    public function __construct(private PortalQuiz $quiz) {}

    public function index()
    {
        $user = Auth::user();
        $questions = $this->quiz->questions($user->student?->student_number);
        $attempts = $this->attempts();

        return view('ktvtc.students.quiz.index', compact('questions', 'attempts'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $count = count($this->quiz->questions());
        $data = $request->validate([
            'answers'   => "required|array|size:{$count}",
            'answers.*' => 'required|integer|min:0|max:3',
        ], [
            'answers.size'       => 'Please answer all ' . $count . ' questions.',
            'answers.required'   => 'Please answer all ' . $count . ' questions.',
            'answers.*.required' => 'Please answer all ' . $count . ' questions.',
        ]);

        $result = $this->quiz->mark($data['answers'], $user->student?->student_number);
        $attempt = PortalQuizAttempt::create([
            'user_id'    => $user->id,
            'student_id' => $user->student_id,
            'quiz'       => PortalQuiz::KEY,
            'score'      => $result['score'],
            'total'      => $result['total'],
            'answers'    => array_map('intval', $data['answers']),
        ]);

        return redirect()->route('student.quiz.show', $attempt);
    }

    public function show(PortalQuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 404);
        $user = Auth::user();
        $questions = $this->quiz->questions($user->student?->student_number);
        $result = $this->quiz->mark($attempt->answers, $user->student?->student_number);
        $attempts = $this->attempts();

        return view('ktvtc.students.quiz.result', compact('attempt', 'questions', 'result', 'attempts'));
    }

    private function attempts()
    {
        return PortalQuizAttempt::where('user_id', Auth::id())->where('quiz', PortalQuiz::KEY)->latest()->get();
    }
}
