<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CafeteriaController extends Controller
{
    //
    public function dashboard()
    {
        return view('ktvtc.cafeteria.dashboard');
    }

    // Add this method to your CafeteriaController
    public function getStats()
    {
        try {
            Log::info('Cafeteria stats API called');

            $shopId = auth()->user()->shop_id ?? 1;
            $today = now()->format('Y-m-d');

            $todaySales = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $today)
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_amount') ?? 0;

            $transactionCount = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $today)
                ->where('payment_status', '!=', 'cancelled')
                ->count() ?? 0;

            $pendingOrders = Sale::where('shop_id', $shopId)
                ->whereDate('sale_date', $today)
                ->where('order_status', 'pending')
                ->count() ?? 0;

            $averageSale = $transactionCount > 0 ? $todaySales / $transactionCount : 0;

            return response()->json([
                'total_sales' => (float) $todaySales,
                'transaction_count' => (int) $transactionCount,
                'average_sale' => (float) $averageSale,
                'pending_orders' => (int) $pendingOrders
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching stats: ' . $e->getMessage());
            return response()->json([
                'total_sales' => 0,
                'transaction_count' => 0,
                'average_sale' => 0,
                'pending_orders' => 0
            ]);
        }
    }
}

