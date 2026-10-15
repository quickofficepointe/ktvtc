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

    {{-- Issue Card --}}
    <a href="{{ route('cards.create') }}"
       class="bg-primary hover:bg-primary-dark text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-plus mr-2"></i> Issue Card
    </a>

    {{-- Generate All QR --}}
    <button type="button" onclick="generateQrForAll()"
            class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-qrcode mr-2"></i> Generate All QR
    </button>

    {{-- Bulk PDF (A4) --}}
    <button type="button" onclick="openBulkPdfModal()"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-layer-group mr-2"></i> Bulk PDF (A4)
    </button>
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
                            <td colspan="5" class="py-2 px-4 font-bold text-gray-800">Total (this page)</td>
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
        <div class="flex flex-col md:flex-row md:items-center gap-3 flex-wrap">
            <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>

            <button type="button" onclick="bulkGenerateQr()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-qrcode mr-2"></i> Generate QR
            </button>

            <button type="button" onclick="bulkPrint()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-print mr-2"></i> Print Cards
            </button>

            <button type="button" onclick="downloadSelected('fronts')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-file-pdf mr-2"></i> PDF Fronts
            </button>

            <button type="button" onclick="downloadSelected('backs')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-file-pdf mr-2"></i> PDF Backs
            </button>

            <button type="button" onclick="downloadSelected('front_back')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-file-pdf mr-2"></i> PDF Front+Back
            </button>

            <span class="text-xs text-gray-500 md:ml-auto" id="selectedCountInfo">0 cards selected</span>
        </div>
    </div>
</div>

{{-- ================================================================
     BULK PDF MODAL — buttons instead of dropdown (no clipping)
================================================================ --}}
<div id="bulkPdfModal" class="hidden fixed inset-0 z-[1300] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeBulkPdfModal()"></div>

        <div class="relative bg-white rounded-xl max-w-2xl w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-layer-group text-emerald-600 mr-2"></i>
                    Bulk PDF Download
                </h3>
                <button type="button" onclick="closeBulkPdfModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <p class="text-sm text-gray-600 mb-5">
                Choose a layout and scope. Cards are <strong>3.5" × 2.0"</strong>.
            </p>

            {{-- Layout tabs --}}
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Layout</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" data-layout="fronts" onclick="selectLayout('fronts')"
                            class="layout-btn border-2 border-gray-200 rounded-lg p-3 text-center hover:border-emerald-500 transition">
                        <i class="fas fa-id-card text-lg text-emerald-600 block mb-1"></i>
                        <div class="text-xs font-bold">Fronts</div>
                        <div class="text-[10px] text-gray-500">10 / A4 page</div>
                    </button>
                    <button type="button" data-layout="backs" onclick="selectLayout('backs')"
                            class="layout-btn border-2 border-gray-200 rounded-lg p-3 text-center hover:border-blue-500 transition">
                        <i class="fas fa-file-alt text-lg text-blue-600 block mb-1"></i>
                        <div class="text-xs font-bold">Backs</div>
                        <div class="text-[10px] text-gray-500">10 / A4 page</div>
                    </button>
                    <button type="button" data-layout="front_back" onclick="selectLayout('front_back')"
                            class="layout-btn border-2 border-gray-200 rounded-lg p-3 text-center hover:border-indigo-500 transition">
                        <i class="fas fa-clone text-lg text-indigo-600 block mb-1"></i>
                        <div class="text-xs font-bold">Front + Back</div>
                        <div class="text-[10px] text-gray-500">5 / A4 page</div>
                    </button>
                </div>
            </div>

            {{-- Scope tabs --}}
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Scope</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="selectScope('all')"
                            class="scope-btn border-2 border-gray-200 rounded-lg p-3 text-center hover:border-emerald-500 transition">
                        <i class="fas fa-globe text-lg text-emerald-600 block mb-1"></i>
                        <div class="text-xs font-bold">All</div>
                        <div class="text-[10px] text-gray-500">Every active card</div>
                    </button>
                    <button type="button" onclick="selectScope('class')"
                            class="scope-btn border-2 border-gray-200 rounded-lg p-3 text-center hover:border-blue-500 transition">
                        <i class="fas fa-users text-lg text-blue-600 block mb-1"></i>
                        <div class="text-xs font-bold">By Class</div>
                        <div class="text-[10px] text-gray-500">One form only</div>
                    </button>
                    <button type="button" onclick="selectScope('selected')"
                            class="scope-btn border-2 border-gray-200 rounded-lg p-3 text-center hover:border-indigo-500 transition">
                        <i class="fas fa-check-square text-lg text-indigo-600 block mb-1"></i>
                        <div class="text-xs font-bold">Selected</div>
                        <div class="text-[10px] text-gray-500">From table checkboxes</div>
                    </button>
                </div>
            </div>

            {{-- Class selector (only shown when scope = class) --}}
            <div id="classOptionsPanel" class="hidden mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Select Class</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-56 overflow-y-auto p-1 border rounded-lg">
                    @forelse($classes ?? [] as $cls)
                        <button type="button" onclick="selectClass('{{ $cls }}')"
                                class="class-btn border-2 border-gray-200 rounded-lg px-3 py-2 text-sm font-medium hover:border-emerald-500 hover:bg-emerald-50 transition">
                            {{ $cls }}
                        </button>
                    @empty
                        <p class="text-sm text-gray-500 col-span-full text-center py-3">
                            No classes available
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Selected info (only when scope = selected) --}}
            <div id="selectedOptionsPanel" class="hidden mb-4">
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3 text-sm text-indigo-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span id="selectedInfoText">0 cards will be included.</span>
                </div>
            </div>

            {{-- Preview / Status --}}
            <div id="bulkStatus" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-3 border-t">
                <button type="button" onclick="closeBulkPdfModal()"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">
                    Cancel
                </button>
                <button type="button" id="bulkDownloadBtn" onclick="executeBulkDownload()"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i class="fas fa-download mr-2"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Download Modal (ZIP) --}}
