<?php
// app/Http/Controllers/ParentFundingController.php

namespace App\Http\Controllers;

use App\Models\HighSchoolStudent;
use App\Models\CardAccount;
use App\Models\CardTransaction;
use App\Models\CardFundingRequest;
use App\Services\CardFundingService;
use App\Services\KcbSalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ParentFundingController extends Controller
{
    protected $cardFundingService;
    protected $kcbSalesService;

    public function __construct(
        CardFundingService $cardFundingService,
        KcbSalesService $kcbSalesService
    ) {
        $this->cardFundingService = $cardFundingService;
        $this->kcbSalesService = $kcbSalesService;
    }

    /**
     * Show the parent funding portal
     */
    public function showPortal($admissionNumber)
    {
        // Find student by admission number
        $student = HighSchoolStudent::where('admission_number', $admissionNumber)
            ->with('cardAccount')
            ->first();

        if (!$student) {
            abort(404, 'Student not found. Please check the admission number.');
        }

        // Get card account
        $card = $student->cardAccount;

        if (!$card) {
            abort(404, 'No card found for this student. Please contact the school.');
        }

        // Get recent transactions (last 10)
        $recentTransactions = CardTransaction::where('card_account_id', $card->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get funding statistics
        $totalFunded = CardTransaction::where('card_account_id', $card->id)
            ->where('transaction_type', 'funding')
            ->where('status', 'completed')
            ->sum('amount');

        $totalSpent = CardTransaction::where('card_account_id', $card->id)
            ->where('transaction_type', 'purchase')
            ->where('status', 'completed')
            ->sum('amount');

        // Get pending funding requests
        $pendingFunding = CardFundingRequest::where('card_account_id', $card->id)
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->first();

        return view('parent.funding-portal', compact(
            'student',
            'card',
            'recentTransactions',
            'totalFunded',
            'totalSpent',
            'pendingFunding'
        ));
    }

    /**
     * Fund Transport
     */
    public function fundTransport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:high_school_students,id',
            'amount' => 'required|numeric|min:10|max:50000',
            'phone_number' => 'required|string|regex:/^[0-9]{10,12}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $student = HighSchoolStudent::with('cardAccount')->find($request->student_id);
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        $card = $student->cardAccount;

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'No card found for this student.'
            ], 404);
        }

        // Initiate funding for Transport (T)
        $result = $this->cardFundingService->initiateFunding(
            $student,
            $card,
            $request->phone_number,
            $request->amount,
            'T' // Transport
        );

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment request sent! Check your phone for M-Pesa prompt.',
            'checkout_request_id' => $result['checkout_request_id'],
            'amount' => $request->amount,
            'funding_type' => 'Transport',
            'transaction_id' => $result['transaction']->id ?? null
        ]);
    }

    /**
     * Fund Canteen
     */
    public function fundCanteen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:high_school_students,id',
            'amount' => 'required|numeric|min:10|max:50000',
            'phone_number' => 'required|string|regex:/^[0-9]{10,12}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $student = HighSchoolStudent::with('cardAccount')->find($request->student_id);
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        $card = $student->cardAccount;

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'No card found for this student.'
            ], 404);
        }

        // Initiate funding for Canteen (C)
        $result = $this->cardFundingService->initiateFunding(
            $student,
            $card,
            $request->phone_number,
            $request->amount,
            'C' // Canteen
        );

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment request sent! Check your phone for M-Pesa prompt.',
            'checkout_request_id' => $result['checkout_request_id'],
            'amount' => $request->amount,
            'funding_type' => 'Canteen',
            'transaction_id' => $result['transaction']->id ?? null
        ]);
    }

    /**
     * Check funding status
     */
    public function checkStatus($checkoutRequestId)
    {
        $result = $this->cardFundingService->checkFundingStatus($checkoutRequestId);
        return response()->json($result);
    }

    /**
     * Get student balance via API
     */
    public function getBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admission_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $student = HighSchoolStudent::where('admission_number', $request->admission_number)
            ->with('cardAccount')
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        if (!$student->cardAccount) {
            return response()->json([
                'success' => false,
                'message' => 'No card found for this student.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student_name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'class' => $student->class,
                'transport_balance' => $student->cardAccount->balance,
                'canteen_balance' => $student->cardAccount->canteen_balance ?? 0,
                'total_funded' => $student->cardAccount->total_funded,
                'total_spent' => $student->cardAccount->total_spent,
                'card_status' => $student->cardAccount->is_active ? 'active' : 'inactive',
            ]
        ]);
    }
}