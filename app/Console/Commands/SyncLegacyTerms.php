<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Services\Terms\TermRegistrationService;
use Illuminate\Console\Command;

/**
 * Existing students (whole-course fee) are registered for every term up to
 * today, once finance has split their course into terms. Safe to re-run.
 * Students' portal pages also do this on their own the first time they open.
 */
class SyncLegacyTerms extends Command
{
    protected $signature = 'terms:sync-existing';

    protected $description = 'Register existing course-fee students for the terms they are already in';

    public function handle(TermRegistrationService $terms): int
    {
        $created = 0;
        $enrollments = 0;
        $notSetUp = [];

        Enrollment::with('course')->where('status', 'active')->where('billing_mode', 'course')
            ->chunkById(200, function ($rows) use ($terms, &$created, &$enrollments, &$notSetUp) {
                foreach ($rows as $enrollment) {
                    if (!$enrollment->course?->hasTermSetup()) {
                        $notSetUp[$enrollment->course?->name ?? 'No course'] = ($notSetUp[$enrollment->course?->name ?? 'No course'] ?? 0) + 1;
                        continue;
                    }
                    $n = $terms->syncLegacy($enrollment);
                    $created += $n;
                    $enrollments += $n > 0 ? 1 : 0;
                }
            });

        $this->info("Registered {$created} terms for {$enrollments} enrollments.");

        if ($notSetUp) {
            arsort($notSetUp);
            $this->warn('Courses not split into terms yet (set them up in Finance > Term Fees, then run again):');
            $this->table(['Course', 'Active enrollments'], collect($notSetUp)->map(fn ($n, $c) => [$c, $n])->values());
        }

        return self::SUCCESS;
    }
}
