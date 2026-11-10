<?php

namespace App\Console\Commands;

use App\Models\KcbIpn;
use App\Services\Payments\KcbIpnRouter;
use Illuminate\Console\Command;

/**
 * Re-checks quarantined KCB payments against the current rules and routes the ones that
 * now pass (for example after KCB's sending address was added to KCB_IPN_ALLOWED_IPS).
 * Ones that still fail stay quarantined for finance. Already-credited payments are never touched.
 *
 *   php artisan kcb:release-quarantined --dry-run
 *   php artisan kcb:release-quarantined
 */
class ReleaseQuarantinedIpns extends Command
{
    protected $signature = 'kcb:release-quarantined {--dry-run : Show what would be released without changing anything}';

    protected $description = 'Route quarantined KCB payments that pass the current source checks';

    public function handle(KcbIpnRouter $router): int
    {
        $dry = (bool) $this->option('dry-run');
        $rows = KcbIpn::where('status', KcbIpn::STATUS_QUARANTINED)->orderBy('id')->get();
        if ($rows->isEmpty()) {
            $this->info('No quarantined payments.');
            return self::SUCCESS;
        }

        $this->info('Allowed KCB addresses: ' . (implode(', ', config('kcb.ipn.allowed_ips', [])) ?: '(any)'));
        $outcomes = [];
        $kept = 0;

        foreach ($rows as $ipn) {
            $result = $router->releaseQuarantined($ipn, $dry);
            if (!$result) {
                $kept++;
                continue;
            }
            $status = $dry ? 'would be routed' : $result->status;
            $where = $dry ? $router->preview($ipn) : ($result->status_reason ?? '');
            $outcomes[$status] = ($outcomes[$status] ?? 0) + 1;
            $this->line(sprintf('  #%d  %s  KES %s  %s  -> %s', $ipn->id, $ipn->transaction_id, number_format((float) $ipn->transaction_amount, 2),
                $ipn->business_key ?? '-', $dry ? $where : "{$status}" . ($where ? " ({$where})" : '')));
        }

        $this->newLine();
        foreach ($outcomes as $status => $n) {
            $this->info("{$n} {$status}");
        }
        $this->info("{$kept} still quarantined (they fail the current checks, or were recovered from history)");
        if ($kept) {
            $ips = KcbIpn::where('status', KcbIpn::STATUS_QUARANTINED)->selectRaw('source_ip, COUNT(*) n')->groupBy('source_ip')->pluck('n', 'source_ip');
            foreach ($ips as $ip => $n) {
                $this->line("  from {$ip}: {$n}");
            }
        }

        return self::SUCCESS;
    }
}
