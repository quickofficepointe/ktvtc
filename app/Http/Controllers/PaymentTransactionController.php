<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\Sale;
use App\Models\KcbBuniTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentTransactionController extends Controller
{
    /**
     * Display a listing of payment transactions.
     */
    public function index(Request $request)
    {
        $query = PaymentTransaction::with(['sale', 'sale.shop', 'sale.items.product'])
            ->latest();

        // Apply filters
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filtering
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->end_date);
                    }
                    break;
            }
        } else {
            // Default to today
            $query->whereDate('created_at', today());
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_number', 'like', '%' . $request->search . '%')
                  ->orWhere('mpesa_receipt', 'like', '%' . $request->search . '%')
                  ->orWhereHas('sale', function($sq) use ($request) {
                      $sq->where('invoice_number', 'like', '%' . $request->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                        ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $transactions = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStats($request);

        // Get sales data for charts
        $chartData = $this->getChartData($request);

        // Get source breakdown (online vs POS using sale_type and channel)
        $sourceBreakdown = $this->getSourceBreakdown($request);

        return view('ktvtc.cafeteria.payments.index', compact(
            'transactions',
            'stats',
            'chartData',
            'sourceBreakdown'
        ));
    }

    /**
     * Calculate payment statistics
     */
    private function calculateStats($request)
    {
        $query = PaymentTransaction::query();

        // Apply date filters
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'last_week':
                    $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->whereDate('created_at', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->whereDate('created_at', '<=', $request->end_date);
                    }
                    break;
            }
        } else {
            $query->whereDate('created_at', today());
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return [
            'total_amount' => $query->clone()->where('status', 'completed')->sum('amount'),
            'total_count' => $query->clone()->where('status', 'completed')->count(),
            'mpesa_amount' => $query->clone()->where('payment_method', 'mpesa')->where('status', 'completed')->sum('amount'),
            'mpesa_count' => $query->clone()->where('payment_method', 'mpesa')->where('status', 'completed')->count(),
            'cash_amount' => $query->clone()->where('payment_method', 'cash')->where('status', 'completed')->sum('amount'),
            'cash_count' => $query->clone()->where('payment_method', 'cash')->where('status', 'completed')->count(),
            'card_amount' => $query->clone()->where('payment_method', 'card')->where('status', 'completed')->sum('amount'),
            'card_count' => $query->clone()->where('payment_method', 'card')->where('status', 'completed')->count(),
            'pending_count' => $query->clone()->where('status', 'pending')->count(),
            'pending_amount' => $query->clone()->where('status', 'pending')->sum('amount'),
            'failed_count' => $query->clone()->where('status', 'failed')->count(),
            'failed_amount' => $query->clone()->where('status', 'failed')->sum('amount'),
        ];
    }

    /**
     * Get chart data for graphs
     */
    private function getChartData($request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : now()->subDays(30)->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');

        // Daily transaction amounts for the last 7 days
        $dailyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $amount = PaymentTransaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('amount');
            $dailyData[] = [
                'date' => now()->subDays($i)->format('M d'),
                'amount' => $amount,
                'count' => PaymentTransaction::whereDate('created_at', $date)->where('status', 'completed')->count()
            ];
        }

        // Payment method breakdown
        $methodBreakdown = [
            'mpesa' => PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_method', 'mpesa')
                ->where('status', 'completed')
                ->sum('amount'),
            'cash' => PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_method', 'cash')
                ->where('status', 'completed')
                ->sum('amount'),
            'card' => PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_method', 'card')
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        // Hourly breakdown for today
        $hourlyData = [];
        for ($hour = 7; $hour <= 18; $hour++) {
            $amount = PaymentTransaction::whereDate('created_at', today())
                ->whereTime('created_at', '>=', sprintf('%02d:00:00', $hour))
                ->whereTime('created_at', '<', sprintf('%02d:00:00', $hour + 1))
                ->where('status', 'completed')
                ->sum('amount');
            $hourlyData[] = [
                'hour' => sprintf('%02d:00', $hour),
                'amount' => $amount
            ];
        }

        return [
            'daily' => $dailyData,
            'method_breakdown' => $methodBreakdown,
            'hourly' => $hourlyData
        ];
    }

    /**
     * Get source breakdown (Online Orders vs POS Sales)
     * Using sale_type and channel columns from sales table
     */
    private function getSourceBreakdown($request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : now()->subDays(30)->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : now()->format('Y-m-d');

        // Online orders: sale_type = 'online' OR channel IN ('website', 'mobile_app')
        $onlineOrders = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where(function($sub) {
                    $sub->where('sale_type', 'online')
                        ->orWhereIn('channel', ['website', 'mobile_app']);
                });
            })
            ->sum('amount');

        $onlineCount = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where(function($sub) {
                    $sub->where('sale_type', 'online')
                        ->orWhereIn('channel', ['website', 'mobile_app']);
                });
            })
            ->count();

        // POS sales: sale_type = 'pos' OR channel = 'cafeteria'
        $posSales = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where(function($sub) {
                    $sub->where('sale_type', 'pos')
                        ->orWhere('channel', 'cafeteria');
                });
            })
            ->sum('amount');

        $posCount = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->whereHas('sale', function($q) {
                $q->where(function($sub) {
                    $sub->where('sale_type', 'pos')
                        ->orWhere('channel', 'cafeteria');
                });
            })
            ->count();

        $total = $onlineOrders + $posSales;

        return [
            'online' => [
                'amount' => $onlineOrders,
                'count' => $onlineCount,
                'percentage' => $total > 0 ? round(($onlineOrders / $total) * 100, 1) : 0
            ],
            'pos' => [
                'amount' => $posSales,
                'count' => $posCount,
                'percentage' => $total > 0 ? round(($posSales / $total) * 100, 1) : 0
            ]
        ];
    }

    /**
     * Show a single transaction
     */
    public function show(PaymentTransaction $paymentTransaction)
    {
        $paymentTransaction->load(['sale', 'sale.items.product', 'sale.shop']);
        return response()->json($paymentTransaction);
    }

    /**
     * Update transaction status
     */
    public function update(Request $request, PaymentTransaction $paymentTransaction)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,reversed',
            'notes' => 'nullable|string'
        ]);

        $paymentTransaction->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $paymentTransaction->notes,
            'completed_at' => $validated['status'] === 'completed' ? now() : $paymentTransaction->completed_at,
        ]);

        // Update related sale status if needed
        if ($paymentTransaction->sale && $validated['status'] === 'completed') {
            $paymentTransaction->sale->update([
                'payment_status' => 'paid',
                'payment_confirmed_at' => now()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Transaction updated']);
    }

    /**
     * Reverse a transaction
     */
    public function reverse(PaymentTransaction $paymentTransaction)
    {
        if ($paymentTransaction->status !== 'completed') {
            return response()->json(['error' => 'Only completed transactions can be reversed'], 422);
        }

        DB::beginTransaction();
        try {
            $paymentTransaction->update([
                'status' => 'reversed',
                'notes' => ($paymentTransaction->notes ? $paymentTransaction->notes . ' | ' : '') . 'Reversed on ' . now()->format('Y-m-d H:i:s')
            ]);

            if ($paymentTransaction->sale) {
                $paymentTransaction->sale->update([
                    'payment_status' => 'refunded'
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction reversed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Print receipt
     */
    public function printReceipt(PaymentTransaction $paymentTransaction)
    {
        $paymentTransaction->load(['sale', 'sale.items.product', 'sale.shop']);
        return view('ktvtc.cafeteria.payments.receipt', compact('paymentTransaction'));
    }

    /**
     * Export transactions to Excel/CSV
     */
    public function export(Request $request)
    {
        $query = PaymentTransaction::with(['sale'])
            ->where('status', 'completed');

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->get();

        $filename = 'payments_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');

        // Add headers
        fputcsv($handle, [
            'Transaction #', 'Sale Invoice', 'Customer', 'Phone', 'Payment Method',
            'Amount', 'M-Pesa Receipt', 'Status', 'Sale Type', 'Channel', 'Date'
        ]);

        foreach ($transactions as $transaction) {
            $sale = $transaction->sale;
            fputcsv($handle, [
                $transaction->transaction_number,
                $sale?->invoice_number ?? 'N/A',
                $sale?->customer_name ?? 'N/A',
                $transaction->phone_number ?? $sale?->customer_phone ?? 'N/A',
                ucfirst($transaction->payment_method),
                number_format($transaction->amount, 2),
                $transaction->mpesa_receipt ?? 'N/A',
                ucfirst($transaction->status),
                $sale?->sale_type ?? 'N/A',
                $sale?->channel ?? 'N/A',
                $transaction->created_at->format('Y-m-d H:i:s')
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent)
            ->withHeaders([
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
    }

    /**
     * Get today's stats for dashboard
     */
    public function todayStats()
    {
        $today = today();

        return response()->json([
            'total_amount' => PaymentTransaction::whereDate('created_at', $today)->where('status', 'completed')->sum('amount'),
            'total_count' => PaymentTransaction::whereDate('created_at', $today)->where('status', 'completed')->count(),
            'mpesa_amount' => PaymentTransaction::whereDate('created_at', $today)->where('payment_method', 'mpesa')->where('status', 'completed')->sum('amount'),
            'cash_amount' => PaymentTransaction::whereDate('created_at', $today)->where('payment_method', 'cash')->where('status', 'completed')->sum('amount'),
            'online_count' => PaymentTransaction::whereDate('created_at', $today)
                ->whereHas('sale', function($q) {
                    $q->where('sale_type', 'online')->orWhereIn('channel', ['website', 'mobile_app']);
                })
                ->where('status', 'completed')
                ->count(),
            'pos_count' => PaymentTransaction::whereDate('created_at', $today)
                ->whereHas('sale', function($q) {
                    $q->where('sale_type', 'pos')->orWhere('channel', 'cafeteria');
                })
                ->where('status', 'completed')
                ->count(),
        ]);
    }

    /**
     * Get MPesa transactions
     */
    public function mpesaTransactions(Request $request)
    {
        $transactions = KcbBuniTransaction::with(['sale'])
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Get transaction by sale ID
     */
    public function getBySaleId($saleId)
    {
        $transactions = PaymentTransaction::where('sale_id', $saleId)
            ->latest()
            ->get();

        return response()->json($transactions);
    }
}
