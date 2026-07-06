<?php

namespace App\Http\Controllers;

use App\Models\CardFundingRequest;
use App\Models\CardAccount;
use App\Services\CardService;
use Illuminate\Http\Request;

class CardFundingRequestController extends Controller
{
    protected $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * List all funding requests
     */
    public function index(Request $request)
    {
        $query = CardFundingRequest::with('cardAccount.student');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('parent_phone', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%")
                  ->orWhere('student_name', 'like', "%{$search}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Stats
        $pending = CardFundingRequest::whereIn('status', ['pending', 'processing'])->count();
        $completed = CardFundingRequest::where('status', 'completed')->count();
        $failed = CardFundingRequest::where('status', 'failed')->count();

        return view('high-school.funding.index', compact('requests', 'pending', 'completed', 'failed'));
    }

    /**
     * Show funding request details
     */
    public function show(CardFundingRequest $cardFundingRequest)
    {
        $cardFundingRequest->load('cardAccount.student');
        return view('high-school.funding.show', compact('cardFundingRequest'));
    }

    /**
     * Mark funding request as completed
     */
    public function complete(Request $request, CardFundingRequest $cardFundingRequest)
    {
        $request->validate([
            'mpesa_receipt' => 'required|string'
        ]);

        if ($cardFundingRequest->status === 'completed') {
            return redirect()->back()->with('error', 'Funding already completed');
        }

        // Process completion
        $result = $this->cardService->completeFunding(
            $cardFundingRequest->checkout_request_id,
            [
                'transaction_id' => $cardFundingRequest->ipn_transaction_id ?? 'MANUAL-' . time(),
                'mpesa_receipt' => $request->mpesa_receipt,
                'fee_payment_id' => null
            ]
        );

        if ($result['success']) {
            return redirect()->route('high-school.funding.show', $cardFundingRequest)
                ->with('success', 'Funding completed successfully');
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Mark funding request as failed
     */
    public function fail(Request $request, CardFundingRequest $cardFundingRequest)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        if ($cardFundingRequest->status === 'completed') {
            return redirect()->back()->with('error', 'Cannot fail completed funding');
        }

        $cardFundingRequest->status = 'failed';
        $cardFundingRequest->failure_reason = $request->reason;
        $cardFundingRequest->save();

        return redirect()->route('high-school.funding.show', $cardFundingRequest)
            ->with('success', 'Funding marked as failed');
    }

    /**
     * Retry funding request
     */
    public function retry(CardFundingRequest $cardFundingRequest)
    {
        if (!$cardFundingRequest->canRetry()) {
            return redirect()->back()
                ->with('error', 'This request cannot be retried. Max retries reached or expired.');
        }

        $cardFundingRequest->incrementRetry();
        $cardFundingRequest->status = 'pending';
        $cardFundingRequest->save();

        // Re-initiate STK Push
        $kcbService = app(KcbCardFundingService::class);
        $result = $kcbService->initiateSTKPush(
            $cardFundingRequest->parent_phone,
            $cardFundingRequest->amount,
            'Card Funding for ' . $cardFundingRequest->student_name,
            $cardFundingRequest->id
        );

        if (isset($result['error'])) {
            $cardFundingRequest->status = 'failed';
            $cardFundingRequest->failure_reason = $result['error'];
            $cardFundingRequest->save();

            return redirect()->back()->with('error', 'Retry failed: ' . $result['error']);
        }

        $cardFundingRequest->checkout_request_id = $result['checkout_request_id'];
        $cardFundingRequest->kcb_invoice_number = $result['kcb_invoice_number'] ?? null;
        $cardFundingRequest->status = 'processing';
        $cardFundingRequest->save();

        return redirect()->route('high-school.funding.show', $cardFundingRequest)
            ->with('success', 'Retry initiated successfully');
    }

    /**
     * Pending funding requests
     */
    public function pending()
    {
        $requests = CardFundingRequest::whereIn('status', ['pending', 'processing'])
            ->with('cardAccount.student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('high-school.funding.pending', compact('requests'));
    }

    /**
     * Failed funding requests
     */
    public function failed()
    {
        $requests = CardFundingRequest::where('status', 'failed')
            ->with('cardAccount.student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('high-school.funding.failed', compact('requests'));
    }

    /**
     * Completed funding requests
     */
    public function completed()
    {
        $requests = CardFundingRequest::where('status', 'completed')
            ->with('cardAccount.student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('high-school.funding.completed', compact('requests'));
    }

    /**
     * Export funding requests
     */
    public function export(Request $request)
    {
        // TODO: Implement export
        return redirect()->back()->with('info', 'Export functionality coming soon');
    }
}
