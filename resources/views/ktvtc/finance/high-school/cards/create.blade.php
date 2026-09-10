@extends('ktvtc.finance.layouts.app')

@section('title', 'Issue Cards')
@section('subtitle', 'Issue cards to students')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('finance.cards.index') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            Cards
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Issue Cards</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap justify-end gap-2">
    <button type="button" onclick="issueAllCards()" class="bg-primary hover:bg-primary-dark text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-credit-card mr-2"></i> Issue Cards to All
    </button>

    <button type="button" onclick="issueSelectedCards()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-check-double mr-2"></i> Issue to Selected
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
                    <p class="text-xs font-medium text-gray-500">Total Students</p>
                    <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($totalStudents ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 flex-shrink-0">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-100 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500">With Cards</p>
                    <p class="text-xl font-bold text-green-600 mt-1">{{ number_format($withCards ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
                    <i class="fas fa-id-card text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-yellow-100 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500">Without Cards</p>
                    <p class="text-xl font-bold text-yellow-600 mt-1">{{ number_format($withoutCards ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 flex-shrink-0">
                    <i class="fas fa-id-card text-lg opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-500">QR Generated</p>
                    <p class="text-xl font-bold text-blue-600 mt-1">{{ number_format($qrGenerated ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                    <i class="fas fa-qrcode text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('finance.cards.create') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-4">
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Name, Admission, Phone..."
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
                <label class="text-xs font-semibold text-gray-600">Card Status</label>
                <select name="card_status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All</option>
                    <option value="without_card" {{ request('card_status') == 'without_card' ? 'selected' : '' }}>Without Card</option>
                    <option value="with_card" {{ request('card_status') == 'with_card' ? 'selected' : '' }}>With Card</option>
                </select>
            </div>

            <div class="lg:col-span-4 flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition font-semibold text-sm">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>

                <a href="{{ route('finance.cards.create') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Students Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="w-full overflow-x-auto">
            <table class="min-w-[1100px] w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 w-10">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Admission</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Card Status</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">QR Status</th>
                        <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($students ?? [] as $student)
                        <tr class="hover:bg-gray-50 transition-colors" data-student-id="{{ $student->id }}">
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary" value="{{ $student->id }}">
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-mono text-xs text-gray-600">
                                    {{ $student->admission_number ?? 'N/A' }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    @if($student->profile_picture)
                                        <img src="{{ asset('storage/' . $student->profile_picture) }}"
                                             alt="{{ $student->full_name }}"
                                             class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <span class="font-medium text-gray-900 text-sm">{{ $student->full_name }}</span>
                                </div>
                            </td>

                            <td class="py-3 px-4 text-sm text-gray-600">
                                {{ $student->class ?? 'N/A' }}
                            </td>

                            <td class="py-3 px-4 text-sm text-gray-600">
                                {{ $student->parent_phone ?? 'N/A' }}
                            </td>

                            <td class="py-3 px-4 card-status">
                                @if($student->cardAccount)
                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-600 rounded-full">
                                        <i class="fas fa-id-card mr-1"></i> Has Card
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-600 rounded-full">
                                        <i class="fas fa-id-card mr-1"></i> No Card
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4">
                                @if($student->cardAccount && $student->cardAccount->qr_code)
                                    <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i> Generated
                                    </span>
                                @elseif($student->cardAccount)
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">
                                        <i class="fas fa-times-circle mr-1"></i> Pending
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-400 rounded-full">
                                        <i class="fas fa-minus mr-1"></i> N/A
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center gap-2 whitespace-nowrap">
                                    @if($student->cardAccount)
                                        <a href="{{ route('finance.cards.show', $student->cardAccount) }}" class="text-primary hover:text-primary-dark transition" title="View Card">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($student->cardAccount->qr_code)
                                            <a href="{{ route('finance.cards.download-qr', $student->cardAccount) }}" class="text-purple-600 hover:text-purple-800 transition" title="Download QR">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                        @else
                                            <button type="button" onclick="generateSingleQr({{ $student->cardAccount->id }})" class="text-gray-400 hover:text-gray-600 transition" title="Generate QR">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        @endif
                                    @else
                                        <button type="button" onclick="issueSingleCard({{ $student->id }})" class="text-green-600 hover:text-green-800 transition" title="Issue Card">
                                            <i class="fas fa-credit-card"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-500">
                                <i class="fas fa-users text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-lg font-medium">No students found</p>
                                <p class="text-sm text-gray-400 mt-1">Add a new student or adjust your filters</p>

                                <a href="{{ route('finance.hs-students.create') }}" class="mt-3 inline-block px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                                    <i class="fas fa-user-plus mr-2"></i> Add Student
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($students))
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 overflow-x-auto">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    {{-- Bulk Actions --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>

            <button type="button" onclick="issueSelectedCards()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-credit-card mr-2"></i> Issue to Selected
            </button>

            <button type="button" onclick="issueAllCards()" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-credit-card mr-2"></i> Issue to All
            </button>

            <button type="button" onclick="generateQrForSelected()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-qrcode mr-2"></i> Generate QR for Selected
            </button>

            <span class="text-xs text-gray-500 md:ml-auto" id="selectedCountInfo">0 students selected</span>
        </div>
    </div>
</div>

{{-- QR/Processing Modal --}}
<div id="qrModal" class="hidden fixed inset-0 z-[1200] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('qrModal')"></div>

        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Processing</h3>

                <button type="button" onclick="closeModal('qrModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="qrModalContent">
                <div class="flex items-center justify-center mb-4">
                    <div class="spinner-border animate-spin inline-block w-8 h-8 border-4 border-primary border-t-transparent rounded-full"></div>
                </div>
                <p class="text-sm text-gray-600 mb-4 text-center" id="qrModalMessage">Processing...</p>

                <div id="qrModalResult" class="hidden">
                    <div id="qrModalResultContent" class="text-sm"></div>
                </div>

                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" onclick="closeModal('qrModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">
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
            selectedCountInfo.textContent = selectedIds.length + ' students selected';
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
    // MODAL HELPERS
    // ============================================

    function showProcessingModal(message) {
        const modal = document.getElementById('qrModal');
        const msgEl = document.getElementById('qrModalMessage');
        const resultEl = document.getElementById('qrModalResult');
        const spinner = document.querySelector('.spinner-border');
        
        if (modal) modal.classList.remove('hidden');
        if (msgEl) {
            msgEl.textContent = message;
            msgEl.classList.remove('hidden');
        }
        if (resultEl) resultEl.classList.add('hidden');
        if (spinner) spinner.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function showResultModal(message, isSuccess = true) {
        const msgEl = document.getElementById('qrModalMessage');
        const resultEl = document.getElementById('qrModalResult');
        const resultContent = document.getElementById('qrModalResultContent');
        const spinner = document.querySelector('.spinner-border');
        
        if (spinner) spinner.classList.add('hidden');
        if (msgEl) msgEl.classList.add('hidden');
        if (resultEl) {
            resultEl.classList.remove('hidden');
            if (resultContent) {
                resultContent.innerHTML = message.replace(/\n/g, '<br>');
                resultContent.className = 'text-sm ' + (isSuccess ? 'text-green-600' : 'text-red-600');
            }
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
        document.body.style.overflow = 'auto';
        
        // Reset modal state
        const msgEl = document.getElementById('qrModalMessage');
        const resultEl = document.getElementById('qrModalResult');
        const spinner = document.querySelector('.spinner-border');
        
        if (msgEl) msgEl.classList.remove('hidden');
        if (resultEl) resultEl.classList.add('hidden');
        if (spinner) spinner.classList.remove('hidden');
    }

    // ============================================
    // ISSUE CARD FUNCTIONS
    // ============================================

    function issueSingleCard(studentId) {
        if (!confirm('Issue a card to this student?')) {
            return;
        }

        showLoading('Issuing card...');

        fetch('/finance/cards', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                student_id: studentId,
                daily_limit: 500,
                per_transaction_limit: 300
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                toastr.success(data.message || 'Card issued successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || 'Failed to issue card');
            }
        })
        .catch(() => {
            hideLoading();
            toastr.error('An error occurred');
        });
    }

    function issueSelectedCards() {
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one student');
            return;
        }

        // Filter students without cards
        const studentsWithoutCards = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
            const row = cb.closest('tr');
            const cardStatus = row.querySelector('.card-status')?.textContent?.trim() || '';
            if (!cardStatus.includes('Has Card')) {
                studentsWithoutCards.push(cb.value);
            }
        });

        if (studentsWithoutCards.length === 0) {
            toastr.warning('All selected students already have cards');
            return;
        }

        const totalSelected = selectedIds.length;
        const withCards = totalSelected - studentsWithoutCards.length;
        let confirmMsg = `Issue cards to ${studentsWithoutCards.length} students without cards?`;
        if (withCards > 0) {
            confirmMsg = `${withCards} selected students already have cards. ` + confirmMsg;
        }

        if (!confirm(confirmMsg)) {
            return;
        }

        showProcessingModal(`Issuing cards to ${studentsWithoutCards.length} students...`);

        fetch('/finance/cards/bulk/issue', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                student_ids: studentsWithoutCards
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            let message = data.message || 'Processing complete';
            if (data.errors && data.errors.length > 0) {
                message += '\n\nErrors:\n' + data.errors.join('\n');
            }
            showResultModal(message, data.success);
            
            if (data.success) {
                toastr.success(data.message || 'Cards issued successfully');
                setTimeout(() => {
                    closeModal('qrModal');
                    location.reload();
                }, 4000);
            } else {
                toastr.error(data.message || 'Failed to issue cards');
            }
        })
        .catch(error => {
            hideLoading();
            showResultModal('Error: ' + error.message, false);
            toastr.error('An error occurred');
        });
    }

    function issueAllCards() {
        const totalStudents = {{ $withoutCards ?? 0 }};
        if (totalStudents === 0) {
            toastr.info('All students already have cards');
            return;
        }

        if (!confirm(`Issue cards to ALL ${totalStudents} students without cards? This may take a while.`)) {
            return;
        }

        showProcessingModal(`Fetching ${totalStudents} students without cards...`);

        // Fetch all students without cards from the server
        fetch('/finance/cards/get-students-without-cards', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success || data.student_ids.length === 0) {
                hideLoading();
                showResultModal('No students without cards found', false);
                toastr.warning('No students without cards found');
                return;
            }

            showProcessingModal(`Issuing cards to ${data.student_ids.length} students...`);

            // Now issue cards to all these students
            fetch('/finance/cards/bulk/issue', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    student_ids: data.student_ids
                })
            })
            .then(response => response.json())
            .then(result => {
                hideLoading();
                let message = result.message || 'Processing complete';
                if (result.errors && result.errors.length > 0) {
                    message += '\n\nErrors:\n' + result.errors.join('\n');
                }
                showResultModal(message, result.success);
                
                if (result.success) {
                    toastr.success(result.message || 'Cards issued successfully');
                    setTimeout(() => {
                        closeModal('qrModal');
                        location.reload();
                    }, 4000);
                } else {
                    toastr.error(result.message || 'Failed to issue cards');
                }
            })
            .catch(error => {
                hideLoading();
                showResultModal('Error: ' + error.message, false);
                toastr.error('An error occurred');
            });
        })
        .catch(error => {
            hideLoading();
            showResultModal('Error fetching students: ' + error.message, false);
            toastr.error('Failed to fetch students');
        });
    }

    // ============================================
    // GENERATE QR FUNCTIONS
    // ============================================

    function generateSingleQr(cardId) {
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

    function generateQrForSelected() {
        if (selectedIds.length === 0) {
            toastr.warning('Please select at least one student');
            return;
        }

        showProcessingModal('Fetching card data...');

        fetch('/finance/hs-students/get-card-ids', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                student_ids: selectedIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success || data.card_ids.length === 0) {
                hideLoading();
                showResultModal('No cards found for selected students', false);
                toastr.warning('No cards found for selected students');
                return;
            }

            showProcessingModal(`Generating QR codes for ${data.card_ids.length} card(s)...`);

            fetch('/finance/cards/bulk/generate-qr', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    card_ids: data.card_ids
                })
            })
            .then(response => response.json())
            .then(result => {
                hideLoading();
                let message = result.message || 'Processing complete';
                if (result.errors && result.errors.length > 0) {
                    message += '\n\nErrors:\n' + result.errors.join('\n');
                }
                showResultModal(message, result.success);
                
                if (result.success) {
                    toastr.success(result.message || 'QR codes generated');
                    setTimeout(() => {
                        closeModal('qrModal');
                        location.reload();
                    }, 3000);
                } else {
                    toastr.error(result.message || 'Failed to generate QR codes');
                }
            })
            .catch(error => {
                hideLoading();
                showResultModal('Error: ' + error.message, false);
                toastr.error('An error occurred');
            });
        })
        .catch(error => {
            hideLoading();
            showResultModal('Error fetching cards: ' + error.message, false);
            toastr.error('Failed to fetch card data');
        });
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