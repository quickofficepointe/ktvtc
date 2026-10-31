@extends('ktvtc.finance.layouts.app')

@section('title', 'KCB Payments')
@section('subtitle', 'Every M-Pesa payment KCB has notified us about, and where it went')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">KCB Payments</span>
</li>
@endsection

@php
    $tabs = [
        'attention'   => ['Needs attention', 'fa-exclamation-circle', 'text-red-600'],
        'credited'    => ['Credited', 'fa-check-circle', 'text-green-600'],
        'linked'      => ['Linked to sales', 'fa-link', 'text-blue-600'],
        'duplicate'   => ['Already recorded', 'fa-clone', 'text-gray-600'],
        'ignored'     => ['Set aside', 'fa-ban', 'text-gray-600'],
        'all'         => ['All', 'fa-list', 'text-gray-700'],
    ];
    $attentionCount = ($counts['unallocated']->n ?? 0) + ($counts['quarantined']->n ?? 0);
    $attentionTotal = ($counts['unallocated']->total ?? 0) + ($counts['quarantined']->total ?? 0);
    $tillLabel = fn ($till) => match ($till) {
        $tills['school_fees'] => 'School fees',
        $tills['cafeteria']   => 'Cafeteria / cards',
        default               => $till ?: '—',
    };
    $badge = [
        'credited'    => 'bg-green-100 text-green-800',
        'linked'      => 'bg-blue-100 text-blue-800',
        'unallocated' => 'bg-yellow-100 text-yellow-800',
        'quarantined' => 'bg-red-100 text-red-800',
        'ignored'     => 'bg-gray-100 text-gray-700',
        'duplicate'   => 'bg-gray-100 text-gray-700',
        'received'    => 'bg-gray-100 text-gray-700',
    ];
@endphp

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="finance-card p-4 border-red-200 bg-red-50">
            <p class="text-sm text-gray-600">Waiting for finance</p>
            <p class="text-2xl font-bold text-red-700">{{ number_format($attentionCount) }}</p>
            <p class="text-xs text-gray-500">KES {{ number_format($attentionTotal, 2) }}</p>
        </div>
        <div class="finance-card p-4 border-green-200 bg-green-50">
            <p class="text-sm text-gray-600">Credited automatically or by finance</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($counts['credited']->n ?? 0) }}</p>
            <p class="text-xs text-gray-500">KES {{ number_format($counts['credited']->total ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4 border-blue-200 bg-blue-50">
            <p class="text-sm text-gray-600">Linked to cafeteria sales / orders</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($counts['linked']->n ?? 0) }}</p>
            <p class="text-xs text-gray-500">KES {{ number_format($counts['linked']->total ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="finance-card p-4 space-y-4">
        <div class="flex flex-wrap gap-2">
            @foreach($tabs as $key => [$label, $icon, $color])
                <a href="{{ route('finance.kcb-payments.index', array_filter(['status' => $key, 'till' => request('till'), 'q' => request('q')])) }}"
                   class="px-3 py-1.5 rounded-lg text-sm border {{ $status === $key ? 'bg-primary text-white border-primary' : 'bg-white border-gray-200 hover:bg-gray-50' }}">
                    <i class="fas {{ $icon }} mr-1 {{ $status === $key ? '' : $color }}"></i>{{ $label }}
                    @if($key === 'attention' && $attentionCount)
                        <span class="ml-1 text-xs font-bold">({{ $attentionCount }})</span>
                    @endif
                </a>
            @endforeach
        </div>

        <form method="GET" class="flex flex-col sm:flex-row gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <select name="till" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Both tills</option>
                <option value="{{ $tills['school_fees'] }}" @selected(request('till') === $tills['school_fees'])>School fees ({{ $tills['school_fees'] }})</option>
                <option value="{{ $tills['cafeteria'] }}" @selected(request('till') === $tills['cafeteria'])>Cafeteria / cards ({{ $tills['cafeteria'] }})</option>
            </select>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Transaction ID, account, phone or name"
                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <button class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search mr-1"></i>Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3 px-3">Received</th>
                        <th class="py-3 px-3">Till</th>
                        <th class="py-3 px-3">Account typed</th>
                        <th class="py-3 px-3">Payer</th>
                        <th class="py-3 px-3 text-right">Amount</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-3 whitespace-nowrap">
                                {{ ($p->transaction_date ?? $p->created_at)->format('d M Y H:i') }}
                                <div class="text-xs text-gray-400">{{ $p->transaction_id }}</div>
                            </td>
                            <td class="py-3 px-3">{{ $tillLabel($p->till_number) }}</td>
                            <td class="py-3 px-3 font-mono text-xs">{{ $p->business_key ?? '—' }}</td>
                            <td class="py-3 px-3">
                                {{ $p->payer_name ?: '—' }}
                                <div class="text-xs text-gray-400">{{ $p->debit_msisdn }}</div>
                            </td>
                            <td class="py-3 px-3 text-right font-semibold whitespace-nowrap">KES {{ number_format($p->transaction_amount, 2) }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge[$p->status] ?? 'bg-gray-100' }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                                @if($p->is_historical)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-800">Recovered</span>
                                @endif
                                <div class="text-xs text-gray-500 mt-1 max-w-xs">{{ $p->status_reason }}</div>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('finance.kcb-payments.show', $p) }}"
                                   class="{{ $p->needsAttention() ? 'btn-primary text-white' : 'border border-gray-300 text-gray-700' }} px-3 py-1.5 rounded-lg text-xs whitespace-nowrap">
                                    {{ $p->needsAttention() ? 'Resolve' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-500">
                                <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                                <p>Nothing here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $payments->links() }}
    </div>
</div>
@endsection
