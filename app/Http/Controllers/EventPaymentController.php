<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\EventPaymentService;
use App\Models\Event;

class EventPaymentController extends Controller
{
    protected $paymentService;

    public function __construct(EventPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processEventPayment(Request $request, Event $event)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'parent_name' => 'required|string|max:255',
                'parent_contact' => 'required|string|max:20',
                'parent_email' => 'required|email',
                'number_of_people' => 'required|integer|min:1|max:10',
                'attendees' => 'required|array|min:1',
                'attendees.*.name' => 'required|string|max:255',
                'attendees.*.school' => 'required|string|max:255',
                'attendees.*.age' => 'required|integer|min:3|max:25',
            ]);

            Log::info('Event payment process started', [
                'event_id' => $event->id,
                'parent_email' => $request->parent_email
            ]);

            $result = $this->paymentService->processEventApplicationAndPayment(
                $request->all(),
                $event
            );

            if (isset($result['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);
            }

            if ($event->is_paid) {
                return response()->json([
                    'success' => true,
                    'message' => $result['payment']['message'] ?? 'Payment initiated successfully',
                    'application_id' => $result['application']->id,
                    'checkout_request_id' => $result['payment']['checkout_request_id'] ?? null,
                    'requires_payment' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'application_id' => $result['application']->id,
                'requires_payment' => false,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Event payment validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data. Please check your information.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Event payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your application. Please try again.',
            ], 500);
        }
    }

    public function paymentCallback(Request $request)
    {
        try {
            $callbackData = $request->all();

            $this->paymentService->handleEventPaymentCallback($callbackData);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);

        } catch (\Exception $e) {
            Log::error('Event payment callback processing failed', [
                'error' => $e->getMessage(),
                'callback_data' => $request->all()
            ]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Callback processing failed']);
        }
    }

    public function checkPaymentStatus(Request $request)
    {
        try {
            $request->validate([
                'checkout_request_id' => 'required|string'
            ]);

            $result = $this->paymentService->checkEventPaymentStatus($request->checkout_request_id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 404);
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Check event payment status failed', [
                'error' => $e->getMessage(),
                'checkout_request_id' => $request->checkout_request_id ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status'
            ], 500);
        }
    }
}
