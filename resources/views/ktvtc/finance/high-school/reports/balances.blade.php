@extends('ktvtc.finance.layouts.app')

@section('title', 'Balance Report')
@section('subtitle', 'View all card balances')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('card-reports.index') }}" class="text-gray-600 hover:text-primary">Card Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Balances</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
    <a href="{{ route('card-reports.export') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div class="finance-card p-3">
            <p class="text-xs text-gray-500">Total Cards</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($cards->total()) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Total Balance</p>
            <p class="text-xl font-bold text-green-600">KES {{ number_format($totalBalance ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Average Balance</p>
            <p class="text-xl font-bold text-blue-600">
                KES {{ number_format($cards->total() > 0 ? ($totalBalance ?? 0) / $cards->total() : 0, 2) }}
            </p>
        </div>
    </div>

    <!-- Table -->
    <div class="finance-card p-3 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Student</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Admission</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Class</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Card Number</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Balance</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cards ?? [] as $card)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-center">
                                {{ ($cards->currentPage() - 1) * $cards->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="font-medium">{{ $card->student_name }}</span>
                            </td>
                            <td class="px-3 py-2 text-sm">{{ $card->student_admission_number }}</td>
                            <td class="px-3 py-2 text-sm">{{ $card->student_class }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $card->card_number }}</td>
                            <td class="px-3 py-2 text-right font-bold text-sm
                                @if($card->balance > 500) text-green-600
                                @elseif($card->balance > 100) text-yellow-600
                                @elseif($card->balance > 0) text-orange-600
                                @else text-red-600 @endif">
                                KES {{ number_format($card->balance, 2) }}
                            </td>
                            <td class="px-3 py-2">
                                @if($card->is_blocked)
                                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded">Blocked</span>
                                @elseif($card->is_locked)
                                    <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-600 rounded">Locked</span>
                                @elseif($card->is_active)
                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-600 rounded">Active</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-gray-500">No cards found</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td colspan="5" class="px-3 py-2 font-bold">Page Total</td>
                        <td class="px-3 py-2 text-right font-bold">
                            KES {{ number_format($cards->sum('balance'), 2) }}
                        </td>
                        <td class="px-3 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-3 border-t pt-3">
            {{ $cards->links() }}
        </div>
    </div>
</div>
@endsection
