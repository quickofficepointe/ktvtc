@extends('ktvtc.finance.layouts.app')

@section('title', 'Inactive Cards')
@section('subtitle', 'Cards not used recently')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.card-reports.index') }}" class="text-gray-600 hover:text-primary">Card Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Inactive Cards</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div class="finance-card p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Inactive Cards</p>
            <p class="text-xl font-bold text-yellow-600">{{ number_format($cards->count()) }}</p>
            <p class="text-xs text-gray-500">Not used in {{ $days ?? 30 }} days</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Balance</p>
            <p class="text-xl font-bold text-blue-600">KES {{ number_format($cards->sum('balance'), 2) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Zero Balance</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($cards->where('balance', 0)->count()) }}</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="finance-card p-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Days Inactive</label>
                <select name="days" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="7" {{ request('days', 30) == 7 ? 'selected' : '' }}>7 days</option>
                    <option value="14" {{ request('days', 30) == 14 ? 'selected' : '' }}>14 days</option>
                    <option value="30" {{ request('days', 30) == 30 ? 'selected' : '' }}>30 days</option>
                    <option value="60" {{ request('days', 30) == 60 ? 'selected' : '' }}>60 days</option>
                    <option value="90" {{ request('days', 30) == 90 ? 'selected' : '' }}>90 days</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm">
                    <i class="fas fa-search mr-2"></i> Update
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="finance-card p-3 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Student</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Card Number</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Balance</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Last Used</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cards ?? [] as $card)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 text-center">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2">
                                <span class="font-medium">{{ $card->student_name }}</span>
                            </td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $card->card_number }}</td>
                            <td class="px-3 py-2 text-right font-bold text-sm
                                @if($card->balance > 500) text-green-600
                                @elseif($card->balance > 100) text-yellow-600
                                @elseif($card->balance > 0) text-orange-600
                                @else text-red-600 @endif">
                                KES {{ number_format($card->balance, 2) }}
                            </td>
                            <td class="px-3 py-2 text-sm">
                                @if($card->last_used_at)
                                    {{ $card->last_used_at->format('d M Y') }}
                                    <span class="text-xs text-gray-400">({{ $card->last_used_at->diffForHumans() }})</span>
                                @else
                                    <span class="text-gray-400">Never used</span>
                                @endif
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
                            <td class="px-3 py-2 text-center">
                                <button onclick="notifyParent({{ $card->id }})" class="text-yellow-600 hover:text-yellow-800" title="Notify Parent">
                                    <i class="fas fa-sms"></i>
                                </button>
                                <button onclick="deactivateCard({{ $card->id }})" class="text-red-600 hover:text-red-800 ml-1" title="Deactivate">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-gray-500">No inactive cards found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 border-t pt-3">
            {{ $cards->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    function notifyParent(cardId) {
        if (confirm('Send notification to parent about inactive card?')) {
            showLoading('Sending notification...');
            toastr.success('Notification sent successfully');
            hideLoading();
        }
    }

    function deactivateCard(cardId) {
        if (confirm('Deactivate this card?')) {
            showLoading('Deactivating...');
            toastr.success('Card deactivated successfully');
            hideLoading();
        }
    }
</script>
@endpush
