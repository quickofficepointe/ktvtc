@extends('ktvtc.finance.layouts.app')

@section('title', 'M-Pesa Prompts')
@section('subtitle', 'Every M-Pesa PIN prompt the system has sent to a phone, and whether it was paid')

@php
    $types = [
        'school_fees'        => 'School fees',
        'application_fee'    => 'Application fee',
        'event_registration' => 'Event fee',
        'sale_payment'       => 'Cafeteria / card top-up',
    ];
    $badge = [
        'completed' => 'bg-green-100 text-green-800',
        'initiated' => 'bg-yellow-100 text-yellow-800',
        'failed'    => 'bg-red-100 text-red-800',
    ];
    $label = ['completed' => 'Paid', 'initiated' => 'Waiting for PIN', 'failed' => 'Not paid'];
@endphp

@section('content')
<div class="space-y-6">
    <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded-xl p-4 text-sm">
        <i class="fas fa-info-circle mr-1"></i>
        These are prompts only. Money reaches a student or card when KCB confirms the payment; see
        <a href="{{ route('finance.kcb-payments.index') }}" class="font-semibold underline">KCB Payments</a>.
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Prompts sent</p>
            <p class="text-2xl font-bold text-primary">{{ number_format($stats['all']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Paid</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['completed']) }}</p>
            <p class="text-xs text-gray-500">KES {{ number_format($stats['paid_amount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Not paid (cancelled, wrong PIN, timed out)</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['failed']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Waiting for PIN</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['initiated']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('finance.transactions.mpesa') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">For</label>
                <select name="type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    <option value="">Everything</option>
                    @foreach($types as $k => $v)
                        <option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 block mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    <option value="">Any</option>
                    @foreach($label as $k => $v)
                        <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <input type="text" name="search" placeholder="Phone, account or receipt" value="{{ request('search') }}" class="flex-1 min-w-0 px-3 py-2 text-sm border border-gray-300 rounded-lg">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Sent</th>
                        <th class="pb-2 font-semibold">For</th>
                        <th class="pb-2 font-semibold">Account</th>
                        <th class="pb-2 font-semibold">Phone</th>
                        <th class="pb-2 font-semibold text-right">Amount</th>
                        <th class="pb-2 font-semibold">M-Pesa receipt</th>
                        <th class="pb-2 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2 text-gray-600 whitespace-nowrap">{{ $t->created_at?->format('d M Y, H:i') }}</td>
                            <td class="py-2">{{ $types[$t->transaction_type] ?? ucfirst(str_replace('_', ' ', $t->transaction_type)) }}</td>
                            <td class="py-2 font-mono text-xs">{{ $t->invoice_number }}</td>
                            <td class="py-2">{{ $t->phone_number }}</td>
                            <td class="py-2 text-right font-semibold">KES {{ number_format($t->amount, 2) }}</td>
                            <td class="py-2 font-mono text-xs">{{ $t->mpesa_receipt_number ?: '-' }}</td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge[$t->status] ?? 'bg-gray-100 text-gray-700' }}"
                                      title="{{ $t->result_description }}">{{ $label[$t->status] ?? ucfirst($t->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-gray-500">No M-Pesa prompts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
