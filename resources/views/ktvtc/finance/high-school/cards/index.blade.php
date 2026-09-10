@extends('ktvtc.finance.layouts.app')

@section('title', 'Card Management')
@section('subtitle', 'Manage all student cards')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('finance.dashboard') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            Dashboard
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Cards</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    {{-- Issue Card button - goes to create page --}}
    <a href="{{ route('cards.create') }}" class="bg-primary hover:bg-primary-dark text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-plus mr-2"></i> Issue Card
    </a>

    {{-- Generate QR for ALL cards (across ALL pages) --}}
    <button type="button" onclick="generateQrForAll()" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-qrcode mr-2"></i> Generate All QR
    </button>

    {{-- Download Dropdown --}}
    <div class="relative inline-block text-left" x-data="{ open: false }">
        <button @click="open = !open" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-download mr-2"></i> Download Cards
            <i class="fas fa-chevron-down ml-2 text-xs"></i>
        </button>
        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-56 bg-white rounded-lg shadow-lg border z-10 py-1">
            <button onclick="downloadCards('all')" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                <i class="fas fa-download mr-2 text-purple-600"></i> All Cards (ZIP)
            </button>
            <button onclick="downloadCards('class')" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                <i class="fas fa-users mr-2 text-blue-600"></i> By Class (ZIP)
            </button>
            <button onclick="downloadCards('selected')" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                <i class="fas fa-check-square mr-2 text-green-600"></i> Selected Cards (ZIP)
            </button>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="w-full max-w-full space-y-6 overflow-hidden">

    {{-- Stats Cards --}}
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

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('cards.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-4">
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

            <div class="lg:col-span-4 flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition font-semibold text-sm">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>

                <a href="{{ route('cards.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Cards Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="w-full overflow-x-auto">
            <table class="min-w-[1100px] w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 w-10">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Card #</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Admission</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">QR</th>
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

                            <td class="py-3 px-4">
                                @if($card->qr_code)
                                    <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i> Yes
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-400 rounded-full">
                                        <i class="fas fa-times-circle mr-1"></i> No
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('cards.show', $card) }}" class="text-primary hover:text-primary-dark transition" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('cards.edit', $card) }}" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($card->qr_code)
                                        <a href="{{ route('cards.download-qr', $card) }}" class="text-purple-600 hover:text-purple-800 transition" title="Download QR">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                    @else
                                        <button type="button" onclick="generateQr({{ $card->id }})" class="text-gray-400 hover:text-gray-600 transition" title="Generate QR">
                                            <i class="fas fa-qrcode"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('cards.download-pdf', $card) }}" class="text-red-600 hover:text-red-800 transition" title="Download PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-500">
                                <i class="fas fa-id-card text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-lg font-medium">No cards found</p>
                                <p class="text-sm text-gray-400 mt-1">Start by issuing a new card</p>

                                <a href="{{ route('cards.create') }}" class="mt-3 inline-block px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
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
                            <td colspan="3" class="py-2 px-4"></td>
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

    {{-- Bulk Actions --}}
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

{{-- Download Modal --}}
<div id="downloadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Download ID Cards</h3>
            <button onclick="closeDownloadModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="downloadForm" method="GET" action="{{ route('cards.download-bulk') }}">
            <input type="hidden" name="type" id="downloadType">
            <input type="hidden" name="card_ids" id="downloadCardIds">

            <div id="classSelector" class="hidden mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Select Class</label>
                <select name="class" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">Select a class...</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class }}">{{ $class }}</option>
                    @endforeach
                </select>
            </div>

            <div id="selectedInfo" class="hidden mb-4 text-sm text-gray-600">
                <p><span id="selectedCountDisplay">0</span> cards selected</p>
            </div>

            <div class="bg-blue-50 border border-blue-100 p-3 rounded-lg text-sm text-blue-700 mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                <span id="downloadMessage">All active cards will be downloaded as individual PDFs in a ZIP file.</span>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeDownloadModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                    <i class="fas fa-download mr-2"></i> Download ZIP
                </button>
            </div>
        </form>
    </div>
</div>

{{-- QR Modal --}}
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

    // ============================================
    // CHECKBOX HANDLING
    // ============================================

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

    // ============================================
    // GENERATE QR FUNCTIONS
    // ============================================

    function generateQr(cardId) {
        if (!confirm('Generate QR code for this card?')) {
            return;
        }
        showLoading('Generating QR code...');
        fetch(`/finance/cards/${cardId}/generate-qr`, {
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

    function generateQrForAll() {
        if (!confirm('Generate QR codes for ALL cards? This may take a while.')) {
            return;
        }

        const modal = document.getElementById('qrModal');
        const message = document.getElementById('qrModalMessage');

        if (modal && message) {
            message.textContent = 'Generating QR codes for all cards...';
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        showLoading('Generating QR codes for all...');

        fetch('/finance/cards/generate-all-qr', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
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

    // ============================================
    // DOWNLOAD FUNCTIONS
    // ============================================

    function downloadCards(type) {
        const modal = document.getElementById('downloadModal');
        const typeInput = document.getElementById('downloadType');
        const classSelector = document.getElementById('classSelector');
        const selectedInfo = document.getElementById('selectedInfo');
        const selectedCountDisplay = document.getElementById('selectedCountDisplay');
        const message = document.getElementById('downloadMessage');

        typeInput.value = type;

        if (type === 'class') {
            classSelector.classList.remove('hidden');
            selectedInfo.classList.add('hidden');
            message.textContent = 'All cards for the selected class will be downloaded as individual PDFs in a ZIP file.';
        } else if (type === 'selected') {
            classSelector.classList.add('hidden');
            selectedInfo.classList.remove('hidden');
            const selected = document.querySelectorAll('.row-checkbox:checked');
            const selectedIds = Array.from(selected).map(cb => cb.value);
            document.getElementById('downloadCardIds').value = JSON.stringify(selectedIds);
            selectedCountDisplay.textContent = selectedIds.length;
            if (selectedIds.length === 0) {
                toastr.warning('Please select at least one card first');
                return;
            }
            message.textContent = selectedIds.length + ' selected cards will be downloaded as individual PDFs in a ZIP file.';
        } else {
            classSelector.classList.add('hidden');
            selectedInfo.classList.add('hidden');
            message.textContent = 'All active cards in the school will be downloaded as individual PDFs in a ZIP file.';
        }
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDownloadModal() {
        document.getElementById('downloadModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // ============================================
    // MODAL FUNCTIONS
    // ============================================

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
            closeDownloadModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCount();
    });
</script>
@endpush