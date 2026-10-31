@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', 'Sale ' . $sale->invoice_number)
@section('subtitle', ($sale->sale_date ?? $sale->created_at)?->format('d M Y H:i'))

@section('header-actions')
<a href="{{ url()->previous() }}" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">Back</a>
@endsection

@section('content')
@php
    $badge = fn ($status) => match ($status) {
        'paid', 'completed', 'delivered' => 'bg-green-100 text-green-800',
        'pending', 'processing' => 'bg-yellow-100 text-yellow-800',
        'failed', 'cancelled' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border p-4 overflow-x-auto">
            <h3 class="font-semibold text-gray-800 mb-3">Items ({{ $sale->items->count() }})</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Product</th><th class="py-2 text-right">Qty</th><th class="py-2 text-right">Unit price</th><th class="py-2 text-right">Total</th></tr></thead>
                <tbody>
                    @forelse($sale->items as $item)
                        <tr class="border-b">
                            <td class="py-2">{{ $item->product_name }} <span class="text-xs text-gray-400">{{ $item->product_code }}</span></td>
                            <td class="py-2 text-right">{{ rtrim(rtrim(number_format($item->quantity, 3), '0'), '.') }}</td>
                            <td class="py-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2 text-right">{{ number_format($item->final_price ?? $item->total_price, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-gray-500">No items recorded.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="text-sm">
                    <tr><td colspan="3" class="pt-3 text-right text-gray-500">Subtotal</td><td class="pt-3 text-right">{{ number_format($sale->subtotal, 2) }}</td></tr>
                    @if($sale->discount_amount > 0)<tr><td colspan="3" class="text-right text-gray-500">Discount</td><td class="text-right">-{{ number_format($sale->discount_amount, 2) }}</td></tr>@endif
                    @if($sale->tax_amount > 0)<tr><td colspan="3" class="text-right text-gray-500">Tax</td><td class="text-right">{{ number_format($sale->tax_amount, 2) }}</td></tr>@endif
                    @if($sale->delivery_fee > 0)<tr><td colspan="3" class="text-right text-gray-500">Delivery</td><td class="text-right">{{ number_format($sale->delivery_fee, 2) }}</td></tr>@endif
                    <tr class="font-semibold"><td colspan="3" class="pt-2 text-right">Total (KES)</td><td class="pt-2 text-right">{{ number_format($sale->total_amount, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-4 overflow-x-auto">
            <h3 class="font-semibold text-gray-800 mb-3">Payments</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Reference</th><th class="py-2">Method</th><th class="py-2">Status</th><th class="py-2 text-right">Amount</th><th class="py-2">Date</th></tr></thead>
                <tbody>
                    @forelse($sale->paymentTransactions as $payment)
                        <tr class="border-b">
                            <td class="py-2 font-mono text-xs">{{ $payment->mpesa_receipt ?: $payment->transaction_number }}</td>
                            <td class="py-2">{{ ucfirst($payment->payment_method) }}</td>
                            <td class="py-2"><span class="px-2 py-0.5 rounded text-xs {{ $badge($payment->status) }}">{{ $payment->status }}</span></td>
                            <td class="py-2 text-right">{{ number_format($payment->amount, 2) }}</td>
                            <td class="py-2 whitespace-nowrap">{{ $payment->created_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-gray-500">No payment records for this sale.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-4 text-sm space-y-2 self-start">
        <h3 class="font-semibold text-gray-800 mb-1">Summary</h3>
        <p class="flex justify-between"><span class="text-gray-500">Payment</span><span class="px-2 py-0.5 rounded text-xs {{ $badge($sale->payment_status) }}">{{ $sale->payment_status }}</span></p>
        <p class="flex justify-between"><span class="text-gray-500">Order</span><span class="px-2 py-0.5 rounded text-xs {{ $badge($sale->order_status) }}">{{ $sale->order_status }}</span></p>
        <p class="flex justify-between"><span class="text-gray-500">Method</span><span>{{ ucfirst($sale->payment_method ?? '—') }}</span></p>
        @if($sale->mpesa_receipt)<p class="flex justify-between"><span class="text-gray-500">M-Pesa receipt</span><span class="font-mono text-xs">{{ $sale->mpesa_receipt }}</span></p>@endif
        <p class="flex justify-between"><span class="text-gray-500">Channel</span><span>{{ $sale->channel ?? $sale->sale_type ?? '—' }}</span></p>
        <p class="flex justify-between"><span class="text-gray-500">Shop</span><span>{{ $sale->shop->shop_name ?? $sale->shop->name ?? '—' }}</span></p>
        <p class="flex justify-between"><span class="text-gray-500">Section</span><span>{{ $sale->businessSection->section_name ?? $sale->businessSection->name ?? '—' }}</span></p>
        <p class="flex justify-between"><span class="text-gray-500">Cashier</span><span>{{ $sale->cashier->name ?? '—' }}</span></p>
        <hr>
        <p class="flex justify-between"><span class="text-gray-500">Customer</span><span>{{ $sale->customer_name ?: 'Walk-in' }}</span></p>
        @if($sale->customer_phone)<p class="flex justify-between"><span class="text-gray-500">Phone</span><span>{{ $sale->customer_phone }}</span></p>@endif
        @if($sale->customer_notes)<p class="text-gray-600 pt-2">{{ $sale->customer_notes }}</p>@endif
        @if($sale->cancellation_reason)<p class="text-red-600 pt-2">Cancelled: {{ $sale->cancellation_reason }}</p>@endif
    </div>
</div>
@endsection
