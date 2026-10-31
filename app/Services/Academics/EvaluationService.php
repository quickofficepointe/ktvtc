<?php

namespace App\Services\Academics;

use App\Models\CourseUnit;
use App\Models\EvaluationQuestion;
use App\Models\TermRegistration;
use App\Models\TrainerEvaluation;
use App\Models\TrainerEvaluationAnswer;
use App\Models\UnitResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Students evaluate the trainer of every unit they take in a term.
 * Reports are anonymous: answers are never tied to a name, and a unit's
 * results are only shown once MIN_RESPONSES students have answered.
 */
class EvaluationService
{
    public const MIN_RESPONSES = 3;

    /**
     * Units with a trainer that the student takes in this term: the term's units,
     * plus units they re-sit this term.
     *
     * @return Collection<int, CourseUnit> each with ->evaluated (bool)
     */
    public function unitsFor(TermRegistration $reg): Collection
    {
        $resitUnitIds = UnitResult::where('term_registration_id', $reg->id)->where('attempt', '>', 1)->pluck('course_unit_id');

        $units = CourseUnit::with('trainer')->active()->whereNotNull('trainer_id')
            ->where('course_id', $reg->course_id)
            ->where(fn ($q) => $q->where('term_number', $reg->term_number)->orWhereIn('id', $resitUnitIds))
            ->orderBy('term_number')->orderBy('sort_order')->orderBy('id')->get();

        $done = TrainerEvaluation::where('term_registration_id', $reg->id)->pluck('course_unit_id')->flip();

        return $units->each(fn ($u) => $u->evaluated = $done->has($u->id));
    }

    /** @return string[] e.g. ["Evaluate your trainer for Baking Basics (Jane W.)."] */
    public function pendingFor(TermRegistration $reg): array
    {
        if ($reg->isExempt() || ($reg->starts_on && $reg->starts_on->isFuture())) {
            return [];
        }

        $pending = $this->unitsFor($reg)->reject(fn ($u) => $u->evaluated);
        if ($pending->isEmpty()) {
            return [];
        }

        return ['Evaluate your trainers for: ' . $pending->map(fn ($u) => $u->label . ' (' . ($u->trainer->name ?? 'trainer') . ')')->implode(', ') . '.'];
    }

    /**
     * @param array<int, mixed> $answers question_id => rating (1-5) or text
     * @throws \DomainException
     */
    public function submit(TermRegistration $reg, CourseUnit $unit, array $answers): TrainerEvaluation
    {
        if ($reg->status !== 'registered') {
            throw new \DomainException('This term registration is not active.');
        }
        if ($reg->starts_on->isFuture()) {
            throw new \DomainException("Term {$reg->term_number} hasn't started yet.");
        }

        $unit = $this->unitsFor($reg)->firstWhere('id', $unit->id);
        if (!$unit) {
            throw new \DomainException('You don\'t take this unit this term.');
        }
        if ($unit->evaluated) {
            throw new \DomainException('You have already evaluated this trainer for this unit.');
        }

        $rows = [];
        foreach (EvaluationQuestion::active()->get() as $q) {
            $value = $answers[$q->id] ?? null;
            if ($q->type === 'rating') {
                $rating = is_numeric($value) ? (int) $value : null;
                if ($rating !== null && ($rating < 1 || $rating > 5)) {
                    throw new \DomainException('Ratings must be from 1 to 5.');
                }
                if ($rating === null && $q->is_required) {
                    throw new \DomainException('Please answer every rating question.');
                }
                $rows[] = ['evaluation_question_id' => $q->id, 'rating' => $rating, 'answer' => null];
            } else {
                $text = trim(mb_substr((string) $value, 0, 1000));
                if ($text === '' && $q->is_required) {
                    throw new \DomainException('Please answer every required question.');
                }
                if ($text !== '') {
                    $rows[] = ['evaluation_question_id' => $q->id, 'rating' => null, 'answer' => $text];
                }
            }
        }

        return DB::transaction(function () use ($reg, $unit, $rows) {
            $evaluation = TrainerEvaluation::create([
                'term_registration_id' => $reg->id,
                'course_unit_id'       => $unit->id,
                'trainer_id'           => $unit->trainer_id,
                'student_id'           => $reg->student_id,
                'term_number'          => $reg->term_number,
            ]);
            $evaluation->answers()->createMany($rows);
            return $evaluation;
        });
    }

    /**
     * Anonymous summary per unit (and trainer), optionally for one trainer.
     *
     * @return Collection<int, array{unit: CourseUnit, trainer: ?\App\Models\User, responses: int, visible: bool,
     *         overall: ?float, questions: Collection, comments: Collection}>
     */
    public function report(?int $trainerId = null, ?string $from = null, ?string $to = null): Collection
    {
        $evaluations = TrainerEvaluation::query()
            ->when($trainerId, fn ($q) => $q->where('trainer_id', $trainerId))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        $groups = (clone $evaluations)->selectRaw('course_unit_id, trainer_id, COUNT(*) as responses')
            ->groupBy('course_unit_id', 'trainer_id')->get();

        $questions = EvaluationQuestion::orderBy('sort_order')->get()->keyBy('id');

        return $groups->map(function ($g) use ($evaluations, $questions) {
            $ids = (clone $evaluations)->where('course_unit_id', $g->course_unit_id)->where('trainer_id', $g->trainer_id)->pluck('id');
            $answers = TrainerEvaluationAnswer::whereIn('trainer_evaluation_id', $ids)->get();

            $perQuestion = $answers->whereNotNull('rating')->groupBy('evaluation_question_id')
                ->map(fn ($a, $qid) => ['question' => $questions[$qid]->text ?? '?', 'average' => round($a->avg('rating'), 2), 'count' => $a->count()])
                ->sortBy(fn ($row, $qid) => $questions[$qid]->sort_order ?? 0)->values();

            $visible = $g->responses >= self::MIN_RESPONSES;

            return [
                'unit'      => CourseUnit::with('course')->find($g->course_unit_id),
                'trainer'   => \App\Models\User::find($g->trainer_id),
                'responses' => (int) $g->responses,
                'visible'   => $visible,
                'overall'   => $visible ? round($answers->whereNotNull('rating')->avg('rating'), 2) : null,
                'questions' => $visible ? $perQuestion : collect(),
                // Shuffled so order can't be matched to submission times
                'comments'  => $visible ? $answers->whereNotNull('answer')->pluck('answer')->shuffle()->values() : collect(),
            ];
        })->sortByDesc('responses')->values();
    }

    /**
     * How many registered students have/haven't finished their evaluations, per course term.
     */
    public function completion(): Collection
    {
        return TermRegistration::with('course')->taught()->where('starts_on', '<=', today())->where('ends_on', '>=', today()->subDays(30))
            ->get()
            ->groupBy(fn ($r) => $r->course_id . ':' . $r->term_number)
            ->map(function ($regs) {
                $first = $regs->first();
                $done = $regs->filter(fn ($r) => empty($this->pendingFor($r)))->count();
                return ['course' => $first->course->name ?? '?', 'term' => $first->term_number, 'students' => $regs->count(), 'complete' => $done];
            })
            ->sortBy(['course', 'term'])->values();
    }
}
