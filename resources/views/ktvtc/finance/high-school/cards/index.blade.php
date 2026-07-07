@extends('ktvtc.finance.layouts.app')

@section('title', 'Card Management')
@section('subtitle', 'Manage all student cards')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Cards</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">All Cards</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <a href="{{ route('finance.cards.create') }}" class="bg-primary hover:bg-primary-dark text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-plus mr-2"></i> Issue Card
    </a>

    <button type="button" onclick="bulkGenerateQr()" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-qrcode mr-2"></i> Bulk QR
    </button>

    <button type="button" onclick="bulkPrint()" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-print mr-2"></i> Print Cards
    </button>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500">Total Cards</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($totalCards ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 flex-shrink-0">
                    <i class="fas fa-id-card text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-100 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500">Active</p>
                    <p class="text-xl font-bold text-green-600 mt-1">{{ number_format($activeCards ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-yellow-100 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500">Locked</p>
                    <p class="text-xl font-bold text-yellow-600 mt-1">{{ number_format($lockedCards ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 flex-shrink-0">
                    <i class="fas fa-lock text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500">Blocked</p>
                    <p class="text-xl font-bold text-red-600 mt-1">{{ number_format($blockedCards ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                    <i class="fas fa-ban text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('finance.cards.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-5">
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Card #, Student, Account..."
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-semibold text-gray-600">Class</label>
                <select name="class" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class }}" {{ request('class') == $class ? 'selected' : '' }}>
                            {{ $class }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-semibold text-gray-600">Status</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>

            <div class="lg:col-span-3 flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition font-semibold text-sm">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>

                <a href="{{ route('finance.cards.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="w-full overflow-x-auto">
            <table class="min-w-[1100px] w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Card #</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Admission</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($cards ?? [] as $card)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary" value="{{ $card->id }}">
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-mono font-medium text-primary text-xs">
                                    {{ $card->card_number }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-medium text-gray-900 text-sm">
                                    {{ $card->student_name }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-sm text-gray-600">
                                {{ $card->student_admission_number }}
                            </td>

                            <td class="py-3 px-4 text-sm text-gray-600">
                                {{ $card->student_class }}
                            </td>

                            <td class="py-3 px-4 text-right font-bold text-sm
                                @if(($card->balance ?? 0) > 500) text-green-600
                                @elseif(($card->balance ?? 0) > 100) text-yellow-600
                                @else text-red-600 @endif">
                                KES {{ number_format($card->balance ?? 0, 2) }}
                            </td>

                            <td class="py-3 px-4">
                                @if($card->is_blocked)
                                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded-full">Blocked</span>
                                @elseif($card->is_locked)
                                    <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-600 rounded-full">Locked</span>
                                @elseif($card->is_active)
                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-600 rounded-full">Active</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">Inactive</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center gap-3 whitespace-nowrap">
                                    <a href="{{ route('finance.cards.show', $card) }}" class="text-primary hover:text-primary-dark transition" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('finance.cards.edit', $card) }}" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($card->qr_code)
                                        <a href="{{ route('finance.cards.download-qr', $card) }}" class="text-purple-600 hover:text-purple-800 transition" title="Download QR">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                    @else
                                        <button type="button" onclick="generateQr({{ $card->id }})" class="text-gray-400 hover:text-gray-600 transition" title="Generate QR">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-500">
                                <i class="fas fa-id-card text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-lg font-medium">No cards found</p>
                                <p class="text-sm text-gray-400 mt-1">Start by issuing a new card</p>

                                <a href="{{ route('finance.cards.create') }}" class="mt-3 inline-block px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                                    <i class="fas fa-plus mr-2"></i> Issue Card
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if(isset($cards) && $cards && $cards->count() > 0)
                    <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                        <tr>
                            <td colspan="5" class="py-2 px-4 font-bold text-gray-800">Total</td>
                            <td class="py-2 px-4 text-right font-bold text-gray-800">
                                KES {{ number_format($cards->sum('balance'), 2) }}
                            </td>
                            <td colspan="2" class="py-2 px-4"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if(isset($cards))
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 overflow-x-auto">
                {{ $cards->links() }}
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>

            <button type="button" onclick="bulkGenerateQr()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-qrcode mr-2"></i> Generate QR
            </button>

            <button type="button" onclick="bulkPrint()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-print mr-2"></i> Print Cards
            </button>

            <span class="text-xs text-gray-500 md:ml-auto" id="selectedCountInfo">0 cards selected</span>
        </div>
    </div>
</div>

<div id="qrModal" class="hidden fixed inset-0 z-[1200] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('qrModal')"></div>

        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Generate QR Codes</h3>

                <button type="button" onclick="closeModal('qrModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="qrModalContent">
                <p class="text-sm text-gray-600 mb-4" id="qrModalMessage">Processing...</p>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('qrModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let selectedIds = [];

    function toggleAll(checkbox) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });

        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        selectedIds = Array.from(checkboxes).map(cb => cb.value);

        const selectedCountInfo = document.getElementById('selectedCountInfo');
        if (selectedCountInfo) {
            selectedCountInfo.textContent = selectedIds.length + ' cards selected';
        }

        const selectAll = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('.row-checkbox');

        if (selectAll && allCheckboxes.length > 0) {
            selectAll.checked = selectedIds.length === allCheckboxes.length;
        }
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateSelectedCount();
        }
    });

    function generateQr(id) {
        if (!confirm('Generate QR code for this card?')) {
            return;
        }

        showLoading('Generating QR code...');

        fetch(`/finance/cards/${id}/generate-qr`, {
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
        .catch(() => {
            hideLoading();
            toastr.error('An error occurred');
        });
    }

    function bulkGenerateQr() {
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one card');
            return;
        }

        const modal = document.getElementById('qrModal');
        const message = document.getElementById('qrModalMessage');

        if (modal && message) {
            message.textContent = `Generating QR codes for ${selectedIds.length} card(s)...`;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        showLoading('Generating QR codes...');

        fetch('/finance/cards/bulk/generate-qr', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                card_ids: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success) {
                const message = document.getElementById('qrModalMessage');

                if (message) {
                    message.textContent = data.message || 'QR codes generated successfully';
                }

                toastr.success(data.message || 'QR codes generated');

                setTimeout(() => {
                    closeModal('qrModal');
                    location.reload();
                }, 2000);
            } else {
                toastr.error(data.message || 'Failed to generate QR codes');
                closeModal('qrModal');
            }
        })
        .catch(() => {
            hideLoading();
            toastr.error('An error occurred');
            closeModal('qrModal');
        });
    }

    function bulkPrint() {
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one card');
            return;
        }

        window.open(`/finance/cards/bulk/print-sheet?card_ids=${selectedIds.join(',')}`, '_blank');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);

        if (modal) {
            modal.classList.add('hidden');
        }

        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('qrModal');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCount();
    });
</script>
@endpush
