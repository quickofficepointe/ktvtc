<?php

namespace App\Services\Academics;

use App\Models\CourseUnit;
use App\Models\GradingScale;
use App\Models\TermRegistration;
use App\Models\UnitResult;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Results for course units.
 *
 *   trainer enters theory/practical (or CAT/exam) -> draft
 *   trainer submits                                -> submitted (locked for the trainer)
 *   academic admin publishes                        -> published (student sees it)
 *   admin can return a submitted result to the trainer with a reason.
 *
 * A student who did not pass a published result is listed again for that unit
 * in their current term as a re-sit (attempt + 1). Earlier attempts are kept.
 */
class ResultsService
{
    /**
     * Students due for this unit.
     *
     * @param string $scope 'current' (term running or ended in the last 90 days) or 'all'
     * @return Collection<int, array{registration: TermRegistration, attempt: int, result: ?UnitResult, resit: bool}>
     */
    public function roster(CourseUnit $unit, string $scope = 'current'): Collection
    {
        $rows = collect();

        $regs = TermRegistration::with(['student', 'enrollment'])
            ->taught()
            ->where('course_id', $unit->course_id)
            ->where('term_number', $unit->term_number)
            ->where('starts_on', '<=', today())
            ->when($scope === 'current', fn ($q) => $q->where('ends_on', '>=', today()->subDays(90)))
            ->orderBy('starts_on')
            ->get();

        foreach ($regs as $reg) {
            $result = UnitResult::where('course_unit_id', $unit->id)->where('enrollment_id', $reg->enrollment_id)->where('attempt', 1)->first();
            $rows->put($reg->enrollment_id . ':1', ['registration' => $reg, 'attempt' => 1, 'result' => $result, 'resit' => false]);
        }

        // Re-sits: latest published attempt not passed -> next attempt in the student's latest term
        $failedLatest = UnitResult::where('course_unit_id', $unit->id)->published()
            ->whereIn('id', UnitResult::selectRaw('MAX(id)')->where('course_unit_id', $unit->id)->groupBy('enrollment_id'))
            ->where('passed', false)
            ->get();

        foreach ($failedLatest as $failed) {
            $nextAttempt = $failed->attempt + 1;
            $current = TermRegistration::with(['student', 'enrollment'])->taught()
                ->where('enrollment_id', $failed->enrollment_id)->orderByDesc('term_number')->first();
            if (!$current) {
                continue;
            }
            $result = UnitResult::where('course_unit_id', $unit->id)->where('enrollment_id', $failed->enrollment_id)->where('attempt', $nextAttempt)->first();
            $rows->put($failed->enrollment_id . ':' . $nextAttempt, ['registration' => $current, 'attempt' => $nextAttempt, 'result' => $result, 'resit' => true]);
        }

        // Re-sit rows already started show up whatever the scope
        UnitResult::with(['termRegistration.student', 'termRegistration.enrollment'])
            ->where('course_unit_id', $unit->id)->where('attempt', '>', 1)->where('status', '!=', UnitResult::PUBLISHED)
            ->get()
            ->each(function ($r) use ($rows) {
                $rows->put($r->enrollment_id . ':' . $r->attempt, ['registration' => $r->termRegistration, 'attempt' => $r->attempt, 'result' => $r, 'resit' => true]);
            });

        return $rows->sortBy(fn ($row) => $row['registration']->student->full_name ?? '')->values();
    }

