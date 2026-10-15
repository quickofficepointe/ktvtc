{{-- resources/views/ktvtc/finance/high-school/cards/transactions.blade.php --}}

@extends('ktvtc.finance.layouts.app')

@section('title', 'Transactions - ' . $cardAccount->card_number)
@section('subtitle', 'Transaction history for this card')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('cards.index') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            Cards
        </a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('cards.show', $cardAccount) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            {{ $cardAccount->card_number }}
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Transactions</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <button type="button" onclick="openAdjustModal()"
            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-sliders-h mr-2"></i> Adjust Balance
    </button>
    <a href="{{ route('cards.show', $cardAccount) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Card
    </a>
</div>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Summary Strip --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs font-medium text-gray-500">Current Balance</p>
            <p class="text-xl font-bold text-green-600 mt-1">
                KES {{ number_format($cardAccount->balance, 2) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs font-medium text-gray-500">Total Funded</p>
            <p class="text-xl font-bold text-blue-600 mt-1">
                KES {{ number_format($cardAccount->total_funded ?? 0, 2) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs font-medium text-gray-500">Total Spent</p>
            <p class="text-xl font-bold text-red-600 mt-1">
                KES {{ number_format($cardAccount->total_spent ?? 0, 2) }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs font-medium text-gray-500">Transactions</p>
            <p class="text-xl font-bold text-gray-800 mt-1">
                {{ $transactions->count() }}
            </p>
        </div>
    </div>

    {{-- Student Context --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-4">
            @php
                $student = $cardAccount->student;
                $photoUrl = $student && $student->profile_picture
                    ? asset('storage/' . $student->profile_picture)
                    : asset('images/default/avatar.png');
            @endphp
            <img src="{{ $photoUrl }}" alt="Student" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-800 text-sm">{{ $cardAccount->student_name }}</p>
                <p class="text-xs text-gray-500">
                    {{ $cardAccount->student_admission_number }} · {{ $cardAccount->student_class }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Card</p>
                <p class="font-mono text-sm font-semibold text-gray-700">{{ $cardAccount->card_number }}</p>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-bold text-gray-800">
                <i class="fas fa-history text-primary mr-2"></i>
                Transaction History
            </h3>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="min-w-[900px] w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Balance After</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt / Ref</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($transactions as $txn)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <p class="text-sm text-gray-800 font-medium">
                                    {{ $txn->created_at->format('d M Y') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $txn->created_at->format('H:i:s') }}
                                </p>
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($txn->transaction_type === 'funding')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">
                                        <i class="fas fa-arrow-down mr-1"></i> Funding
                                    </span>
                                @elseif($txn->transaction_type === 'purchase')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">
                                        <i class="fas fa-shopping-cart mr-1"></i> Purchase
                                    </span>
                                @elseif($txn->transaction_type === 'adjustment')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-medium">
                                        <i class="fas fa-sliders-h mr-1"></i> Adjustment
                                    </span>
                                @elseif($txn->transaction_type === 'refund')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">
                                        <i class="fas fa-undo mr-1"></i> Refund
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">
                                        {{ ucfirst($txn->transaction_type) }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4">
                                <p class="text-sm text-gray-700 line-clamp-2">
                                    {{ $txn->description ?? '—' }}
                                </p>
                            </td>

                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <span class="font-bold text-sm
                                    @if(in_array($txn->transaction_type, ['funding', 'refund']))
                                        text-green-600
                                    @elseif($txn->transaction_type === 'purchase')
                                        text-red-600
                                    @else
                                        text-gray-700
                                    @endif">
                                    {{ in_array($txn->transaction_type, ['funding', 'refund']) ? '+' : '-' }}
                                    KES {{ number_format($txn->amount, 2) }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-700">
                                    KES {{ number_format($txn->balance_after, 2) }}
                                </span>
                                <p class="text-xs text-gray-400">
                                    (was {{ number_format($txn->balance_before, 2) }})
                                </p>
                            </td>

                            <td class="py-3 px-4">
                                @if($txn->mpesa_receipt)
                                    <p class="font-mono text-xs text-gray-700">
                                        {{ $txn->mpesa_receipt }}
                                    </p>
                                @endif
                                @if($txn->reference && $txn->reference !== $txn->mpesa_receipt)
                                    <p class="font-mono text-[10px] text-gray-400">
                                        {{ $txn->reference }}
                                    </p>
                                @endif
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($txn->status === 'completed')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">
                                        <i class="fas fa-check-circle mr-1"></i> Completed
                                    </span>
                                @elseif($txn->status === 'pending')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-medium">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @elseif($txn->status === 'failed')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-medium">
                                        <i class="fas fa-times-circle mr-1"></i> Failed
                                    </span>
                                @elseif($txn->status === 'reversed')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 font-medium">
                                        <i class="fas fa-undo mr-1"></i> Reversed
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">
                                        {{ ucfirst($txn->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-500">
                                <i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-base font-medium">No transactions yet</p>
                                <p class="text-sm text-gray-400 mt-1">
                                    Transactions will appear here once the card is used.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ADJUST BALANCE MODAL (same as show page)
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
                    <strong>Student:</strong> {{ $cardAccount->student_name }}
                </p>
                <p class="text-gray-600">
                    <strong>Card:</strong> <span class="font-mono">{{ $cardAccount->card_number }}</span>
                </p>
                <p class="text-gray-600">
                    <strong>Current Balance:</strong>
                    <span class="font-bold text-green-600">
                        KES {{ number_format($cardAccount->balance, 2) }}
                    </span>
                </p>
            </div>

            <form method="POST" action="{{ route('cards.adjust-balance', $cardAccount) }}"
                  onsubmit="return confirmAdjust()">
                @csrf

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

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                        Amount (KES)
                    </label>
                    <input type="number" name="amount" step="0.01" min="0.01"
                           required placeholder="0.00"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-lg font-bold">
                </div>

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

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4 text-xs text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Warning:</strong> This directly changes the card balance and cannot be undone from the UI.
                </div>

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
    function openAdjustModal() {
        const modal = document.getElementById('adjustBalanceModal');
        if (!modal) return;

        const form = modal.querySelector('form');
        if (form) {
            form.reset();
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
            verb + ' KES ' + amount + ' ' + preposition + ' card {{ $cardAccount->card_number }}?\n\n' +
            'Reason: ' + reason + '\n\n' +
            'This will be logged in the audit trail.'
        );
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAdjustModal();
        }
    });
</script>
@endpush
@endsection
