<?php

namespace App\Console\Commands;

use App\Models\CardAccount;
use App\Services\CardQrCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RegenerateQrCodes extends Command
{
    protected $signature = 'qr:regenerate {--class= : Only regenerate for this class} {--force : Regenerate even if QR already exists}';

    protected $description = 'Regenerate QR codes with short-token payload (less dense)';

    public function handle(CardQrCodeService $svc)
    {
        $class = $this->option('class');
        $force = $this->option('force');

        $query = CardAccount::with('student');

        if ($class) {
            $query->where('student_class', $class);
            $this->info("Regenerating QRs for class: {$class}");
        } else {
            $this->info("Regenerating QRs for ALL cards");
        }

        $total = 0;
        $success = 0;
        $failed = 0;

        $query->chunk(100, function ($cards) use ($svc, $force, &$total, &$success, &$failed) {
            foreach ($cards as $c) {
                $total++;

                // Skip if already has QR and not forcing
                if (!$force && $c->qr_code && \Storage::disk('public')->exists($c->qr_code) && !empty($c->qr_token)) {
                    continue;
                }

                // Fresh short token
                $c->qr_token = Str::random(16);
                $c->qr_code = null;   // force overwrite
                $c->save();

                if ($c->student) {
                    $result = $svc->generateQrCode($c, $c->student);
                    if ($result['success'] ?? false) {
                        $this->line("✓ {$c->card_number} — {$c->student_name}");
                        $success++;
                    } else {
                        $this->error("✗ {$c->card_number} — " . ($result['message'] ?? 'unknown'));
                        $failed++;
                    }
                } else {
                    $this->warn("⚠ {$c->card_number} — no student");
                }
            }
        });

        $this->info('');
        $this->info("Done. Total: {$total}, Success: {$success}, Failed: {$failed}");

        return self::SUCCESS;
    }
}