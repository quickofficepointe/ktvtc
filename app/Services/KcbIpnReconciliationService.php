<?php

namespace App\Services;

use App\Models\KcbIpn;
use App\Models\FeePayment;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KcbIpnReconciliationService
{
    /**
     * Auto-reconcile an IPN payment for school fees
     */
    public function reconcile(KcbIpn $ipn)
    {
        $invoiceNumber = $ipn->invoice_number;
        $transactionId = $ipn->transaction_id;
        $amount = $ipn->transaction_amount;

        Log::info('KCB IPN Reconciliation Started', [
            'invoice_number' => $invoiceNumber,
            'transaction_id' => $transactionId,
            'amount' => $amount
        ]);

        if (!$invoiceNumber) {
            return [
                'matched' => false,
                'notes' => 'No invoice number provided in IPN'
            ];
        }

        // Extract student number from invoice number: "7664166#FBOM/01653" -> "FBOM/01653"
        $studentNumber = $this->extractStudentNumber($invoiceNumber);

        if (!$studentNumber) {
            return [
                'matched' => false,
                'notes' => "Could not extract student number from invoice: {$invoiceNumber}"
            ];
        }

        Log::info('KCB IPN: Extracted student number', [
            'original' => $invoiceNumber,
            'student_number' => $studentNumber
        ]);

        // Find the student
        $student = Student::where('student_number', $studentNumber)->first();

        if (!$student) {
            return [
                'matched' => false,
                'notes' => "Student not found with number: {$studentNumber}"
            ];
        }

        // Find active enrollment with balance
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->where('balance', '>', 0)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$enrollment) {
            return [
                'matched' => false,
                'notes' => "No active enrollment with balance found for student: {$studentNumber}"
            ];
        }

        DB::beginTransaction();

        try {
            // Create fee payment record
            $receiptNumber = $this->generateReceiptNumber();

            $feePayment = FeePayment::create([
                'student_id' => $student->id,
                'enrollment_id' => $enrollment->id,
                'amount' => $amount,
                'payment_date' => now(),
                'receipt_number' => $receiptNumber,
                'payment_method' => 'kcb',
                'transaction_code' => $transactionId,
                'bill_reference_number' => $invoiceNumber,
                'payer_name' => $ipn->first_name . ' ' . $ipn->last_name,
                'payer_phone' => $ipn->debit_msisdn,
                'payer_type' => 'student',
                'status' => 'completed',
                'is_verified' => true,
                'verified_at' => now(),
                'import_source' => 'kcb_ipn',
                'notes' => "Auto-reconciled via KCB IPN. Transaction: {$transactionId}"
            ]);

            // Update enrollment
            $enrollment->amount_paid = $enrollment->amount_paid + $amount;
            $enrollment->balance = $enrollment->total_fees - $enrollment->amount_paid;
            $enrollment->save();

            DB::commit();

            Log::info('KCB IPN: Auto-reconciled successfully', [
                'payment_id' => $feePayment->id,
                'student_number' => $studentNumber,
                'amount' => $amount,
                'receipt' => $receiptNumber,
                'new_balance' => $enrollment->balance
            ]);

            return [
                'matched' => true,
                'record_type' => 'fee_payment',
                'record_id' => $feePayment->id,
                'notes' => "Auto-reconciled successfully. Student: {$studentNumber}, Amount: {$amount}, New Balance: {$enrollment->balance}"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KCB IPN Reconciliation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'matched' => false,
                'notes' => "Processing error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Extract student number from KCB invoice number
     * Format: "7664166#FBOM/01653" -> "FBOM/01653"
     */
    private function extractStudentNumber($invoiceNumber)
    {
        if (!$invoiceNumber) {
            return null;
        }

        // Remove till number prefix (7664166#)
        $studentNumber = preg_replace('/^7664166#/', '', $invoiceNumber);

        // Remove any # prefix
        $studentNumber = ltrim($studentNumber, '#');

        // Remove any trailing spaces
        $studentNumber = trim($studentNumber);

        return $studentNumber ?: null;
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $lastPayment = FeePayment::whereDate('created_at', today())->count();
        $sequence = str_pad($lastPayment + 1, 4, '0', STR_PAD_LEFT);
        return "KCB-{$year}{$month}{$day}-{$sequence}";
    }

    /**
     * Manual reconciliation by admin (if needed)
     */
    public function manualReconcile(KcbIpn $ipn, $recordType, $recordId, $notes = null)
    {
        // Only support fee_payment for manual reconciliation
        if ($recordType !== 'fee_payment') {
            return ['success' => false, 'error' => 'Only fee_payment type is supported'];
        }

        $feePayment = FeePayment::find($recordId);

        if (!$feePayment) {
            return ['success' => false, 'error' => 'Fee payment record not found'];
        }

        DB::beginTransaction();

        try {
            $feePayment->update([
                'status' => 'completed',
                'is_verified' => true,
                'verified_at' => now(),
                'transaction_code' => $ipn->transaction_id,
                'notes' => ($feePayment->notes ? $feePayment->notes . ' | ' : '') . 'Manually reconciled via KCB IPN. ' . ($notes ?? '')
            ]);

            // Update enrollment if linked
            if ($feePayment->enrollment_id) {
                $enrollment = Enrollment::find($feePayment->enrollment_id);
                if ($enrollment) {
                    $enrollment->amount_paid = $enrollment->amount_paid + $feePayment->amount;
                    $enrollment->balance = $enrollment->total_fees - $enrollment->amount_paid;
                    $enrollment->save();
                }
            }

            $ipn->markProcessed($recordType, $recordId, $notes ?? 'Manually reconciled by admin');

            DB::commit();

            return ['success' => true];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