<div id="downloadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Download ID Cards (ZIP)</h3>
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
    // ============================================
    // STATE
    // ============================================
    let selectedIds = [];
    let bulkLayout = null;    // 'fronts' | 'backs' | 'front_back'
    let bulkScope = null;     // 'all' | 'class' | 'selected'
    let bulkClass = null;     // string when scope = class

    const bulkPdfUrl = "{{ route('cards.bulk-pdf') }}";

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

        // Update bulk modal if open
        const infoText = document.getElementById('selectedInfoText');
        if (infoText) {
            infoText.textContent = selectedIds.length + ' cards will be included.';
        }
        refreshBulkDownloadBtn();
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateSelectedCount();
        }
    });

    // ============================================
    // BULK PDF MODAL
    // ============================================
    function openBulkPdfModal() {
        bulkLayout = null;
        bulkScope = null;
        bulkClass = null;

        // Reset visual state
        document.querySelectorAll('.layout-btn').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50', 'border-blue-500', 'bg-blue-50', 'border-indigo-500', 'bg-indigo-50');
            b.classList.add('border-gray-200');
        });
        document.querySelectorAll('.scope-btn').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50', 'border-blue-500', 'bg-blue-50', 'border-indigo-500', 'bg-indigo-50');
            b.classList.add('border-gray-200');
        });
        document.querySelectorAll('.class-btn').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50');
            b.classList.add('border-gray-200');
        });

        document.getElementById('classOptionsPanel').classList.add('hidden');
        document.getElementById('selectedOptionsPanel').classList.add('hidden');
        document.getElementById('bulkStatus').classList.add('hidden');
        document.getElementById('selectedInfoText').textContent = selectedIds.length + ' cards will be included.';

        const modal = document.getElementById('bulkPdfModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        refreshBulkDownloadBtn();
    }

    function closeBulkPdfModal() {
        document.getElementById('bulkPdfModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function selectLayout(layout) {
        bulkLayout = layout;
        document.querySelectorAll('.layout-btn').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50', 'border-blue-500', 'bg-blue-50', 'border-indigo-500', 'bg-indigo-50');
            b.classList.add('border-gray-200');
        });
        const btn = document.querySelector(`.layout-btn[data-layout="${layout}"]`);
        if (btn) {
            btn.classList.remove('border-gray-200');
            const color = layout === 'fronts' ? 'emerald' : (layout === 'backs' ? 'blue' : 'indigo');
            btn.classList.add(`border-${color}-500`, `bg-${color}-50`);
        }
        refreshBulkDownloadBtn();
    }

    function selectScope(scope) {
        bulkScope = scope;
        bulkClass = null;

        document.querySelectorAll('.scope-btn').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50', 'border-blue-500', 'bg-blue-50', 'border-indigo-500', 'bg-indigo-50');
            b.classList.add('border-gray-200');
        });

        // Highlight based on which scope button was clicked
        const btns = document.querySelectorAll('.scope-btn');
        const idx = scope === 'all' ? 0 : (scope === 'class' ? 1 : 2);
        if (btns[idx]) {
            btns[idx].classList.remove('border-gray-200');
            const color = scope === 'all' ? 'emerald' : (scope === 'class' ? 'blue' : 'indigo');
            btns[idx].classList.add(`border-${color}-500`, `bg-${color}-50`);
        }

        // Show/hide relevant panels
        document.getElementById('classOptionsPanel').classList.toggle('hidden', scope !== 'class');
        document.getElementById('selectedOptionsPanel').classList.toggle('hidden', scope !== 'selected');

        // Reset class button highlight when switching scope
        document.querySelectorAll('.class-btn').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50');
            b.classList.add('border-gray-200');
        });

        refreshBulkDownloadBtn();
    }

    function selectClass(cls) {
        bulkClass = cls;
        document.querySelectorAll('.class-btn').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50');
            b.classList.add('border-gray-200');
        });
        // Highlight the clicked button
        document.querySelectorAll('.class-btn').forEach(b => {
            if (b.textContent.trim() === cls) {
                b.classList.remove('border-gray-200');
                b.classList.add('border-emerald-500', 'bg-emerald-50');
            }
        });
        refreshBulkDownloadBtn();
    }

    function refreshBulkDownloadBtn() {
        const btn = document.getElementById('bulkDownloadBtn');
        if (!btn) return;

        let ready = !!bulkLayout && !!bulkScope;

        if (bulkScope === 'class' && !bulkClass) ready = false;
        if (bulkScope === 'selected' && selectedIds.length === 0) ready = false;

        btn.disabled = !ready;
    }

    function executeBulkDownload() {
        // Guard
        if (!bulkLayout || !bulkScope) {
            toastr.warning('Please pick a layout and scope');
            return;
        }
        if (bulkScope === 'class' && !bulkClass) {
            toastr.warning('Please pick a class');
            return;
        }
        if (bulkScope === 'selected' && selectedIds.length === 0) {
            toastr.warning('Please select cards from the table first');
            return;
        }

        // Build URL
        let url = bulkPdfUrl + '?mode=' + encodeURIComponent(bulkLayout);

        if (bulkScope === 'all') {
            url += '&scope=all';
        } else if (bulkScope === 'class') {
            url += '&scope=class&class=' + encodeURIComponent(bulkClass);
        } else {
            url += '&scope=selected';
            selectedIds.forEach(id => url += '&card_ids[]=' + encodeURIComponent(id));
        }

        // Show status
        const status = document.getElementById('bulkStatus');
        status.className = 'mb-4 p-3 rounded-lg text-sm bg-emerald-50 text-emerald-800 border border-emerald-200';
        status.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating PDF... please wait.';
        status.classList.remove('hidden');

        // Trigger download in hidden iframe so we stay on the page
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);

        // Optional: show success after a delay
        setTimeout(() => {
            status.className = 'mb-4 p-3 rounded-lg text-sm bg-emerald-50 text-emerald-800 border border-emerald-200';
            status.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Download started. Check your Downloads folder.';
        }, 3500);
    }

    // ============================================
    // BULK ACTIONS: SELECTED-ONLY DOWNLOAD
    // ============================================
    function downloadSelected(mode) {
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one card from the table');
            return;
        }
        let url = bulkPdfUrl + '?scope=selected&mode=' + encodeURIComponent(mode);
        selectedIds.forEach(id => url += '&card_ids[]=' + encodeURIComponent(id));

        showLoading('Preparing PDF...');
        // Use iframe to avoid full page nav
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);
        setTimeout(() => {
            hideLoading();
            toastr.success('Download started');
        }, 3000);
    }

    // ============================================
    // GENERATE QR
    // ============================================
    function generateQr(cardId) {
        if (!confirm('Generate QR code for this card?')) return;

        showLoading('Generating QR code...');
        fetch(`/finance/cards/${cardId}/generate-qr`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
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
            body: JSON.stringify({ card_ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                const msg = document.getElementById('qrModalMessage');
                if (msg) msg.textContent = data.message || 'QR codes generated successfully';
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
        if (!confirm('Generate QR codes for ALL cards? This may take a while.')) return;

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
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                const msg = document.getElementById('qrModalMessage');
                if (msg) msg.textContent = data.message || 'QR codes generated successfully';
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
    // ZIP DOWNLOAD (LEGACY)
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
            const ids = Array.from(selected).map(cb => cb.value);
            document.getElementById('downloadCardIds').value = JSON.stringify(ids);
            selectedCountDisplay.textContent = ids.length;
            if (ids.length === 0) {
                toastr.warning('Please select at least one card first');
                return;
            }
            message.textContent = ids.length + ' selected cards will be downloaded as individual PDFs in a ZIP file.';
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
    // MODAL HELPERS
    // ============================================
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('qrModal');
            closeBulkPdfModal();
            closeDownloadModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCount();
    });
</script>
@endpush