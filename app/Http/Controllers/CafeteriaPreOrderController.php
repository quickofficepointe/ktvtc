<?php
// app/Http/Controllers/CafeteriaPreOrderController.php

namespace App\Http\Controllers;

use App\Models\PreOrder;
use App\Services\PreOrderSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CafeteriaPreOrderController extends Controller
{
    protected PreOrderSmsService $sms;

    public function __construct(PreOrderSmsService $sms)
    {
        $this->sms = $sms;
    }

    /**
     * List pre-orders, filterable by status / date / search.
     */
    public function index(Request $request)
    {
        $query = PreOrder::with(['account', 'items', 'shop'])
            ->orderBy('created_at', 'desc');

        // Status filter — defaults to "actionable" (paid, accepted, ready)
        $status = $request->get('status', 'active');

        if ($status === 'active') {
            $query->whereIn('status', ['paid', 'accepted', 'ready']);
        } elseif ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        } elseif ($status === 'cancelled') {
            $query->whereIn('status', ['cancelled', 'rejected', 'failed']);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->get('date_filter', 'today') === 'today') {
            $query->whereDate('created_at', today());
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('account', function ($a) use ($search) {
                      $a->where('display_name', 'like', "%{$search}%")
                        ->orWhere('identifier', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        // Counts for the tabs
        $counts = [
            'pending'   => PreOrder::where('status', 'pending')->whereDate('created_at', today())->count(),
            'active'    => PreOrder::whereIn('status', ['paid', 'accepted', 'ready'])->whereDate('created_at', today())->count(),
            'completed' => PreOrder::where('status', 'completed')->whereDate('created_at', today())->count(),
            'cancelled' => PreOrder::whereIn('status', ['cancelled', 'rejected', 'failed'])->whereDate('created_at', today())->count(),
        ];

        return view('cafeteria.pre-orders.index', compact('orders', 'counts', 'status'));
    }

    /**
     * Show one pre-order in detail.
     */
    public function show($id)
    {
        $order = PreOrder::with(['account', 'items.product', 'shop', 'acceptedBy'])
            ->findOrFail($id);

        return view('cafeteria.pre-orders.show', compact('order'));
    }

    /**
     * Accept a paid order.
     */
    public function accept(Request $request, $id)
    {
        $order = PreOrder::findOrFail($id);

        if (!$order->canBeAccepted()) {
            return back()->with('error', 'This order cannot be accepted right now (status: ' . $order->status_label . ').');
        }

        $order->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'accepted_by_user_id' => Auth::id(),
            'staff_notes' => $request->input('staff_notes') ?: $order->staff_notes,
        ]);

        Log::info('Pre-order accepted', [
            'order_number' => $order->order_number,
            'accepted_by' => Auth::id(),
        ]);

        return back()->with('success', 'Order accepted.');
    }

    /**
     * Mark an accepted order as ready for pickup.
     */
    public function markReady(Request $request, $id)
    {
        $order = PreOrder::findOrFail($id);

        if ($order->status !== 'accepted') {
            return back()->with('error', 'Only accepted orders can be marked ready.');
        }

        $order->update([
            'status' => 'ready',
            'ready_at' => now(),
            'staff_notes' => $request->input('staff_notes') ?: $order->staff_notes,
        ]);

        // Notify customer
        try {
            $this->sms->sendOrderReadyNotification($order);
        } catch (\Exception $e) {
            Log::error('Ready SMS failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Order marked ready. Customer notified.');
    }

    /**
     * Mark as picked up / completed.
     */
    public function markPickedUp(Request $request, $id)
    {
        $order = PreOrder::findOrFail($id);

        if (!in_array($order->status, ['ready', 'accepted'])) {
            return back()->with('error', 'Only ready or accepted orders can be marked picked up.');
        }

        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
            'staff_notes' => $request->input('staff_notes') ?: $order->staff_notes,
        ]);

        return back()->with('success', 'Order marked as picked up.');
    }

    /**
     * Reject an order (e.g. product unavailable).
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $order = PreOrder::findOrFail($id);

        if (in_array($order->status, ['completed', 'cancelled', 'rejected'])) {
            return back()->with('error', 'This order is already ' . $order->status_label . '.');
        }

        DB::beginTransaction();

        try {
            // Refund HS card if paid via card
            if ($order->payment_method === 'card' && $order->isPaid()) {
                $account = $order->account;
                $card = $account?->cardAccount();
                if ($card) {
                    $refund = (float) $order->total_amount;
                    $before = (float) $card->balance;
                    $card->balance = $before + $refund;
                    $card->total_spent = max(0, (float) $card->total_spent - $refund);
                    $card->save();

                    \App\Models\CardTransaction::create([
                        'card_account_id' => $card->id,
                        'high_school_student_id' => $account->user_id,
                        'transaction_type' => 'refund',
                        'amount' => $refund,
                        'balance_before' => $before,
                        'balance_after' => (float) $card->balance,
                        'reference' => \App\Models\CardTransaction::generateReference('RFND'),
                        'description' => 'Refund for rejected pre-order ' . $order->order_number,
                        'status' => 'completed',
                        'processed_at' => now(),
                    ]);
                }
            }

            $order->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by_user_id' => Auth::id(),
                'rejection_reason' => $request->reason,
            ]);

            DB::commit();

            // Notify customer
            try {
                $this->sms->sendOrderCancelledNotification($order, $request->reason);
            } catch (\Exception $e) {
                Log::error('Rejection SMS failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            Log::info('Pre-order rejected', [
                'order_number' => $order->order_number,
                'reason' => $request->reason,
                'by' => Auth::id(),
            ]);

            return back()->with('success', 'Order rejected. Customer notified.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pre-order rejection failed', ['order_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Failed to reject order.');
        }
    }

    /**
     * Stats page / JSON endpoint.
     */
    public function stats(Request $request)
    {
        $today = today();

        $data = [
            'today' => [
                'pending' => PreOrder::whereDate('created_at', $today)->where('status', 'pending')->count(),
                'paid' => PreOrder::whereDate('created_at', $today)->where('status', 'paid')->count(),
                'accepted' => PreOrder::whereDate('created_at', $today)->where('status', 'accepted')->count(),
                'ready' => PreOrder::whereDate('created_at', $today)->where('status', 'ready')->count(),
                'completed' => PreOrder::whereDate('created_at', $today)->where('status', 'completed')->count(),
                'revenue' => (float) PreOrder::whereDate('created_at', $today)
                    ->whereIn('status', ['paid', 'accepted', 'ready', 'completed'])
                    ->sum('total_amount'),
            ],
            'all_time' => [
                'total_orders' => PreOrder::count(),
                'total_revenue' => (float) PreOrder::whereIn('status', ['paid', 'accepted', 'ready', 'completed'])
                    ->sum('total_amount'),
            ],
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($data);
        }

        return view('cafeteria.pre-orders.stats', compact('data'));
    }
}