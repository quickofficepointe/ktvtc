@extends('ktvtc.cafeteria.layout.cafeterialayout')

@section('title', $supplier->supplier_name)
@section('subtitle', 'Supplier ' . $supplier->supplier_code)

@section('header-actions')
<a href="{{ route('cafeteria.suppliers.index') }}" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">All suppliers</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl shadow-sm border p-5 text-sm space-y-2 self-start">
        <h3 class="font-semibold text-gray-800 mb-2">Details</h3>
        <p class="flex justify-between gap-3"><span class="text-gray-500">Status</span><span>{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span></p>
        <p class="flex justify-between gap-3"><span class="text-gray-500">Contact</span><span>{{ $supplier->contact_person ?: '—' }}</span></p>
        <p class="flex justify-between gap-3"><span class="text-gray-500">Phone</span><span>{{ $supplier->phone ?: '—' }}</span></p>
        <p class="flex justify-between gap-3"><span class="text-gray-500">Email</span><span>{{ $supplier->email ?: '—' }}</span></p>
        <p class="flex justify-between gap-3"><span class="text-gray-500">KRA PIN</span><span>{{ $supplier->tax_number ?: '—' }}</span></p>
        <p class="flex justify-between gap-3"><span class="text-gray-500">Payment terms</span><span>{{ $supplier->payment_terms ?: '—' }}</span></p>
        <p class="flex justify-between gap-3"><span class="text-gray-500">Bank</span><span class="text-right">{{ trim($supplier->bank_name . ' ' . $supplier->bank_branch) ?: '—' }}<br>{{ $supplier->bank_account }}</span></p>
        @if($supplier->address)<p class="text-gray-600 pt-2">{{ $supplier->address }}</p>@endif
        @if($supplier->notes)<p class="text-gray-600 pt-2">{{ $supplier->notes }}</p>@endif
        <p class="text-xs text-gray-400 pt-2">Added {{ $supplier->created_at?->format('d M Y') }} {{ $supplier->creator ? 'by ' . $supplier->creator->name : '' }}</p>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-5 overflow-x-auto">
        <h3 class="font-semibold text-gray-800 mb-3">Purchase orders ({{ $supplier->purchaseOrders->count() }})</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">PO</th><th class="py-2">Ordered</th><th class="py-2">Status</th><th class="py-2 text-right">Total (KES)</th></tr></thead>
            <tbody>
                @forelse($supplier->purchaseOrders->sortByDesc('order_date') as $po)
                    <tr class="border-b">
                        <td class="py-2"><a href="{{ route('cafeteria.purchase-orders.show', $po) }}" class="text-blue-600 hover:underline">{{ $po->po_number }}</a></td>
                        <td class="py-2">{{ $po->order_date ? \Carbon\Carbon::parse($po->order_date)->format('d M Y') : '—' }}</td>
                        <td class="py-2">{{ ucfirst(str_replace('_', ' ', $po->status)) }}</td>
                        <td class="py-2 text-right">{{ number_format((float) $po->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-500">No purchase orders yet.</td></tr>
                @endforelse
            </tbody>
            @if($supplier->purchaseOrders->isNotEmpty())
                <tfoot><tr class="font-semibold"><td colspan="3" class="pt-3 text-right">Total ordered</td><td class="pt-3 text-right">{{ number_format((float) $supplier->purchaseOrders->sum('total_amount'), 2) }}</td></tr></tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
