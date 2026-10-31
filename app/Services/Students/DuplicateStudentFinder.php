<?php

namespace App\Services\Students;

use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Finds people who already have a student record: same ID number, or same
 * name and phone, or same email. Used to stop new duplicates and to list
 * existing ones for the merge screen.
 */
class DuplicateStudentFinder
{
    /**
     * Existing students who look like the person described.
     *
     * @param array{id_number?: ?string, first_name?: ?string, last_name?: ?string, phone?: ?string, email?: ?string} $person
     * @return Collection<int, array{student: Student, why: string}>
     */
    public function matchesFor(array $person, ?int $exceptId = null): Collection
    {
        $found = collect();
        $add = function ($students, string $why) use ($found) {
            foreach ($students as $s) {
                if (!$found->has($s->id)) {
                    $found->put($s->id, ['student' => $s, 'why' => $why]);
                }
            }
        };
        $base = fn () => Student::query()->whereNull('merged_into_id')->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->limit(10);

        $id = $this->clean($person['id_number'] ?? null);
        if ($id && strlen($id) >= 5) {
            $add($base()->where('id_number', $id)->get(), 'Same ID number');
        }

        $phone = substr(preg_replace('/\D/', '', (string) ($person['phone'] ?? '')), -9);
        $first = strtoupper(trim((string) ($person['first_name'] ?? '')));
        $last = strtoupper(trim((string) ($person['last_name'] ?? '')));
        if (strlen($phone) === 9 && $first !== '' && $last !== '') {
            $add($base()->whereRaw("RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 9) = ?", [$phone])
                ->whereRaw('UPPER(TRIM(first_name)) = ?', [$first])->whereRaw('UPPER(TRIM(last_name)) = ?', [$last])->get(), 'Same name and phone');
        }

        $email = strtolower(trim((string) ($person['email'] ?? '')));
        if ($email !== '' && !str_ends_with($email, '@student.ktvtc.ac.ke')) {
            $add($base()->whereRaw('LOWER(TRIM(email)) = ?', [$email])->get(), 'Same email');
        }

        return $found->values();
    }

    /**
     * Groups of existing records that look like the same person.
     *
     * @return Collection<int, array{why: string, students: Collection<int, Student>}>
     */
    public function groups(): Collection
    {
        $groups = collect();

        $byId = Student::whereNull('merged_into_id')->whereNotNull('id_number')->whereRaw("LENGTH(TRIM(id_number)) >= 5")
            ->select('id_number')->groupBy('id_number')->havingRaw('COUNT(*) > 1')->pluck('id_number');
        foreach ($byId as $id) {
            $groups->push(['why' => "Same ID number ({$id})", 'ids' => Student::whereNull('merged_into_id')->where('id_number', $id)->pluck('id')->sort()->values()]);
        }

        $byNamePhone = DB::table('students')->whereNull('deleted_at')->whereNull('merged_into_id')->whereNotNull('phone')
            ->selectRaw("UPPER(TRIM(first_name)) f, UPPER(TRIM(last_name)) l, RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 9) p, GROUP_CONCAT(id) ids")
            ->groupByRaw("UPPER(TRIM(first_name)), UPPER(TRIM(last_name)), RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 9)")
            ->havingRaw('COUNT(*) > 1 AND LENGTH(p) = 9')->get();
        foreach ($byNamePhone as $g) {
            $groups->push(['why' => 'Same name and phone', 'ids' => collect(explode(',', $g->ids))->map(fn ($i) => (int) $i)->sort()->values()]);
        }

        // One entry per set of records
        return $groups->unique(fn ($g) => $g['ids']->implode(','))->values()
            ->map(fn ($g) => ['why' => $g['why'], 'students' => Student::withCount('enrollments')->whereIn('id', $g['ids'])->orderBy('id')->get()])
            ->filter(fn ($g) => $g['students']->count() > 1)->values();
    }

    private function clean(?string $value): ?string
    {
        $value = strtoupper(preg_replace('/\s+/', '', (string) $value));
        return $value === '' ? null : $value;
    }
}
