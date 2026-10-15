<?php

namespace App\Services;

use App\Models\CardAccount;
use App\Models\HighSchoolStudent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class CardQrCodeService
{
    /**
     * Generate a compact QR code (short token payload = low density).
     */
    public function generateQrCode(CardAccount $card, HighSchoolStudent $student)
    {
        try {
            // 16-char token → QR Version ~2 (very light)
            if (empty($card->qr_token)) {
                $card->qr_token = Str::random(16);
                $card->save();
            }

            $qrData = $card->qr_token;

            $qrCode = Builder::create()
                ->writer(new PngWriter())
                ->data($qrData)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::Low)
                ->size(300)
                ->margin(5)
                ->build();

            $qrImage = $qrCode->getString();

            $directory = 'cards/qr/';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $filename = 'qr_' . $card->card_number . '.png';
            $path = $directory . $filename;

            Storage::disk('public')->put($path, $qrImage);

            $card->qr_code = $path;
            $card->qr_generated_at = now();
            $card->save();

            return [
                'success' => true,
                'path' => $path,
                'url' => Storage::url($path),
            ];

        } catch (\Exception $e) {
            \Log::error('QR Code generation failed', [
                'card_id' => $card->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Resolve a scanned QR payload back to a CardAccount.
     */
    public function resolveQrData(string $qrData): ?CardAccount
    {
        return CardAccount::where('qr_token', $qrData)
            ->with('student')
            ->first();
    }
}