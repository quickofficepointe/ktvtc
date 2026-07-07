<?php

namespace App\Services;

use App\Models\HighSchoolStudent;
use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\CardDailyUsage;
use App\Models\CardAuditLog;
use App\Models\CardFundingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardService
{
    protected $qrService;

    /**
     * Inject the QR service
     */
    public function __construct(CardQrCodeService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Create a card for a student - WITH AUTO QR GENERATION
     */
    public function createCardForStudent(HighSchoolStudent $student, $dailyLimit = 500, $perTransactionLimit = 300)
    {
        DB::beginTransaction();

        try {
            $accountNumber = CardAccount::generateAccountNumber($student);
            $cardNumber = CardAccount::generateCardNumber();

            $card = CardAccount::create([
                'high_school_student_id' => $student->id,
                'account_number' => $accountNumber,
                'card_number' => $cardNumber,
                'balance' => 0,
                'total_funded' => 0,
                'total_spent' => 0,
                'is_active' => true,
                'is_locked' => false,
                'is_blocked' => false,
                'daily_limit' => $dailyLimit,
                'per_transaction_limit' => $perTransactionLimit,
                'low_balance_threshold' => 100,
                'minimum_balance' => 0,
                'student_name' => $student->full_name,
                'student_class' => $student->class,
                'student_admission_number' => $student->admission_number,
                'student_photo' => $student->profile_picture,
                'issued_at' => now(),
                'issued_by' => auth()->id(),
            ]);

            // ✅ AUTO-GENERATE QR CODE RIGHT AFTER CARD CREATION
            $qrResult = $this->qrService->generateQrCode($card, $student);

            if (!$qrResult['success']) {
                Log::warning('QR code generation failed during card creation', [
                    'card_id' => $card->id,
                    'error' => $qrResult['message']
                ]);
                // Don't throw exception - card creation should succeed even if QR fails
            }

            // Log audit
            CardAuditLog::log(
                $card->id,
                'create',
                'Card issued for ' . $student->full_name . ' (QR: ' . ($qrResult['success'] ? 'Generated' : 'Failed') . ')',
                null,
                $card->card_number,
                ['student_id' => $student->id]
            );

            DB::commit();

            Log::info('Card created for student', [
                'student_id' => $student->id,
                'card_number' => $card->card_number,
                'account_number' => $card->account_number,
                'qr_generated' => $qrResult['success']
            ]);

            return $card;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create card', [
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // ... keep all your other existing methods (processPurchase, adjustBalance, completeFunding, triggerLowBalanceAlert) ...
}
