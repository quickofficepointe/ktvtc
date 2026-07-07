@extends('ktvtc.finance.layouts.app')

@section('title', 'Card Details')
@section('subtitle', 'View card details and manage')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.cards.index') }}" class="text-gray-600 hover:text-primary">Cards</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">{{ $card->card_number }}</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('finance.cards.edit', $card) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-edit mr-2"></i> Edit
    </a>
    @if($card->is_active)
        <button onclick="toggleCard('deactivate')" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-pause mr-2"></i> Deactivate
        </button>
    @else
        <button onclick="toggleCard('activate')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-play mr-2"></i> Activate
        </button>
    @endif
    @if($card->is_locked)
        <button onclick="toggleCard('unlock')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-unlock mr-2"></i> Unlock
        </button>
    @else
        <button onclick="toggleCard('lock')" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-lock mr-2"></i> Lock
        </button>
    @endif
    @if($card->qr_code)
        <a href="{{ route('finance.cards.print-qr', $card) }}" target="_blank" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-print mr-2"></i> Print QR
        </a>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- Card Info -->
    <div class="lg:col-span-2 finance-card p-5">
        <div class="flex items-start">
            <div class="w-16 h-16 rounded-lg bg-primary/10 flex items-center justify-center">
                <i class="fas fa-credit-card text-3xl text-primary"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-bold text-gray-800">{{ $card->student_name }}</h3>
                <p class="text-sm text-gray-500">Admission: {{ $card->student_admission_number }}</p>
                <p class="text-sm text-gray-500">Class: {{ $card->student_class }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t">
            <div>
                <p class="text-xs text-gray-500">Card Number</p>
                <p class="font-mono font-semibold text-sm">{{ $card->card_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Account Number</p>
                <p class="font-mono font-semibold text-sm">{{ $card->account_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Issued At</p>
                <p class="text-sm">{{ $card->issued_at ? $card->issued_at->format('d M Y, H:i') : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    @if($card->is_blocked)
                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded">Blocked</span>
                    @endif
                    @if($card->is_locked)
                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-600 rounded">Locked</span>
                    @endif
                    @if($card->is_active)
                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-600 rounded">Active</span>
                    @else
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

        @if($card->blocked_reason)
            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-xs text-red-600 font-semibold">Blocked Reason</p>
                <p class="text-red-700 text-sm">{{ $card->blocked_reason }}</p>
                <p class="text-xs text-red-500 mt-1">Blocked at: {{ $card->blocked_at ? $card->blocked_at->format('d M Y, H:i') : 'N/A' }}</p>
            </div>
        @endif
    </div>

    <!-- Balance & Limits -->
    <div class="space-y-4">
        <div class="finance-card p-5">
            <h4 class="font-bold text-gray-800 text-sm mb-3">Balance & Limits</h4>
            <div class="space-y-2">
                <div>
                    <p class="text-xs text-gray-500">Current Balance</p>
                    <p class="text-2xl font-bold text-green-600">KES {{ number_format($card->balance, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Daily Limit</p>
                    <p class="font-medium text-sm">KES {{ number_format($card->daily_limit, 2) }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                        @php
                            $percentage = $card->daily_limit > 0 ? ($card->today_spent / $card->daily_limit) * 100 : 0;
                        @endphp
                        <div class="bg-primary h-1.5 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Today Spent: KES {{ number_format($card->today_spent ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Per Transaction Limit</p>
                    <p class="font-medium text-sm">KES {{ number_format($card->per_transaction_limit, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Low Balance Threshold</p>
                    <p class="font-medium text-sm">KES {{ number_format($card->low_balance_threshold, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        @if($card->qr_code)
            <div class="finance-card p-5">
                <h4 class="font-bold text-gray-800 text-sm mb-3">QR Code</h4>
                <div class="flex flex-col items-center">
                    <img src="{{ Storage::url($card->qr_code) }}" alt="QR Code" class="w-32 h-32 object-contain border rounded-lg p-2">
                    <div class="flex space-x-2 mt-2">
                        <a href="{{ route('finance.cards.print-qr', $card) }}" target="_blank" class="text-primary hover:underline text-sm">
                            <i class="fas fa-print mr-1"></i> Print
                        </a>
                        <a href="{{ route('finance.cards.download-qr', $card) }}" class="text-blue-600 hover:underline text-sm">
                            <i class="fas fa-download mr-1"></i> Download
                        </a>
                        <button onclick="regenerateQr()" class="text-yellow-600 hover:underline text-sm">
                            <i class="fas fa-sync mr-1"></i> Regenerate
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="finance-card p-5">
                <h4 class="font-bold text-gray-800 text-sm mb-3">QR Code</h4>
                <div class="text-center py-4">
                    <i class="fas fa-qrcode text-3xl text-gray-300 mb-2 block"></i>
                    <p class="text-gray-500 text-sm">No QR code generated</p>
                    <button onclick="generateQr()" class="mt-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                        <i class="fas fa-qrcode mr-2"></i> Generate QR
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Recent Transactions -->
    <div class="lg:col-span-3 finance-card p-5">
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-bold text-gray-800">Recent Transactions</h4>
            <a href="{{ route('finance.cards.transactions', $card) }}" class="text-sm text-primary hover:underline">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Date</th>
                        <th class="pb-2 font-semibold">Type</th>
                        <th class="pb-2 font-semibold text-right">Amount</th>
                        <th class="pb-2 font-semibold text-right">Balance After</th>
                        <th class="pb-2 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($card->transactions ?? [] as $transaction)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                            <td class="py-2">
                                <span class="text-xs px-2 py-0.5 rounded
                                    @if($transaction->transaction_type === 'funding') bg-green-100 text-green-600
                                    @elseif($transaction->transaction_type === 'purchase') bg-blue-100 text-blue-600
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ ucfirst($transaction->transaction_type) }}
                                </span>
                            </td>
                            <td class="py-2 text-right font-medium
                                @if($transaction->transaction_type === 'funding') text-green-600
                                @else text-red-600 @endif">
                                {{ $transaction->transaction_type === 'funding' ? '+' : '-' }}
                                KES {{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="py-2 text-right">KES {{ number_format($transaction->balance_after, 2) }}</td>
                            <td class="py-2">
                                <span class="text-xs px-2 py-0.5 rounded
                                    @if($transaction->status === 'completed') bg-green-100 text-green-600
                                    @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-600
                                    @else bg-red-100 text-red-600 @endif">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">No transactions yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function generateQr() {
        showLoading('Generating QR code...');
        fetch('{{ route("finance.cards.generate-qr", $card) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                toastr.success('QR code generated successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message || 'Failed to generate QR');
            }
        })
        .catch(error => {
            hideLoading();
            toastr.error('An error occurred');
        });
    }

    function regenerateQr() {
        if (confirm('Regenerate QR code? This will invalidate the current QR code.')) {
            generateQr();
        }
    }

    function toggleCard(action) {
        const actions = {
            'activate': { url: '{{ route("finance.cards.activate", $card) }}', msg: 'activate' },
            'deactivate': { url: '{{ route("finance.cards.deactivate", $card) }}', msg: 'deactivate' },
            'lock': { url: '{{ route("finance.cards.lock", $card) }}', msg: 'lock' },
            'unlock': { url: '{{ route("finance.cards.unlock", $card) }}', msg: 'unlock' }
        };

        if (!actions[action]) return;

        if (confirm(`Are you sure you want to ${actions[action].msg} this card?`)) {
            showLoading('Processing...');
            fetch(actions[action].url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    toastr.success(data.message || `Card ${actions[action].msg}ed successfully`);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(data.message || 'Failed to process');
                }
            })
            .catch(error => {
                hideLoading();
                toastr.error('An error occurred');
            });
        }
    }
</script>
@endpush
