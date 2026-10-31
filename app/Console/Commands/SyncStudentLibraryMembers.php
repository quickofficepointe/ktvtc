<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\Member;
use App\Models\Student;
use App\Services\Library\LendingService;
use Illuminate\Console\Command;

/**
 * Makes every student with an active enrollment a library member, and marks
 * student members whose enrollments ended as inactive. Students also become
 * members on their own the first time they open My Library or borrow a book;
 * this just fills the librarian's member list in one go. Safe to re-run.
 */
class SyncStudentLibraryMembers extends Command
{
    protected $signature = 'library:sync-students';

    protected $description = 'Make every actively enrolled student a library member';

    public function handle(LendingService $lending): int
    {
        $ids = Enrollment::where('status', 'active')->distinct()->pluck('student_id')
            ->merge(Member::whereNotNull('student_id')->pluck('student_id'))->unique();

        $active = 0;
        $inactive = 0;
        foreach (Student::whereNull('merged_into_id')->whereIn('id', $ids)->cursor() as $student) {
            $lending->memberForStudent($student)->is_active ? $active++ : $inactive++;
        }

        $this->info("Student members: {$active} active, {$inactive} inactive (no active enrollment).");

        return self::SUCCESS;
    }
}
