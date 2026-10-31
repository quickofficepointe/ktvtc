<?php
// app/Http/Controllers/CafeteriaPreOrderController.php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\PreOrder;
use App\Models\PreOrderItem;
use App\Models\Product;
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

        // Status filter — defaults to "active" (paid, accepted, ready)
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

        // ✅ Date filter — OPT-IN only. Default = no date restriction.
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        // (Removed the default "today" filter that was hiding older orders.)

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

        // ✅ Counts — respect the same date filter if set, else count ALL (not just today)
        $dateFilter = $request->filled('date') ? $request->date : null;

        $countsQuery = function ($statuses) use ($dateFilter) {
            $q = PreOrder::query();
            if (is_array($statuses)) {
                $q->whereIn('status', $statuses);
            } else {
                $q->where('status', $statuses);
            }
            if ($dateFilter) {
                $q->whereDate('created_at', $dateFilter);
            }
            return $q->count();
        };

        $counts = [
            'pending'   => $countsQuery('pending'),
            'active'    => $countsQuery(['paid', 'accepted', 'ready']),
            'completed' => $countsQuery('completed'),
            'cancelled' => $countsQuery(['cancelled', 'rejected', 'failed']),
            'all'       => PreOrder::query()
                                ->when($dateFilter, fn($q) => $q->whereDate('created_at', $dateFilter))
                                ->count(),
        ];

        $toPrepare = $this->toPrepare();

        return view('ktvtc.cafeteria.pre-orders.index', compact('orders', 'counts', 'status', 'toPrepare'));
    }

    /**
     * Show one pre-order in detail.
     */
    public function show($id)
    {
        $order = PreOrder::with(['account', 'items.product', 'shop', 'acceptedBy'])
            ->findOrFail($id);

        return view('ktvtc.cafeteria.pre-orders.show', compact('order'));
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

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
                'staff_notes' => $request->input('staff_notes') ?: $order->staff_notes,
            ]);
            $this->takeOffStock($order);
        });

        return back()->with('success', 'Order marked as picked up.');
    }

    /**
     * What the kitchen has to make for paid orders not yet collected, per product,
     * and how much of it is not covered by stock on hand.
     */
    protected function toPrepare()
    {
        return PreOrderItem::query()
            ->join('pre_orders', 'pre_orders.id', '=', 'pre_order_items.pre_order_id')
            ->whereIn('pre_orders.status', ['paid', 'accepted'])
            ->groupBy('pre_order_items.product_id', 'pre_order_items.product_name')
            ->selectRaw('pre_order_items.product_id, pre_order_items.product_name, SUM(pre_order_items.quantity) AS quantity, COUNT(DISTINCT pre_orders.id) AS orders')
            ->orderByDesc('quantity')
            ->get()
            ->map(function ($row) {
                $product = Product::find($row->product_id);
                $stock = $product && $product->track_inventory ? max(0, (float) $product->current_stock) : null;
                $row->in_stock = $stock;
                $row->to_make = $stock === null ? (float) $row->quantity : max(0, (float) $row->quantity - $stock);
                return $row;
            });
    }

    /**
     * Collected items come off stock. Items made for the order (not in stock) can't take
     * stock below zero; the movement notes how many were made to order.
     */
    protected function takeOffStock(PreOrder $order): void
    {
        foreach ($order->items as $item) {
            $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
            if (!$product || !$product->track_inventory) {
                continue;
            }

            $onHand = max(0, (float) $product->current_stock);
            $fromStock = min($onHand, (float) $item->quantity);
            $madeToOrder = (float) $item->quantity - $fromStock;
            if ($fromStock <= 0) {
                continue;
            }

            InventoryMovement::create([
                'movement_number' => 'PRE-' . $order->order_number . '-' . $item->id,
                'product_id'      => $product->id,
                'shop_id'         => $order->shop_id,
                'movement_type'   => 'sale',
                'quantity'        => $fromStock,
                'unit'            => $product->unit ?? 'piece',
                'unit_cost'       => $product->cost_price,
                'total_cost'      => (float) $product->cost_price * $fromStock,
                'previous_stock'  => $product->current_stock,
                'new_stock'       => (float) $product->current_stock - $fromStock,
                'reference_number' => $order->order_number,
                'reference_type'  => 'pre_order',
                'reference_id'    => $order->id,
                'recorded_by'     => Auth::id(),
                'notes'           => "Pre-order {$order->order_number} collected" . ($madeToOrder > 0 ? "; {$madeToOrder} made to order" : ''),
            ]);
            $product->decrement('current_stock', $fromStock);
        }
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
                    app(\App\Services\Cards\CardWallet::class)->credit($card, \App\Services\Cards\CardWallet::CANTEEN, (float) $order->total_amount, 'refund', [
                        'reference' => \App\Models\CardTransaction::generateReference('RFND'),
                        'description' => 'Refund for rejected pre-order ' . $order->order_number,
                        'metadata' => ['pre_order_id' => $order->id],
                    ], Auth::id());
                }
            }

            $order->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by_user_id' => Auth::id(),
                'rejection_reason' => $request->reason,
            ]);

            DB::commit();

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

        return view('ktvtc.cafeteria.pre-orders.stats', compact('data'));
    }
}