    /**
     * Save marks as drafts. Results already submitted or published are left alone.
     *
     * @param array<string, array{score_a: ?string, score_b: ?string, remarks: ?string}> $marks keyed "enrollmentId:attempt"
     * @return array{saved: int, locked: int}
     */
    public function saveMarks(CourseUnit $unit, array $marks, User $by): array
    {
        $scale = $this->scaleFor($unit);
        $due = $this->roster($unit, 'all')->keyBy(fn ($row) => $row['registration']->enrollment_id . ':' . $row['attempt']);
        $saved = 0;
        $locked = 0;

        DB::transaction(function () use ($unit, $marks, $by, $scale, $due, &$saved, &$locked) {
            foreach ($marks as $key => $input) {
                $row = $due->get($key);
                if (!$row) {
                    continue; // not on this unit's roster
                }
                $a = $this->score($input['score_a'] ?? null);
                $b = $this->score($input['score_b'] ?? null);
                $remarks = trim((string) ($input['remarks'] ?? '')) ?: null;
                if ($a === null && $b === null && $remarks === null && !$row['result']) {
                    continue;
                }

                $reg = $row['registration'];
                $result = UnitResult::firstOrNew([
                    'course_unit_id' => $unit->id,
                    'enrollment_id'  => $reg->enrollment_id,
                    'attempt'        => $row['attempt'],
                ]);
                if ($result->exists && !$result->isEditableByTrainer()) {
                    $locked++;
                    continue;
                }

                $result->fill([
                    'term_registration_id' => $reg->id,
                    'student_id'           => $reg->student_id,
                    'trainer_id'           => $unit->trainer_id ?? $by->id,
                    'score_a'              => $a,
                    'score_b'              => $b,
                    'remarks'              => $remarks,
                    'status'               => UnitResult::DRAFT,
                    'entered_by'           => $by->id,
                ] + $scale->grade($a, $b));
                $result->save();
                $saved++;
            }
        });

        return ['saved' => $saved, 'locked' => $locked];
    }

    /**
     * Trainer hands complete drafts to the academic admin.
     */
    public function submit(CourseUnit $unit, User $by): array
    {
        $drafts = UnitResult::where('course_unit_id', $unit->id)->where('status', UnitResult::DRAFT)->get();
        $complete = $drafts->whereNotNull('total');

        UnitResult::whereIn('id', $complete->pluck('id'))->update([
            'status' => UnitResult::SUBMITTED, 'submitted_at' => now(), 'returned_reason' => null,
        ]);

        return ['submitted' => $complete->count(), 'incomplete' => $drafts->count() - $complete->count()];
    }

    public function publish(array $resultIds, User $by): int
    {
        return UnitResult::whereIn('id', $resultIds)->where('status', UnitResult::SUBMITTED)
            ->update(['status' => UnitResult::PUBLISHED, 'published_by' => $by->id, 'published_at' => now()]);
    }

    public function returnToTrainer(array $resultIds, string $reason): int
    {
        return UnitResult::whereIn('id', $resultIds)->where('status', UnitResult::SUBMITTED)
            ->update(['status' => UnitResult::DRAFT, 'returned_reason' => $reason, 'submitted_at' => null]);
    }

    /**
     * Admin correction of a published result: it goes back to draft for the trainer.
     */
    public function unpublish(UnitResult $result, string $reason): void
    {
        $result->update(['status' => UnitResult::DRAFT, 'returned_reason' => $reason, 'published_at' => null, 'published_by' => null]);
    }

    /**
     * Published results for a student, latest attempt per unit, grouped by enrollment and term.
     *
     * @return Collection<int, Collection> enrollment_id => term_number => results
     */
    public function transcript(int $studentId): Collection
    {
        $results = UnitResult::with(['unit', 'termRegistration'])->published()->where('student_id', $studentId)
            ->orderBy('attempt')->get();

        return $results
            ->groupBy(fn ($r) => $r->enrollment_id . ':' . $r->course_unit_id)
            ->map(fn ($attempts) => $attempts->last()->setAttribute('attempts_count', $attempts->count()))
            ->values()
            ->groupBy('enrollment_id')
            ->map(fn ($rows) => $rows->groupBy(fn ($r) => $r->unit->term_number)->sortKeys());
    }

    public function scaleFor(CourseUnit $unit): GradingScale
    {
        $scale = $unit->course->gradingScale();
        if (!$scale) {
            throw new \DomainException("{$unit->course->name} has no academic track yet (CDACC CBET, KNEC CBET or Internal). The academic admin sets it.");
        }
        return $scale;
    }

    private function score($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value) || $value < 0 || $value > 100) {
            throw new \DomainException("Scores must be between 0 and 100 (got \"{$value}\").");
        }
        return round((float) $value, 2);
    }
}
