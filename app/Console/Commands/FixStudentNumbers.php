<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off clean-up of numbers damaged by the old enrollment code:
 *   - course codes stored with a trailing slash ("ICT/") produced numbers like ICT//985/2026;
 *   - the trailing slash is removed from course codes, and "//" from student, enrollment and login numbers.
 * Payments made with the old "//" spelling still match: the payment router ignores separators.
 */
class FixStudentNumbers extends Command
{
    protected $signature = 'students:fix-numbers {--dry-run}';

    protected $description = 'Remove the doubled slash from student numbers and trailing slashes from course codes';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $fix = fn ($n) => \App\Support\StudentNumber::clean((string) $n);

        $courses = Course::where('code', 'like', '%/%')->get();
        $students = Student::withTrashed()->where('student_number', 'like', '%//%')->get();
        $enrollments = Enrollment::withTrashed()->where('student_number', 'like', '%//%')->get();
        $users = User::where('username', 'like', '%//%')->get();

        // A clean number already used by SOMEONE ELSE blocks the fix (the same student's own copy is fine)
        $clashes = $students->filter(fn ($s) => Student::withTrashed()->where('student_number', $fix($s->student_number))->where('id', '!=', $s->id)->exists())->pluck('student_number')
            ->merge($enrollments->filter(fn ($e) => Enrollment::withTrashed()->where('student_number', $fix($e->student_number))->where('student_id', '!=', $e->student_id)->exists()
                || Student::withTrashed()->where('student_number', $fix($e->student_number))->where('id', '!=', $e->student_id)->exists())->pluck('student_number'))
            ->merge($users->filter(fn ($u) => User::where('username', $fix($u->username))->whereKeyNot($u->id)->exists())->pluck('username'))
            ->unique();
        if ($clashes->isNotEmpty()) {
            $this->error('These would clash with an existing number; fix them by hand first: ' . $clashes->implode(', '));
            return self::FAILURE;
        }

        $this->table(['What', 'Count', 'Example'], [
            ['Course codes', $courses->count(), $courses->first() ? "{$courses->first()->code} -> " . \App\Support\StudentNumber::courseCode($courses->first()->code) : ''],
            ['Student numbers', $students->count(), $students->first() ? "{$students->first()->student_number} -> " . $fix($students->first()->student_number) : ''],
            ['Enrollment numbers', $enrollments->count(), $enrollments->first()->student_number ?? ''],
            ['Login usernames', $users->count(), $users->first()->username ?? ''],
        ]);

        $odd = Student::where('student_number', 'regexp', '^[A-Z0-9]+/(19|20)[0-9]{2}/(19|20)[0-9]{2}$')->pluck('student_number');
        if ($odd->isNotEmpty()) {
            $this->warn('Numbers that look damaged (running number is a year), check by hand: ' . $odd->implode(', '));
        }

        if ($dry) {
            $this->info('Dry run: nothing changed.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($courses, $students, $enrollments, $users, $fix) {
            $courses->each(fn ($c) => $c->forceFill(['code' => $c->code])->save()); // Course::setCodeAttribute strips the slash
            $students->each(fn ($s) => $s->forceFill(['student_number' => $fix($s->student_number)])->save());
            $enrollments->each(fn ($e) => $e->forceFill(['student_number' => $fix($e->student_number)])->saveQuietly());
            $users->each(fn ($u) => $u->forceFill(['username' => $fix($u->username)])->save());
        });

        $this->info('Fixed.');
        return self::SUCCESS;
    }
}
