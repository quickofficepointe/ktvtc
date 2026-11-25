<?php

namespace App\Services\Payments;

use App\Models\KcbBuniTransaction;
use App\Models\KcbIpn;
use App\Models\Student;
use App\Services\KcbService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

/**
 * Staff collecting school fees by M-Pesa for a student, e.g. admissions at registration.
 * The prompt pays the school-fees till with the student's own account (7664166#<number>),
 * so KCB's notification credits the student, or holds it as a deposit until they are
 * enrolled. No money is ever recorded by hand.
 */
class FeePromptService
{
    public function __construct(private KcbService $kcb, private SmsService $sms) {}

    public function account(Student $student): string
    {
        return config('kcb.tills.school_fees') . '#' . $student->student_number;
    }

    /** @throws \DomainException */
    public function send(Student $student, string $phone, float $amount, int $userId): KcbBuniTransaction
    {
        $formatted = $this->kcb->validatePhoneNumber($phone);
        if (!$formatted) {
            throw new \DomainException('That phone number is not valid. Use the format 0712 345 678.');
        }
        $account = $this->account($student);

        // A prompt from the last 90 seconds may still be on that phone: a second one is refused by M-Pesa
        $recent = KcbBuniTransaction::where('invoice_number', $account)->where('transaction_type', 'school_fees')
            ->where('phone_number', $formatted)->where('status', 'initiated')
            ->where('created_at', '>=', now()->subSeconds(90))->latest('id')->first();
        if ($recent) {
            return $recent;
        }

        $response = $this->kcb->initiateStkPush($formatted, $amount, $account, route('kcb.fees.callback'), 'School Fees - ' . $student->student_number);
        if (isset($response['error'])) {
            Log::warning('Staff fee prompt failed', ['student_id' => $student->id, 'error' => $response['error']]);
            throw new \DomainException('M-Pesa did not accept the request: ' . (is_string($response['error']) ? $response['error'] : 'please try again') . '. You can text the payment details instead.');
        }

        return KcbBuniTransaction::create([
            'merchant_request_id' => $response['MerchantRequestID'] ?? $response['merchant_request_id'] ?? null,
            'checkout_request_id' => $response['CheckoutRequestID'] ?? $response['checkout_request_id'] ?? null,
            'phone_number'        => $formatted,
            'amount'              => $amount,
            'invoice_number'      => $account,
            'transaction_type'    => 'school_fees',
            'status'              => 'initiated',
            'request_data'        => json_encode(['student_id' => $student->id, 'student_number' => $student->student_number, 'sent_by' => $userId, 'source' => 'staff']),
            'response_data'       => json_encode($response),
        ]);
    }

    /**
     * Paid? KCB's payment notification is the proof (it is also what credits the student);
     * the PIN-prompt callback is used when it says the payer cancelled.
     *
     * @return array{state: string, message: string}
     */
    public function status(KcbBuniTransaction $tx): array
    {
        $ipn = KcbIpn::where('business_key', $tx->invoice_number)
            ->where('transaction_amount', $tx->amount)
            ->where('created_at', '>=', $tx->created_at->copy()->subMinute())
            ->whereIn('status', [KcbIpn::STATUS_CREDITED, KcbIpn::STATUS_HELD])
            ->latest('id')->first();

        if ($ipn) {
            return ['state' => 'paid', 'message' => 'Paid: KES ' . number_format((float) $ipn->transaction_amount) . ', receipt ' . $ipn->transaction_id
                . ($ipn->status === KcbIpn::STATUS_HELD ? '. Kept as a deposit until the student is enrolled.' : '. Added to the student\'s fees.')];
        }
        if ($tx->status === 'failed') {
            return ['state' => 'failed', 'message' => 'Not paid: ' . ($tx->result_description ?: 'the prompt was cancelled or timed out') . '.'];
        }
        if ($tx->status === 'completed') {
            return ['state' => 'confirming', 'message' => 'Paid on the phone (' . $tx->mpesa_receipt_number . '). Waiting for the bank to confirm...'];
        }
        if ($tx->created_at->lt(now()->subMinutes(3))) {
            return ['state' => 'failed', 'message' => 'No payment yet. The prompt has expired; send it again or text the payment details.'];
        }

        return ['state' => 'waiting', 'message' => 'Prompt sent to ' . $tx->phone_number . '. Waiting for the PIN...'];
    }

    public function textDetails(Student $student, string $phone): void
    {
        $text = "KTVTC fees for {$student->full_name}: pay by M-Pesa Paybill " . config('kcb.paybill')
            . ", Account " . $this->account($student) . ". Any amount is fine; you get an SMS when it is received.";
        $result = $this->sms->sendSingleSms($phone, $text);
        if (is_array($result) && isset($result['success']) && !$result['success']) {
            throw new \DomainException('The SMS could not be sent. Please give the details by hand: Paybill ' . config('kcb.paybill') . ', Account ' . $this->account($student) . '.');
        }
    }
}
