<?php

namespace App\Console\Commands;

use App\Models\HighSchoolKcbTransaction;
use App\Models\KcbIpn;
use App\Services\Payments\KcbIpnRouter;
use Illuminate\Console\Command;

/**
 * Brings IPNs that the old handlers failed to record into the kcb_ipns ledger.
 *
 * Nothing is credited automatically. Already-applied payments are marked
 * "duplicate", sales are "linked", and everything else waits in
 * Finance > KCB Payments for someone to confirm.
 *
 *   php artisan kcb:import-history --misrouted
 *   php artisan kcb:import-history --log=C:\Users\ENVY\Downloads\ipn_since_aug.txt
 *   add --dry-run to see what would happen without saving
 */
class ImportKcbIpnHistory extends Command
{
    protected $signature = 'kcb:import-history
        {--misrouted : Failed/school-fee IPNs stored in high_school_kcb_transactions by the old card handler}
        {--log= : Laravel log file (or grep output) containing "KCB IPN RECEIVED" entries}
        {--dry-run : Parse and report only}';

    protected $description = 'Recover KCB IPNs from old tables or logs into the payment ledger for finance review';

    public function handle(KcbIpnRouter $router): int
    {
        if (!$this->option('misrouted') && !$this->option('log')) {
            $this->error('Choose --misrouted and/or --log=<file>.');
            return self::INVALID;
        }

        $payloads = [];

        if ($this->option('misrouted')) {
            $payloads = array_merge($payloads, $this->fromMisroutedRows());
        }

        if ($file = $this->option('log')) {
            if (!is_readable($file)) {
                $this->error("Cannot read {$file}");
                return self::FAILURE;
            }
            $payloads = array_merge($payloads, $this->fromLog($file));
        }

        // Same IPN can appear in several sources or log lines
        $unique = [];
        foreach ($payloads as [$payload, $ip]) {
            $id = $payload['requestPayload']['additionalData']['notificationData']['transactionID'] ?? null;
            if ($id) {
                $unique[$id] ??= [$payload, $ip];
            }
        }

        $this->info(count($unique) . ' distinct IPNs found.');

        if ($this->option('dry-run')) {
            $known = KcbIpn::whereIn('transaction_id', array_keys($unique))->pluck('transaction_id')->all();
            $this->line(count($known) . ' already in the ledger; ' . (count($unique) - count($known)) . ' would be imported.');
            return self::SUCCESS;
        }

        $tally = [];
        foreach ($unique as [$payload, $ip]) {
            $ipn = $router->receive($payload, null, $ip, null, historical: true);
            $tally[$ipn->status] = ($tally[$ipn->status] ?? 0) + 1;
        }

        $this->table(['Outcome', 'Count'], collect($tally)->map(fn ($n, $s) => [$s, $n])->values());
        $this->info('Review anything "unallocated" in Finance > KCB Payments.');

        return self::SUCCESS;
    }

    /**
     * The old card IPN handler stored every IPN it got, including school-fee
     * ones it wrongly treated as card top-ups, with the full payload.
     */
    private function fromMisroutedRows(): array
    {
        $out = [];

        HighSchoolKcbTransaction::whereNotNull('transaction_reference')
            ->where(function ($q) {
                $q->where('status', '!=', 'completed')
                  ->orWhere('invoice_number', 'like', config('kcb.tills.school_fees') . '#%');
            })
            ->orderBy('id')
            ->chunk(200, function ($rows) use (&$out) {
                foreach ($rows as $row) {
                    $payload = $this->decode($row->callback_data);
                    if (isset($payload['requestPayload'])) {
                        $out[] = [$payload, null];
                    }
                }
            });

        $this->line(count($out) . ' IPN payloads in high_school_kcb_transactions.');
        return $out;
    }

    /**
     * Log lines look like:
     *   [2026-10-01 10:00:00] production.INFO: ===...=== {"payload":{...},"ip":"196.216.222.14"}
     */
    private function fromLog(string $file): array
    {
        $out = [];
        $handle = fopen($file, 'r');

        while (($line = fgets($handle)) !== false) {
            $pos = strpos($line, '{"payload":');
            if ($pos === false) {
                continue;
            }
            $json = json_decode(rtrim(substr($line, $pos)), true);
            if (isset($json['payload']['requestPayload'])) {
                $out[] = [$json['payload'], $json['ip'] ?? null];
            }
        }
        fclose($handle);

        $this->line(count($out) . ' IPN payloads in ' . basename($file) . '.');
        return $out;
    }

    private function decode($value): array
    {
        // Older rows were json_encoded before an array cast, so they may be double-encoded
        for ($i = 0; $i < 3 && is_string($value); $i++) {
            $value = json_decode($value, true);
        }
        return is_array($value) ? $value : [];
    }
}
