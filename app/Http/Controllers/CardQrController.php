<?php

namespace App\Http\Controllers;

use App\Models\CardAccount;
use App\Services\CardQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CardQrController extends Controller
{
    protected $qrService;

    public function __construct(CardQrCodeService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Generate QR code for a card
     */
    public function generate(CardAccount $cardAccount)
    {
        try {
            $student = $cardAccount->student;
            if (!$student) {
                return redirect()->back()->with('error', 'Student not found');
            }

            $result = $this->qrService->generateQrCode($cardAccount, $student);

            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'QR code generated successfully');
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate QR: ' . $e->getMessage());
        }
    }

    /**
     * Print QR code
     */
    public function print(CardAccount $cardAccount)
    {
        $card = $cardAccount;
        return view('ktvtc.finance.high-school.cards.print-qr', compact('card'));
    }

    /**
     * Download QR code
     */
    public function download(CardAccount $cardAccount)
    {
        if (!$cardAccount->qr_code || !Storage::disk('public')->exists($cardAccount->qr_code)) {
            return redirect()->back()->with('error', 'QR code not found');
        }

        return Storage::disk('public')->download(
            $cardAccount->qr_code,
            'qr-' . $cardAccount->card_number . '.png'
        );
    }

    /**
     * Bulk generate QR codes
     */
    public function bulkGenerate(Request $request)
    {
        $cardIds = $request->card_ids;
        if (empty($cardIds)) {
            return redirect()->back()->with('error', 'No cards selected');
        }

        $generated = 0;
        $failed = 0;

        foreach ($cardIds as $cardId) {
            $card = CardAccount::find($cardId);
            if ($card && $card->student) {
                try {
                    $this->qrService->generateQrCode($card, $card->student);
                    $generated++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }
        }

        return redirect()->back()
            ->with('success', "QR codes generated: $generated, Failed: $failed");
    }

    /**
     * Print QR code sheet
     */
    public function printSheet(Request $request)
    {
        $cardIds = $request->card_ids ?? $request->query('card_ids');
        if (empty($cardIds)) {
            return redirect()->back()->with('error', 'No cards selected');
        }

        $cards = CardAccount::with('student')
            ->whereIn('id', $cardIds)
            ->get();

        return view('ktvtc.finance.high-school.cards.qr-sheet', compact('cards'));
    }
}
