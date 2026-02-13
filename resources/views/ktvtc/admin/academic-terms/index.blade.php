@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Academic Terms')
@section('subtitle', 'Manage academic terms, quarters and registration periods')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">TVET</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Academic</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Terms</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.academic-terms.create') }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Term</span>
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Terms</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalTerms ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-calendar-alt text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Current Term</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($currentTerms ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-play-circle text-success text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Terms</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($activeTerms ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Registration Open</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($registrationOpen ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-door-open text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter academic terms by year, campus and status</p>
            </div>
        </div>
    </div>
    <div class="p-6">
        <form id="filterForm" action="{{ route('admin.tvet.academic-terms.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Academic Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                    <select name="academic_year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Campus Filter (Only for Admin) -->
                @if(auth()->user()->role == 2)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                    <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Campuses</option>
                        <option value="global" {{ request('campus_id') == 'global' ? 'selected' : '' }}>Global Terms</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="yes" {{ request('is_active') == 'yes' ? 'selected' : '' }}>Active</option>
                        <option value="no" {{ request('is_active') == 'no' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Current Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Term</label>
                    <select name="is_current" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('is_current') == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('is_current') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by name, code or year..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.academic-terms.index') }}"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </a>
                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Academic Terms Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Academic Terms & Quarters</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $academicTerms->total() }} terms found</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" id="tableSearch" placeholder="Quick search..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <button onclick="refreshTable()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="termsTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($academicTerms as $term)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewTerm('{{ $term->id }}')">
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas fa-calendar-day text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $term->name }}</span>
                                @if($term->is_current)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-circle mr-1 text-green-500 text-xs"></i>
                                        Current
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-mono text-gray-900">{{ $term->code }}</span>
                        @if($term->short_code)
                            <span class="text-xs text-gray-500 block">{{ $term->short_code }}</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm">
                            <span class="text-gray-900">{{ $term->academic_year }}</span>
                            @if($term->academic_year_name)
                                <span class="text-xs text-gray-500 block">{{ $term->academic_year_name }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-900">
                            {{ $term->start_date->format('M d') }} - {{ $term->end_date->format('M d, Y') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Term {{ $term->term_number }}
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm">
                            <span class="text-gray-900">{{ $term->fee_due_date->format('M d, Y') }}</span>
                            @if($term->fee_due_date->isPast() && $term->is_active)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Overdue
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @if($term->campus)
                            <span class="text-sm text-gray-900">{{ $term->campus->name }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-globe mr-1"></i> Global
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColor = $term->is_active ? 'green' : 'gray';
                            $statusText = $term->is_active ? 'Active' : 'Inactive';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i>
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @if($term->is_registration_open)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-door-open mr-1"></i>
                                Open
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-door-closed mr-1"></i>
                                Closed
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.tvet.academic-terms.show', $term) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.tvet.academic-terms.edit', $term) }}"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Term">
                                <i class="fas fa-edit"></i>
                            </a>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $term->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $term->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if(!$term->is_current)
                                        <button onclick="setCurrent('{{ $term->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-play-circle mr-2"></i>
                                            Set as Current
                                        </button>
                                        @endif

                                        @if($term->is_active)
                                            @if(!$term->is_current)
                                            <button onclick="updateStatus('{{ $term->id }}', 'deactivate')"
                                                    class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                                <i class="fas fa-pause-circle mr-2"></i>
                                                Deactivate
                                            </button>
                                            @endif
                                        @else
                                        <button onclick="updateStatus('{{ $term->id }}', 'activate')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Activate
                                        </button>
                                        @endif

                                        <button onclick="toggleRegistration('{{ $term->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-amber-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-door-open mr-2"></i>
                                            {{ $term->is_registration_open ? 'Close' : 'Open' }} Registration
                                        </button>

                                        <hr class="my-1 border-gray-200">

                                        <button onclick="deleteTerm('{{ $term->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-calendar-alt text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No academic terms found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first term</p>
                            <a href="{{ route('admin.tvet.academic-terms.create') }}"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Term
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($academicTerms instanceof \Illuminate\Pagination\LengthAwarePaginator && $academicTerms->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $academicTerms->firstItem() }}</span> to
                <span class="font-medium">{{ $academicTerms->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($academicTerms->total()) }}</span> terms
            </div>
            <div class="flex items-center space-x-2">
                {{ $academicTerms->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Delete Academic Term</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this academic term?
                    </p>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitDeleteForm()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeQuickSearch();
    });

    // ============ TABLE FUNCTIONS ============
    function viewTerm(termId) {
        window.location.href = `/admin/tvet/academic-terms/${termId}`;
    }

    function refreshTable() {
        location.reload();
    }

    function initializeQuickSearch() {
        const searchInput = document.getElementById('tableSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchParams = new URLSearchParams(window.location.search);
                    searchParams.set('search', this.value);
                    window.location.href = `${window.location.pathname}?${searchParams.toString()}`;
                }
            });
        }
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(termId) {
        const menu = document.getElementById(`actionMenu-${termId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${termId}`) {
                m.classList.add('hidden');
            }
        });

        menu.classList.toggle('hidden');
    }

    // Close action menus when clicking elsewhere
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // ============ TERM ACTIONS ============
    function setCurrent(termId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/tvet/academic-terms/${termId}/set-current`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function updateStatus(termId, action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action === 'activate'
            ? `/admin/tvet/academic-terms/${termId}/activate`
            : `/admin/tvet/academic-terms/${termId}/deactivate`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function toggleRegistration(termId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/tvet/academic-terms/${termId}/toggle-registration`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function deleteTerm(termId) {
        document.getElementById('deleteForm').action = `/admin/tvet/academic-terms/${termId}`;
        openModal('deleteModal');
    }

    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
    }

    // ============ MODAL FUNCTIONS ============
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close modals when clicking escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
            document.body.style.overflow = 'auto';
        }
    });
</script>

<style>
    #termsTable {
        min-width: 1400px;
    }

    @media (max-width: 768px) {
        #termsTable {
            min-width: 100%;
        }
    }

    tr[onclick]:hover {
        cursor: pointer;
        background-color: #F9FAFB;
    }

    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
@endsection
