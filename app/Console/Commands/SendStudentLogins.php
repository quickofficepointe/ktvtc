<?php

namespace App\Console\Commands;

use App\Http\Controllers\StudentPortalAccessController;
use App\Services\Students\StudentAccountService;
use Illuminate\Console\Command;

/**
 * Texts portal login details to every enrolled student who has no account or has
 * never signed in (skipping anyone texted in the last 7 days). For the one-off
 * catch-up after deploying; day to day, enrollment texts the details itself.
 */
class SendStudentLogins extends Command
{
    protected $signature = 'students:send-logins {--dry-run : Count who would be texted without sending} {--limit=0 : Stop after this many}';

    protected $description = 'Text login details to enrolled students who have never signed in to the portal';

    public function handle(StudentAccountService $accounts): int
    {
        $query = StudentPortalAccessController::neverSignedIn(true)->orderBy('id');
        $total = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$total} student(s) would be texted. Nothing sent.");
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $sent = $failed = 0;
        $bar = $this->output->createProgressBar($limit ? min($limit, $total) : $total);

        foreach ($query->cursor() as $student) {
            if ($limit && $sent + $failed >= $limit) {
                break;
            }
            $result = $accounts->sendLoginDetails($student);
            $result['ok'] ? $sent++ : $failed++;
            if (!$result['ok']) {
                $this->newLine();
                $this->warn("{$student->student_number}: {$result['message']}");
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info("Sent {$sent}, not sent {$failed}.");

        return self::SUCCESS;
    }
}
