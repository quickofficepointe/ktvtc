<?php

namespace App\Http\Controllers;

use App\Models\EvaluationQuestion;
use App\Models\User;
use App\Services\Academics\EvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Academic admin: evaluation questions and anonymous trainer reports.
 * Trainers get their own (anonymous) feedback page.
 */
class EvaluationAdminController extends Controller
{
    public function __construct(private EvaluationService $evaluations) {}

    public function report(Request $request)
    {
        $trainerId = $request->integer('trainer_id') ?: null;
        $rows = $this->evaluations->report($trainerId, $request->query('from'), $request->query('to'));
        $trainers = User::where('role', User::ROLE_TRAINERS)->orderBy('name')->get(['id', 'name']);
        $completion = $this->evaluations->completion();
        $min = EvaluationService::MIN_RESPONSES;

        return view('ktvtc.academics.evaluations', compact('rows', 'trainers', 'completion', 'min'));
    }

    public function questions()
    {
        $questions = EvaluationQuestion::withCount('answers')->orderBy('sort_order')->orderBy('id')->get();
        return view('ktvtc.academics.evaluation-questions', compact('questions'));
    }

    public function storeQuestion(Request $request)
    {
        EvaluationQuestion::create($this->validateQuestion($request) + ['is_active' => true]);
        return back()->with('success', 'Question added. Students see it from their next evaluation.');
    }

    public function updateQuestion(Request $request, EvaluationQuestion $question)
    {
        $data = $this->validateQuestion($request);
        if ($question->answers()->exists() && ($data['text'] !== $question->text || $data['type'] !== $question->type)) {
            return back()->with('error', 'Students have already answered this question, so its wording and type are fixed (old answers would no longer match). Deactivate it and add a new one instead.');
        }
        $question->update($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Question saved.');
    }

    public function myFeedback()
    {
        $rows = $this->evaluations->report(Auth::id());
        $min = EvaluationService::MIN_RESPONSES;

        return view('ktvtc.trainers.feedback', compact('rows', 'min'));
    }

    private function validateQuestion(Request $request): array
    {
        $data = $request->validate([
            'text'        => 'required|string|max:255',
            'type'        => 'required|in:rating,text',
            'is_required' => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0|max:999',
        ]);
        $data['is_required'] = $request->boolean('is_required');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        return $data;
    }
}
