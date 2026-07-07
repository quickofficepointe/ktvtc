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
    public function generateQrCode(CardAccount $card, HighSchoolStudent $student)
    {
        try {
            // 🔥 SECURE BUT SHORT: Encrypt just the account number
            // This is shorter than JSON + token + timestamp
            $encryptedAccount = encrypt($card->account_number);

            // Add a short validation hash (6 chars)
            $hash = substr(md5($card->id . $card->card_number), 0, 6);

            // Combine: encrypted|hash - still secure but shorter
            $qrData = $encryptedAccount . '|' . $hash;

            $card->qr_token = Str::random(16) . '-' . time();
            $card->save();

            // ✅ LARGE QR CODE with LOW error correction = FEWER DOTS
            $qrCode = Builder::create()
                ->writer(new PngWriter())
                ->data($qrData)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::Low)  // ✅ Fewer dots
                ->size(500)  // ✅ Large
                ->margin(20) // ✅ More space
                ->build();

            $qrImage = $qrCode->getString();

            // Save QR code
            $directory = 'cards/qr/';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $filename = 'qr_' . $card->card_number . '_' . time() . '.png';
            $path = $directory . $filename;

            Storage::disk('public')->put($path, $qrImage);

            $card->qr_code = $path;
            $card->qr_generated_at = now();
            $card->save();

            return [
                'success' => true,
                'path' => $path,
                'url' => Storage::url($path)
            ];

        } catch (\Exception $e) {
            \Log::error('QR Code generation failed', [
                'card_id' => $card->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Decrypt QR code data
     */
    public function decryptQrData($qrData)
    {
        try {
            // Split encrypted data and hash
            $parts = explode('|', $qrData);
            if (count($parts) != 2) {
                return null;
            }

            $encryptedAccount = $parts[0];
            $hash = $parts[1];

            // Decrypt account number
            $accountNumber = decrypt($encryptedAccount);

            // Find card by account number
            $card = CardAccount::where('account_number', $accountNumber)->first();

            if (!$card) {
                return null;
            }

            // Verify hash
            $expectedHash = substr(md5($card->id . $card->card_number), 0, 6);
            if ($hash !== $expectedHash) {
                return null;
            }

            return $card;

        } catch (\Exception $e) {
            return null;
        }
    }
}
