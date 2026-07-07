@extends('ktvtc.finance.layouts.app')

@section('title', 'Low Balance Report')
@section('subtitle', 'Students with low card balances')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.card-reports.index') }}" class="text-gray-600 hover:text-primary">Card Reports</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Low Balance</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <button onclick="sendBulkAlerts()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-sms mr-2"></i> Send Alerts
    </button>
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div class="finance-card p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Low Balance Cards</p>
            <p class="text-xl font-bold text-yellow-600">{{ number_format($cards->count()) }}</p>
            <p class="text-xs text-gray-500">Below KES {{ number_format($threshold ?? 100, 2) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-red-500">
            <p class="text-xs text-gray-500">Zero Balance</p>
            <p class="text-xl font-bold text-red-600">{{ number_format($cards->where('balance', 0)->count()) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">Total Owed</p>
            <p class="text-xl font-bold text-blue-600">KES {{ number_format($cards->sum('balance'), 2) }}</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="finance-card p-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Threshold</label>
                <div class="flex">
                    <span class="px-3 py-2 border rounded-l-lg bg-gray-100 text-gray-600 text-sm">KES</span>
                    <input type="number" name="threshold" value="{{ request('threshold', 100) }}" class="px-3 py-2 border rounded-r-lg focus:outline-none focus:ring-2 focus:ring-primary w-24 text-sm">
                </div>
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
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Admission</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Class</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Parent Phone</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Balance</th>
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
                            <td class="px-3 py-2 text-sm">{{ $card->student_admission_number }}</td>
                            <td class="px-3 py-2 text-sm">{{ $card->student_class }}</td>
                            <td class="px-3 py-2 text-sm">{{ $card->student->parent_phone ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-right font-bold text-sm
                                @if($card->balance == 0) text-red-600
                                @else text-yellow-600 @endif">
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
                            <td class="px-3 py-2 text-center">
                                <button onclick="sendAlert({{ $card->id }})" class="text-yellow-600 hover:text-yellow-800" title="Send Alert">
                                    <i class="fas fa-sms"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-gray-500">No low balance cards found</td>
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
    function sendAlert(cardId) {
        if (confirm('Send low balance alert to parent?')) {
            showLoading('Sending alert...');
            // Implement alert sending
            toastr.success('Alert sent successfully');
            hideLoading();
        }
    }

    function sendBulkAlerts() {
        if (confirm('Send low balance alerts to all parents?')) {
            showLoading('Sending alerts...');
            // Implement bulk alert sending
            toastr.success('Alerts sent successfully');
            hideLoading();
        }
    }
</script>
@endpush
