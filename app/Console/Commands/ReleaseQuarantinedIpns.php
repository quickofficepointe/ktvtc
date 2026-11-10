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
        $counts = [];

        foreach ($rows as $ipn) {
            $r = $router->releaseQuarantined($ipn, $dry);
            $counts[$r['outcome']] = ($counts[$r['outcome']] ?? 0) + 1;
            $label = match ($r['outcome']) {
                'untrusted' => 'still quarantined: not from a trusted KCB address',
                'held' => 'stays quarantined: ' . $r['detail'],
                'would release' => $r['detail'],
                default => $r['outcome'] . ($r['detail'] ? " ({$r['detail']})" : ''),
            };
            $this->line(sprintf('  #%d  %s  KES %s  %s  -> %s', $ipn->id, $ipn->transaction_id, number_format((float) $ipn->transaction_amount, 2),
                $ipn->business_key ?? '-', $label));
        }

        $this->newLine();
        $names = ['would release' => 'would be released', 'held' => 'stay quarantined (match nothing: finance checks them by hand)',
                  'untrusted' => 'still quarantined (not from a trusted KCB address)'];
        foreach ($counts as $outcome => $n) {
            $this->info("{$n} " . ($names[$outcome] ?? $outcome));
        }

        return self::SUCCESS;
    }
}
