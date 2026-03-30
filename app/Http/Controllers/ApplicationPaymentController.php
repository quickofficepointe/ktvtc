<?php
// app/Http/Controllers/ApplicationPaymentController.php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\KcbBuniTransaction;
use App\Services\ApplicationKcbService; // Use the new service
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApplicationPaymentController extends Controller
{
    protected $applicationKcbService; // Changed from $kcbService
    protected $smsService;

    public function __construct(ApplicationKcbService $applicationKcbService, SmsService $smsService)
    {
        $this->applicationKcbService = $applicationKcbService; // Use ApplicationKcbService
        $this->smsService = $smsService;
    }

    /**
     * Show payment page for application
     */
    public function showPaymentForm($applicationId)
    {
        $application = Application::with(['course', 'campus'])->findOrFail($applicationId);

        // Check if already paid
        if ($this->applicationKcbService->hasCompletedPayment($application)) {
            return redirect()->route('application.success', $applicationId)
                ->with('success', 'Application fee already paid.');
        }

        return view('ktvtc.website.application.payment', compact('application'));
    }

    /**
     * Initiate payment
     */
    public function initiatePayment(Request $request, $applicationId)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        $application = Application::findOrFail($applicationId);

        // Check if already paid
        if ($this->applicationKcbService->hasCompletedPayment($application)) {
            return response()->json([
                'success' => false,
                'message' => 'Application fee already paid.'
            ]);
        }

        // Use ApplicationKcbService instead of direct KcbService
        $result = $this->applicationKcbService->initiateApplicationPayment(
            $application,
            $request->phone_number
        );

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'checkout_request_id' => $result['checkout_request_id'],
            'transaction_id' => $result['transaction']->id
        ]);
    }

    /**
     * Payment callback from KCB
     */
    public function paymentCallback(Request $request)
    {
        Log::info('Application payment callback received:', $request->all());

        try {
            $callbackData = $request->all();
            $result = $this->applicationKcbService->handleApplicationPaymentCallback($callbackData);

            if ($result) {
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            } else {
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Callback processing failed']);
            }

        } catch (\Exception $e) {
            Log::error('Application payment callback error: ' . $e->getMessage());
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Server error']);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'checkout_request_id' => 'required|string'
        ]);

        $result = $this->applicationKcbService->checkPaymentStatus($request->checkout_request_id);

        return response()->json($result);
    }
}
