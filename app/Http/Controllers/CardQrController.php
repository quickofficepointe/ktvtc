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
     * Generate QR code for a single card
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
     * Generate QR codes for ALL active cards (across ALL pages)
     * This generates QR for every active card in the system
     */
    public function generateAll(Request $request)
    {
        $cards = CardAccount::with('student')
            ->where('is_active', true)
            ->get();

        if ($cards->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active cards found'
            ]);
        }

        $generated = 0;
        $failed = 0;
        $errors = [];

        foreach ($cards as $card) {
            if (!$card->student) {
                $failed++;
                $errors[] = "Card {$card->card_number}: No student found";
                continue;
            }

            try {
                $result = $this->qrService->generateQrCode($card, $card->student);
                if ($result['success']) {
                    $generated++;
                } else {
                    $failed++;
                    $errors[] = "Card {$card->card_number}: " . $result['message'];
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Card {$card->card_number}: " . $e->getMessage();
            }
        }

        $message = "QR codes generated: {$generated} successful, {$failed} failed.";

        return response()->json([
            'success' => true,
            'message' => $message,
            'generated' => $generated,
            'failed' => $failed,
            'errors' => $errors
        ]);
    }

    /**
     * Print QR code (single)
     */
    public function print(CardAccount $cardAccount)
    {
        $card = $cardAccount;
        return view('ktvtc.finance.high-school.cards.print-qr', compact('card'));
    }

    /**
     * Download QR code (single)
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
     * Bulk generate QR codes for selected cards
     */
    public function bulkGenerate(Request $request)
    {
        $cardIds = $request->card_ids;
        if (empty($cardIds)) {
            return redirect()->back()->with('error', 'No cards selected');
        }

        $generated = 0;
        $failed = 0;
        $errors = [];

        foreach ($cardIds as $cardId) {
            $card = CardAccount::with('student')->find($cardId);
            if ($card && $card->student) {
                try {
                    $result = $this->qrService->generateQrCode($card, $card->student);
                    if ($result['success']) {
                        $generated++;
                    } else {
                        $failed++;
                        $errors[] = "Card {$card->card_number}: " . $result['message'];
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Card {$card->card_number}: " . $e->getMessage();
                }
            } else {
                $failed++;
                $errors[] = "Card ID {$cardId}: No student found";
            }
        }

        $message = "QR codes generated: {$generated} successful, {$failed} failed.";

        return redirect()->back()
            ->with('success', $message)
            ->with('qr_errors', $errors);
    }

    /**
     * Print QR code sheet (bulk)
     */
    public function printSheet(Request $request)
    {
        $cardIds = $request->card_ids ?? $request->query('card_ids');
        if (empty($cardIds)) {
            return redirect()->back()->with('error', 'No cards selected');
        }

        $ids = is_array($cardIds) ? $cardIds : explode(',', $cardIds);
        $cards = CardAccount::with('student')
            ->whereIn('id', $ids)
            ->get();

        return view('ktvtc.finance.high-school.cards.qr-sheet', compact('cards'));
    }

    /**
     * Regenerate QR code for a single card
     */
    public function regenerate(CardAccount $cardAccount)
    {
        try {
            $student = $cardAccount->student;
            if (!$student) {
                return redirect()->back()->with('error', 'Student not found');
            }

            // Delete old QR if exists
            if ($cardAccount->qr_code && Storage::disk('public')->exists($cardAccount->qr_code)) {
                Storage::disk('public')->delete($cardAccount->qr_code);
            }

            $result = $this->qrService->generateQrCode($cardAccount, $student);

            if ($result['success']) {
                return redirect()->back()->with('success', 'QR code regenerated successfully');
            } else {
                return redirect()->back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to regenerate QR: ' . $e->getMessage());
        }
    }

    /**
     * Bulk regenerate QR codes
     */
    public function bulkRegenerate(Request $request)
    {
        $cardIds = $request->card_ids;
        if (empty($cardIds)) {
            return redirect()->back()->with('error', 'No cards selected');
        }

        $generated = 0;
        $failed = 0;
        $errors = [];

        foreach ($cardIds as $cardId) {
            $card = CardAccount::with('student')->find($cardId);
            if ($card && $card->student) {
                try {
                    // Delete old QR
                    if ($card->qr_code && Storage::disk('public')->exists($card->qr_code)) {
                        Storage::disk('public')->delete($card->qr_code);
                    }

                    $result = $this->qrService->generateQrCode($card, $card->student);
                    if ($result['success']) {
                        $generated++;
                    } else {
                        $failed++;
                        $errors[] = "Card {$card->card_number}: " . $result['message'];
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Card {$card->card_number}: " . $e->getMessage();
                }
            } else {
                $failed++;
                $errors[] = "Card ID {$cardId}: No student found";
            }
        }

        $message = "QR codes regenerated: {$generated} successful, {$failed} failed.";

        return redirect()->back()
            ->with('success', $message)
            ->with('qr_errors', $errors);
    }

    /**
     * Show QR code view
     */
    public function showQr(CardAccount $cardAccount)
    {
        $card = $cardAccount->load('student');
        return view('ktvtc.finance.high-school.cards.qr-show', compact('card'));
    }
}
