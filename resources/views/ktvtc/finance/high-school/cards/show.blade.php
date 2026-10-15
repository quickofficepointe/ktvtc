{{-- resources/views/ktvtc/finance/high-school/cards/show.blade.php --}}

@extends('ktvtc.finance.layouts.app')

@section('title', 'Card Details - ' . $card->card_number)
@section('subtitle', 'View and manage student card')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('cards.index') }}" class="text-gray-600 hover:text-primary">Cards</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">{{ $card->card_number }}</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('cards.print-qr', ['cardAccount' => $card, 'full' => 'true']) }}"
       target="_blank"
       class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-id-card mr-2"></i> Print ID Card
    </a>
    <a href="{{ route('cards.print-qr', $card) }}"
       target="_blank"
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-qrcode mr-2"></i> Print QR
    </a>
    <a href="{{ route('cards.edit', $card) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-edit mr-2"></i> Edit
    </a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- LEFT: Card Info -->
    <div class="lg:col-span-2 space-y-5">
        <!-- Student Info -->
        <div class="finance-card p-5">
            <div class="flex items-start">
                @php
                    $student = $card->student;
                    $photoUrl = $student && $student->profile_picture
                        ? asset('storage/' . $student->profile_picture)
                        : asset('images/default/avatar.png');
                @endphp
                <img src="{{ $photoUrl }}" alt="Student" class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-gray-800">{{ $card->student_name }}</h3>
                    <p class="text-sm text-gray-500">Admission: {{ $card->student_admission_number }}</p>
                    <p class="text-sm text-gray-500">Class: {{ $card->student_class }}</p>
                    <p class="text-sm text-gray-500">Card: <span class="font-mono">{{ $card->card_number }}</span></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t">
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
                <div>
                    <p class="text-xs text-gray-500">Last Used</p>
                    <p class="text-sm">{{ $card->last_used_at ? $card->last_used_at->format('d M Y, H:i') : 'Never' }}</p>
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

        <!-- ID Card Preview -->
        <div class="finance-card p-5">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-bold text-gray-800">Student ID Card</h4>
                <a href="{{ route('cards.print-qr', ['cardAccount' => $card, 'full' => 'true']) }}"
                   target="_blank"
                   class="text-primary hover:underline text-sm">
                    <i class="fas fa-external-link-alt mr-1"></i> Open Full View
                </a>
            </div>

            {{-- Card Preview - Scaled down --}}
            <div class="border rounded-lg overflow-hidden bg-white" style="max-width: 700px; margin: 0 auto;">
                <div style="transform: scale(0.65); transform-origin: top center; width: 100%;">
                    @include('ktvtc.finance.high-school.cards.partials.id-card-preview', ['card' => $card])
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('cards.print-qr', ['cardAccount' => $card, 'full' => 'true']) }}"
                   target="_blank"
                   class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                    <i class="fas fa-print mr-2"></i> Print Full ID Card
                </a>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="finance-card p-5">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-bold text-gray-800">Recent Transactions</h4>
                <a href="{{ route('cards.transactions', $card) }}" class="text-sm text-primary hover:underline">View All →</a>
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

    <!-- RIGHT: Balance & QR -->
    <div class="space-y-4">
        <!-- Balance -->
        <div class="finance-card p-5">
            <h4 class="font-bold text-gray-800 text-sm mb-3">Balance & Limits</h4>
            <div class="space-y-2">
                <div>
                    <p class="text-xs text-gray-500">Current Balance</p>
                    <p class="text-2xl font-bold text-green-600">KES {{ number_format($card->balance, 2) }}</p>

                    {{-- ✅ Adjust Balance trigger --}}
                    <button type="button" onclick="openAdjustModal()"
                            class="mt-2 text-xs text-blue-600 hover:text-blue-800 hover:underline">
                        <i class="fas fa-sliders-h mr-1"></i> Adjust Balance
                    </button>
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
                <div>
                    <p class="text-xs text-gray-500">Total Funded</p>
                    <p class="font-medium text-sm text-green-600">KES {{ number_format($card->total_funded ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Spent</p>
                    <p class="font-medium text-sm text-red-600">KES {{ number_format($card->total_spent ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        <div class="finance-card p-5">
            <h4 class="font-bold text-gray-800 text-sm mb-3">QR Code</h4>
            <div class="flex flex-col items-center">
                @if($card->qr_code && Storage::disk('public')->exists($card->qr_code))
                    <img src="{{ asset('storage/' . $card->qr_code) }}" alt="QR Code" class="w-32 h-32 object-contain border rounded-lg p-2">
                    <div class="flex flex-wrap justify-center gap-2 mt-2">
                        <a href="{{ route('cards.print-qr', $card) }}" target="_blank" class="text-primary hover:underline text-sm">
                            <i class="fas fa-print mr-1"></i> Print
                        </a>
                        <a href="{{ route('cards.download-qr', $card) }}" class="text-blue-600 hover:underline text-sm">
                            <i class="fas fa-download mr-1"></i> Download
                        </a>
                        <button onclick="regenerateQr()" class="text-yellow-600 hover:underline text-sm">
                            <i class="fas fa-sync mr-1"></i> Regenerate
                        </button>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-qrcode text-3xl text-gray-300 mb-2 block"></i>
                        <p class="text-gray-500 text-sm">No QR code generated</p>
                        <button onclick="generateQr()" class="mt-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                            <i class="fas fa-qrcode mr-2"></i> Generate QR
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ADJUST BALANCE MODAL
═══════════════════════════════════════════════════════════ --}}
<div id="adjustBalanceModal" class="hidden fixed inset-0 z-[1300] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeAdjustModal()"></div>

        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-sliders-h text-blue-600 mr-2"></i>
                    Adjust Card Balance
                </h3>
                <button type="button" onclick="closeAdjustModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 mb-4 text-sm">
                <p class="text-gray-600">
                    <strong>Student:</strong> {{ $card->student_name }}
                </p>
                <p class="text-gray-600">
                    <strong>Card:</strong> <span class="font-mono">{{ $card->card_number }}</span>
                </p>
                <p class="text-gray-600">
                    <strong>Current Balance:</strong>
                    <span class="font-bold text-green-600">
                        KES {{ number_format($card->balance, 2) }}
                    </span>
                </p>
            </div>

            <form method="POST" action="{{ route('cards.adjust-balance', $card) }}"
                  onsubmit="return confirmAdjust()">
                @csrf

                {{-- Type --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                        Adjustment Type
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="add" class="peer sr-only" checked>
                            <div class="border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 rounded-lg p-3 text-center transition">
                                <i class="fas fa-plus-circle text-green-600 block mb-1"></i>
                                <span class="text-sm font-semibold text-gray-700">Add</span>
                                <span class="block text-xs text-gray-500">Credit the card</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="subtract" class="peer sr-only">
                            <div class="border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 rounded-lg p-3 text-center transition">
                                <i class="fas fa-minus-circle text-red-600 block mb-1"></i>
                                <span class="text-sm font-semibold text-gray-700">Subtract</span>
                                <span class="block text-xs text-gray-500">Debit the card</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Amount --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                        Amount (KES)
                    </label>
                    <input type="number" name="amount" step="0.01" min="0.01"
                           required placeholder="0.00"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-lg font-bold">
                </div>

                {{-- Reason --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                        Reason (required for audit log)
                    </label>
                    <textarea name="reason" rows="3" required
                              maxlength="255"
                              placeholder="e.g., Reversing duplicate funding from 16 Oct — receipt UI94U61GVH"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        This reason is stored permanently in the card audit log.
                    </p>
                </div>

                {{-- Warning --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4 text-xs text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Warning:</strong> This directly changes the card balance and cannot be undone from the UI. Verify the amount and reason carefully.
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-3 border-t">
                    <button type="button" onclick="closeAdjustModal()"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-50 text-sm">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">
                        <i class="fas fa-check mr-2"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ============================================
    // EXISTING QR FUNCTIONS
    // ============================================
    function generateQr() {
        showLoading('Generating QR code...');
        fetch('{{ route("cards.generate-qr", $card) }}', {
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

    // ============================================
    // ADJUST BALANCE MODAL
    // ============================================
    function openAdjustModal() {
        const modal = document.getElementById('adjustBalanceModal');
        if (!modal) return;

        // Reset form to a clean state every time it opens
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Re-check the "add" radio by default
            const addRadio = form.querySelector('input[name="type"][value="add"]');
            if (addRadio) addRadio.checked = true;
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAdjustModal() {
        const modal = document.getElementById('adjustBalanceModal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function confirmAdjust() {
        const form = document.getElementById('adjustBalanceModal').querySelector('form');
        const type = form.querySelector('input[name="type"]:checked')?.value;
        const amount = form.querySelector('input[name="amount"]')?.value;
        const reason = form.querySelector('textarea[name="reason"]')?.value;

        if (!amount || parseFloat(amount) <= 0) {
            alert('Please enter a valid amount greater than 0.');
            return false;
        }

        if (!reason || reason.trim().length === 0) {
            alert('Please provide a reason for this adjustment.');
            return false;
        }

        const verb = type === 'add' ? 'ADD' : 'SUBTRACT';
        const preposition = type === 'add' ? 'to' : 'from';

        return confirm(
            verb + ' KES ' + amount + ' ' + preposition + ' card {{ $card->card_number }}?\n\n' +
            'Reason: ' + reason + '\n\n' +
            'This will be logged in the audit trail.'
        );
    }

    // ============================================
    // ESCAPE KEY HANDLER
    // ============================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAdjustModal();
        }
    });
</script>
@endpush
@endsection
