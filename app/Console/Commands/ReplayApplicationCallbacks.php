<?php

namespace App\Console\Commands;

use App\Models\KcbBuniTransaction;
use App\Services\ApplicationKcbService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Application-fee STK callbacks that KCB delivered but the app failed to record
 * (until Nov 2026 they crashed on the wrong relation) are re-read from the log
 * and applied. Safe to run again: completed prompts are skipped.
 */
class ReplayApplicationCallbacks extends Command
{
    protected $signature = 'applications:replay-callbacks
        {--log= : Log file to read (default storage/logs/laravel.log)}
        {--dry-run : Only list what would change}
        {--sms : Also text the applicant and admissions for each payment recorded}';

    protected $description = 'Record application-fee payments whose STK callback is in the log but was never saved';

    private const MARKER = 'Application payment callback received:';

    public function handle(ApplicationKcbService $service): int
    {
        $file = $this->option('log') ?: storage_path('logs/laravel.log');
        if (!is_readable($file)) {
            $this->error("Cannot read {$file}");
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $rows = [];
        $handle = fopen($file, 'r');
        while (($line = fgets($handle)) !== false) {
            if (($pos = strpos($line, self::MARKER)) === false) {
                continue;
            }
            $data = json_decode(trim(substr($line, $pos + strlen(self::MARKER))), true);
            $cb = $data['Body']['stkCallback'] ?? null;
            if (!$cb || empty($cb['CheckoutRequestID'])) {
                continue;
            }

            $tx = KcbBuniTransaction::where('checkout_request_id', $cb['CheckoutRequestID'])
                ->where('transaction_type', 'application_fee')->first();
            if (!$tx || $tx->status !== 'initiated') {
                continue; // unknown prompt, or already recorded
            }

            $meta = collect($cb['CallbackMetadata']['Item'] ?? [])->filter(fn ($i) => isset($i['Name'], $i['Value']))
                ->mapWithKeys(fn ($i) => [$i['Name'] => $i['Value']]);
            $code = $cb['ResultCode'] ?? null; // may be text, e.g. "E3008"
            $paid = KcbBuniTransaction::isSuccess($code);
            $receipt = $meta['MpesaReceiptNumber'] ?? null;
            $paidAt = isset($meta['TransactionDate'])
                ? rescue(fn () => Carbon::createFromFormat('YmdHis', (string) $meta['TransactionDate']), now(), false)
                : null;

            $rows[] = [$tx->id, $tx->application_id, $tx->phone_number, $paid ? 'paid' : 'failed', $receipt ?? '-', $paidAt?->format('Y-m-d H:i') ?? '-'];

            if ($dry) {
                continue;
            }
            if ($paid) {
                $service->markPaid($tx, $receipt, $paidAt, [
                    'result_code' => $code,
                    'result_description' => $cb['ResultDesc'] ?? '',
                    'callback_data' => $data,
                ], (bool) $this->option('sms'));
            } else {
                $tx->update(['status' => 'failed', 'result_code' => $code, 'result_description' => $cb['ResultDesc'] ?? '', 'callback_data' => $data]);
            }
        }
        fclose($handle);

        if (!$rows) {
            $this->info('Nothing to replay.');
            return self::SUCCESS;
        }
        $this->table(['Prompt', 'Application', 'Phone', 'Result', 'Receipt', 'Paid at'], $rows);
        $this->info(($dry ? 'Would record ' : 'Recorded ') . count($rows) . ' callback(s).');
        return self::SUCCESS;
    }
}
