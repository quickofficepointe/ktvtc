@extends('ktvtc.finance.layouts.app')

@section('title', 'Card Transactions')
@section('subtitle', 'All card transactions with M-Pesa codes and funding types')

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('card-reports.transactions.export', request()->query()) }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-csv mr-2"></i> Export CSV
    </a>
    <a href="{{ route('card-reports.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Reports
    </a>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500 p-4">
            <p class="text-xs font-medium text-gray-500">Total Transactions</p>
            <p class="text-xl font-bold text-blue-600 mt-1">{{ number_format($stats['total_count']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-100 border-l-4 border-l-green-500 p-4">
            <p class="text-xs font-medium text-gray-500">Total Funded</p>
            <p class="text-xl font-bold text-green-600 mt-1">KES {{ number_format($stats['total_funding'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-purple-100 border-l-4 border-l-purple-500 p-4">
            <p class="text-xs font-medium text-gray-500">Total Spent</p>
            <p class="text-xl font-bold text-purple-600 mt-1">KES {{ number_format($stats['total_spend'], 2) }}</p>
        </div>
        <a href="{{ route('card-reports.transactions', ['duplicates_only' => 1]) }}"
           class="bg-white rounded-xl shadow-sm border border-red-100 border-l-4 border-l-red-500 p-4 hover:bg-red-50 transition">
            <p class="text-xs font-medium text-gray-500">Suspected Duplicates</p>
            <p class="text-xl font-bold text-red-600 mt-1">{{ number_format($stats['duplicate_count']) }}</p>
            <p class="text-xs text-red-500 mt-1">Click to filter →</p>
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('card-reports.transactions') }}"
              class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

            <div class="md:col-span-4">
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="M-Pesa code, student, reference..."
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-600">Type</label>
                <select name="type" class="w-full px-3 py-2 border rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="funding"  {{ request('type') === 'funding'  ? 'selected' : '' }}>Funding</option>
                    <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="reversal" {{ request('type') === 'reversal' ? 'selected' : '' }}>Reversal</option>
                    <option value="refund"   {{ request('type') === 'refund'   ? 'selected' : '' }}>Refund</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-600">Funding Type</label>
                <select name="funding_type" class="w-full px-3 py-2 border rounded-lg text-sm">
                    <option value="">All</option>
                    <option value="T" {{ request('funding_type') === 'T' ? 'selected' : '' }}>Transport (T)</option>
                    <option value="C" {{ request('funding_type') === 'C' ? 'selected' : '' }}>Canteen (C)</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-600">From</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>

            <div class="md:col-span-2">
                <label class="text-xs font-semibold text-gray-600">To</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>

            <div class="md:col-span-12 flex flex-wrap gap-2 items-center">
                <label class="text-xs font-semibold text-gray-600 flex items-center gap-1">
                    <input type="checkbox" name="duplicates_only" value="1"
                           {{ request('duplicates_only') == '1' ? 'checked' : '' }}
                           class="rounded">
                    Show duplicates only
                </label>

                <button type="submit"
                        class="ml-auto px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm font-semibold">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('card-reports.transactions') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="w-full overflow-x-auto">
            <table class="min-w-[1100px] w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-3 text-left font-semibold text-gray-600">Date</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-600">Student</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-600">Card</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-600">Type</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-600">T / C</th>
                        <th class="px-3 py-3 text-right font-semibold text-gray-600">Amount</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-600">M-Pesa Code</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-600">Reference</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-600">Status</th>
                        <th class="px-3 py-3 text-center font-semibold text-gray-600">⚠ Dupe</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $t)
                        @php
                            $metadata = is_array($t->metadata)
                                ? $t->metadata
                                : json_decode($t->metadata ?? '{}', true);

                            $fundingType = $metadata['funding_type'] ?? null;
                            if (!$fundingType && preg_match('/#([TC])\d+$/', $t->reference ?? '', $m)) {
                                $fundingType = $m[1];
                            }
                            $isDuplicate = ($t->duplicate_sibling_count ?? 0) > 0;
                        @endphp

                        <tr class="hover:bg-gray-50 {{ $isDuplicate ? 'bg-red-50' : '' }}">
                            <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap">
                                {{ $t->created_at?->format('d M Y H:i:s') }}
                            </td>

                            <td class="px-3 py-2">
                                <div class="font-medium text-gray-900">
                                    {{ $t->cardAccount->student->full_name ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $t->cardAccount->student->admission_number ?? '' }}
                                </div>
                            </td>

                            <td class="px-3 py-2 font-mono text-xs text-gray-700">
                                {{ $t->cardAccount->card_number ?? '—' }}
                            </td>

                            <td class="px-3 py-2 text-center">
                                @if($t->transaction_type === 'funding')
                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">Funding</span>
                                @elseif($t->transaction_type === 'purchase')
                                    <span class="text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full">Purchase</span>
                                @elseif($t->transaction_type === 'reversal')
                                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full">Reversal</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">{{ ucfirst($t->transaction_type) }}</span>
                                @endif
                            </td>

                            <td class="px-3 py-2 text-center">
                                @if($fundingType === 'T')
                                    <span class="text-xs font-bold px-2 py-0.5 bg-blue-100 text-blue-700 rounded">T</span>
                                @elseif($fundingType === 'C')
                                    <span class="text-xs font-bold px-2 py-0.5 bg-orange-100 text-orange-700 rounded">C</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-3 py-2 text-right font-bold text-gray-800 whitespace-nowrap">
                                KES {{ number_format($t->amount, 2) }}
                            </td>

                            <td class="px-3 py-2 font-mono text-xs">
                                @if($t->mpesa_receipt)
                                    <span class="px-1.5 py-0.5 bg-gray-100 rounded">{{ $t->mpesa_receipt }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-3 py-2 font-mono text-xs text-gray-600">
                                {{ $t->reference ?? '—' }}
                            </td>

                            <td class="px-3 py-2 text-center">
                                @if($t->status === 'completed')
                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">Completed</span>
                                @elseif($t->status === 'reversed')
                                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full">Reversed</span>
                                @elseif($t->status === 'failed')
                                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full">Failed</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">{{ ucfirst($t->status) }}</span>
                                @endif
                            </td>

                            <td class="px-3 py-2 text-center">
                                @if($isDuplicate)
                                    <span class="text-xs px-2 py-0.5 bg-red-600 text-white rounded-full font-bold"
                                          title="This funding has a sibling from the other flow (STK vs IPN)">
                                        ⚠ DUPLICATE
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-gray-500">
                                <i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-lg font-medium">No transactions found</p>
                                <p class="text-sm text-gray-400 mt-1">Try adjusting your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($transactions))
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 overflow-x-auto">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
