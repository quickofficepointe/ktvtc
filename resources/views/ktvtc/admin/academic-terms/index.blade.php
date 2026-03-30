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
    <button onclick="openModal('createModal')"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Term</span>
    </button>
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
                <!-- Academic Year -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                        Academic Year
                    </label>
                    <select name="academic_year"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Select Year</option>
                        @foreach($academicYearsForForm as $year)
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
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewTerm({{ $term->id }})">
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
                            <button onclick="viewTerm({{ $term->id }})"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editTerm({{ $term->id }})"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Term">
                                <i class="fas fa-edit"></i>
                            </button>
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
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Term
                            </button>
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

<!-- Create Modal -->
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('createModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Create Academic Term</h3>
                        <p class="text-sm text-gray-600">Add a new academic term or quarter</p>
                    </div>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST">
                    @csrf
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" id="csrf_token">

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column - Main Term Info -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Basic Information Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-info-circle text-primary mr-2"></i>
                                    Basic Information
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Term Name -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Term Name
                                        </label>
                                        <input type="text"
                                               name="name"
                                               id="create_name"
                                               value="{{ old('name') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., Term 1 2024, Q1 2024, Jan-Mar 2024"
                                               required
                                               onkeyup="generateCreateCode()">
                                    </div>

                                    <!-- Term Code -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Term Code
                                        </label>
                                        <div class="flex">
                                            <input type="text"
                                                   name="code"
                                                   id="create_code"
                                                   value="{{ old('code') }}"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                                                   placeholder="e.g., Q1-2024"
                                                   required>
                                            <button type="button"
                                                    onclick="generateCreateCode()"
                                                    class="ml-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Short Code -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Short Code
                                        </label>
                                        <input type="text"
                                               name="short_code"
                                               value="{{ old('short_code') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., Q1, T1">
                                        <p class="mt-1 text-xs text-gray-500">Display code for dropdowns</p>
                                    </div>

                                    <!-- Term Number -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Term Number
                                        </label>
                                        <select name="term_number"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                required>
                                            <option value="">Select Term</option>
                                            @foreach($termNumbers as $number)
                                                <option value="{{ $number }}" {{ old('term_number') == $number ? 'selected' : '' }}>
                                                    Term {{ $number }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Academic Year -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Academic Year
                                        </label>
                                        <select name="academic_year"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                required>
                                            <option value="">Select Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year }}" {{ old('academic_year') == $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Academic Year Name -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Academic Year Display
                                        </label>
                                        <input type="text"
                                               name="academic_year_name"
                                               value="{{ old('academic_year_name') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., 2024/2025">
                                        <p class="mt-1 text-xs text-gray-500">If empty, academic year will be used</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Term Dates Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                    Term Dates
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Start Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Start Date
                                        </label>
                                        <input type="date"
                                               name="start_date"
                                               id="create_start_date"
                                               value="{{ old('start_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               required>
                                    </div>

                                    <!-- End Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            End Date
                                        </label>
                                        <input type="date"
                                               name="end_date"
                                               id="create_end_date"
                                               value="{{ old('end_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               required>
                                    </div>

                                    <!-- Fee Due Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Fee Due Date
                                        </label>
                                        <input type="date"
                                               name="fee_due_date"
                                               id="create_fee_due_date"
                                               value="{{ old('fee_due_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <!-- Registration Dates Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-door-open text-primary mr-2"></i>
                                    Registration Period
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Registration Start Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Registration Start Date
                                        </label>
                                        <input type="date"
                                               name="registration_start_date"
                                               value="{{ old('registration_start_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <!-- Registration End Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Registration End Date
                                        </label>
                                        <input type="date"
                                               name="registration_end_date"
                                               value="{{ old('registration_end_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <!-- Late Registration Start Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Late Registration Start
                                        </label>
                                        <input type="date"
                                               name="late_registration_start_date"
                                               value="{{ old('late_registration_start_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <!-- Late Registration End Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Late Registration End
                                        </label>
                                        <input type="date"
                                               name="late_registration_end_date"
                                               value="{{ old('late_registration_end_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center">
                                    <input type="checkbox"
                                           name="allow_late_registration"
                                           id="create_allow_late_registration"
                                           value="1"
                                           {{ old('allow_late_registration') ? 'checked' : '' }}
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <label for="create_allow_late_registration" class="ml-2 text-sm text-gray-700">
                                        Allow Late Registration
                                    </label>
                                </div>
                            </div>

                            <!-- Exam Dates Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-file-alt text-primary mr-2"></i>
                                    Examination Period
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Exam Registration Start -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Exam Registration Start
                                        </label>
                                        <input type="date"
                                               name="exam_registration_start_date"
                                               value="{{ old('exam_registration_start_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <!-- Exam Registration End -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Exam Registration End
                                        </label>
                                        <input type="date"
                                               name="exam_registration_end_date"
                                               value="{{ old('exam_registration_end_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <!-- Exam Start Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Exam Start Date
                                        </label>
                                        <input type="date"
                                               name="exam_start_date"
                                               value="{{ old('exam_start_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>

                                    <!-- Exam End Date -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Exam End Date
                                        </label>
                                        <input type="date"
                                               name="exam_end_date"
                                               value="{{ old('exam_end_date') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Settings & Campus -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Campus Assignment Card -->
                            @if(auth()->user()->role == 2)
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-building text-primary mr-2"></i>
                                    Campus Assignment
                                </h4>

                                <div class="space-y-4">
                                    <!-- Global or Campus-specific -->
                                    <div>
                                        <div class="flex items-center mb-3">
                                            <input type="radio"
                                                   name="campus_scope"
                                                   id="create_scope_global"
                                                   value="global"
                                                   {{ !old('campus_id') ? 'checked' : '' }}
                                                   class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                                   onchange="toggleCreateCampusSelect()">
                                            <label for="create_scope_global" class="ml-2 text-sm text-gray-700">
                                                Global Term (All Campuses)
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio"
                                                   name="campus_scope"
                                                   id="create_scope_specific"
                                                   value="specific"
                                                   {{ old('campus_id') ? 'checked' : '' }}
                                                   class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                                   onchange="toggleCreateCampusSelect()">
                                            <label for="create_scope_specific" class="ml-2 text-sm text-gray-700">
                                                Campus-Specific
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Campus Select (hidden if global) -->
                                    <div id="create_campus_select_container" class="{{ !old('campus_id') ? 'hidden' : '' }}">
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Select Campus
                                        </label>
                                        <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="">Choose Campus</option>
                                            @foreach($campuses as $campus)
                                                <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                                                    {{ $campus->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @else
                                <!-- For non-admin users, auto-assign their campus -->
                                <input type="hidden" name="campus_id" value="{{ auth()->user()->campus_id }}">
                            @endif

                            <!-- Status Settings Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-flag text-primary mr-2"></i>
                                    Status Settings
                                </h4>

                                <div class="space-y-4">
                                    <!-- Is Active -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="is_active"
                                               id="create_is_active"
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="create_is_active" class="ml-2 text-sm text-gray-700">
                                            Active
                                        </label>
                                    </div>

                                    <!-- Is Current -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="is_current"
                                               id="create_is_current"
                                               value="1"
                                               {{ old('is_current') ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="create_is_current" class="ml-2 text-sm text-gray-700">
                                            Set as Current Term
                                        </label>
                                    </div>

                                    <!-- Is Registration Open -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="is_registration_open"
                                               id="create_is_registration_open"
                                               value="1"
                                               {{ old('is_registration_open') ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="create_is_registration_open" class="ml-2 text-sm text-gray-700">
                                            Registration Open
                                        </label>
                                    </div>

                                    <!-- Lock Fee Generation -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="is_fee_generation_locked"
                                               id="create_is_fee_generation_locked"
                                               value="1"
                                               {{ old('is_fee_generation_locked') ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="create_is_fee_generation_locked" class="ml-2 text-sm text-gray-700">
                                            Lock Fee Generation
                                        </label>
                                        <i class="fas fa-info-circle text-gray-400 ml-1 text-xs"
                                           data-tooltip="Prevent automatic generation of invoices"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Settings Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-money-bill text-primary mr-2"></i>
                                    Financial Settings
                                </h4>

                                <div class="space-y-4">
                                    <!-- Late Registration Fee -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Late Registration Fee (KES)
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                                            <input type="number"
                                                   name="late_registration_fee"
                                                   value="{{ old('late_registration_fee', 0) }}"
                                                   min="0"
                                                   step="100"
                                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                    </div>

                                    <!-- Late Payment Percentage -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Late Payment Penalty (%)
                                        </label>
                                        <div class="relative">
                                            <input type="number"
                                                   name="late_payment_percentage"
                                                   value="{{ old('late_payment_percentage', 0) }}"
                                                   min="0"
                                                   max="100"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <span class="absolute right-3 top-2 text-gray-500">%</span>
                                        </div>
                                    </div>

                                    <!-- Late Payment Fixed Fee -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Late Payment Fixed Fee (KES)
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">KES</span>
                                            <input type="number"
                                                   name="late_payment_fee"
                                                   value="{{ old('late_payment_fee', 0) }}"
                                                   min="0"
                                                   step="100"
                                                   class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description & Notes Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-sticky-note text-primary mr-2"></i>
                                    Description & Notes
                                </h4>

                                <div class="space-y-4">
                                    <!-- Description -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Description
                                        </label>
                                        <textarea name="description"
                                                  rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                  placeholder="Brief description of this term...">{{ old('description') }}</textarea>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Internal Notes
                                        </label>
                                        <textarea name="notes"
                                                  rows="2"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                  placeholder="Internal notes (only visible to staff)">{{ old('notes') }}</textarea>
                                    </div>

                                    <!-- Sort Order -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Sort Order
                                        </label>
                                        <input type="number"
                                               name="sort_order"
                                               value="{{ old('sort_order', 0) }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Tips Card -->
                            <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                                <h4 class="text-md font-medium text-blue-800 mb-3 flex items-center">
                                    <i class="fas fa-lightbulb text-blue-600 mr-2"></i>
                                    Quick Tips
                                </h4>
                                <ul class="space-y-2 text-sm text-blue-700">
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                        <span><strong>Code format:</strong> Use Q1-2024, T1-2024, etc.</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                        <span><strong>Global terms</strong> apply to all campuses</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                        <span><strong>Only one</strong> term can be current</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                        <span><strong>Fee due date</strong> determines late payment penalties</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('createModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="button"
                        onclick="submitAcademicTerm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Create Term
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('editModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Edit Academic Term</h3>
                        <p class="text-sm text-gray-600">Update academic term information</p>
                    </div>
                    <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="editFormContent"></div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('editModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitEditForm()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Term
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Show Modal -->
<div id="showModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('showModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800" id="showModalTitle">Term Details</h3>
                        <p class="text-sm text-gray-600" id="showModalSubtitle">View academic term information</p>
                    </div>
                    <button onclick="closeModal('showModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="showModalContent"></div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('showModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
                <button onclick="editFromShow()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Term
                </button>
            </div>
        </div>
    </div>
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
        initializeDateValidation();
        initializeRealTimeValidation();

        // Set default dates if needed
        setDefaultDates();
    });

    // ============ DEFAULT DATES ============
    function setDefaultDates() {
        const today = new Date().toISOString().split('T')[0];
        const startDate = document.getElementById('create_start_date');
        const endDate = document.getElementById('create_end_date');
        const feeDueDate = document.getElementById('create_fee_due_date');

        if (startDate && !startDate.value) {
            startDate.value = today;
        }
        if (endDate && !endDate.value) {
            const threeMonthsLater = new Date();
            threeMonthsLater.setMonth(threeMonthsLater.getMonth() + 3);
            endDate.value = threeMonthsLater.toISOString().split('T')[0];
        }
        if (feeDueDate && !feeDueDate.value) {
            const oneMonthLater = new Date();
            oneMonthLater.setMonth(oneMonthLater.getMonth() + 1);
            feeDueDate.value = oneMonthLater.toISOString().split('T')[0];
        }
    }

    // ============ FORM DATA PREPARATION ============
    function prepareAcademicTermData() {
        const form = document.getElementById('createForm');
        const formData = new FormData();

        console.log('=== PREPARING FORM DATA ===');

        // 1. Handle Basic Fields
        const basicFields = [
            'name', 'code', 'short_code', 'term_number', 'academic_year',
            'academic_year_name', 'description', 'notes', 'sort_order'
        ];

        basicFields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input && input.value) {
                formData.append(field, input.value);
                console.log(`Basic field ${field}: ${input.value}`);
            } else if (field === 'sort_order') {
                formData.append(field, '0');
            }
        });

        // 2. Handle Date Fields
        const dateFields = [
            'start_date', 'end_date', 'fee_due_date',
            'registration_start_date', 'registration_end_date',
            'late_registration_start_date', 'late_registration_end_date',
            'exam_registration_start_date', 'exam_registration_end_date',
            'exam_start_date', 'exam_end_date'
        ];

        dateFields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input && input.value) {
                formData.append(field, input.value);
                console.log(`Date field ${field}: ${input.value}`);
            }
        });

        // 3. Handle CAMPUS ID (CRITICAL FIX)
        const campusScope = form.querySelector('[name="campus_scope"]:checked')?.value;
        console.log('Campus scope selected:', campusScope);

        if (campusScope === 'specific') {
            const campusId = form.querySelector('[name="campus_id"]')?.value;
            if (campusId) {
                formData.append('campus_id', campusId);
                console.log('Campus ID (specific):', campusId);
            } else {
                console.error('ERROR: Specific campus selected but no campus_id value');
                showNotification('Please select a campus for campus-specific term', 'error');
                return null;
            }
        } else {
            // For global terms, do NOT append campus_id (let it be NULL in database)
            console.log('Global term selected - campus_id will be NULL');
        }

        // 4. Handle ALL Boolean Fields (send as 0 or 1)
        const booleanFields = [
            'is_active',
            'is_current',
            'is_registration_open',
            'is_fee_generation_locked',
            'allow_late_registration'
        ];

        booleanFields.forEach(field => {
            const checkbox = form.querySelector(`[name="${field}"]`);
            const value = checkbox && checkbox.checked ? '1' : '0';
            formData.append(field, value);
            console.log(`Boolean ${field}: ${value}`);
        });

        // 5. Handle Financial Fields
        const financialFields = [
            'late_registration_fee',
            'late_payment_fee',
            'late_payment_percentage'
        ];

        financialFields.forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            const value = input?.value;
            if (value && value !== '') {
                formData.append(field, value);
                console.log(`Financial ${field}: ${value}`);
            } else {
                formData.append(field, '0');
            }
        });

        // 6. Add CSRF Token
        const token = document.querySelector('input[name="_token"]')?.value ||
                     document.querySelector('#csrf_token')?.value;
        if (token) {
            formData.append('_token', token);
            console.log('CSRF token added');
        } else {
            console.error('CSRF token not found!');
        }

        console.log('=== FINAL FORM DATA ===');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        return formData;
    }

    // ============ FORM SUBMISSION ============
    function submitAcademicTerm() {
        console.log('Submitting academic term...');

        // Validate required fields first
        if (!validateCreateForm()) {
            return false;
        }

        const preparedData = prepareAcademicTermData();

        if (!preparedData) {
            return false; // Validation failed
        }

        // Get form action URL
        const form = document.getElementById('createForm');
        const actionUrl = "{{ route('admin.tvet.academic-terms.store') }}";

        // Show loading state
        const submitBtn = event?.target || document.querySelector('[onclick="submitAcademicTerm()"]');
        const originalText = submitBtn?.innerHTML || 'Create Term';
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            submitBtn.disabled = true;
        }

        // Submit via fetch
        fetch(actionUrl, {
            method: 'POST',
            body: preparedData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data);

            if (data.errors) {
                // Handle validation errors
                let errorMessage = 'Validation errors:\n';
                for (let field in data.errors) {
                    errorMessage += `• ${field}: ${data.errors[field].join(', ')}\n`;
                }
                alert(errorMessage);

                // Highlight error fields
                Object.keys(data.errors).forEach(field => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('border-red-500');
                    }
                });

                // Reset button
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } else if (data.error) {
                alert('Error: ' + data.error);
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } else {
                // Success - show message and redirect
                alert('Academic term created successfully!');
                window.location.href = "{{ route('admin.tvet.academic-terms.index') }}";
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('An error occurred. Please try again.');

            // Reset button
            if (submitBtn) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        return false;
    }

    // ============ FORM VALIDATION ============
    function validateCreateForm() {
        const form = document.getElementById('createForm');
        let isValid = true;
        let firstInvalidField = null;

        // Clear all previous error messages
        document.querySelectorAll('.error-message, .field-error').forEach(el => el.remove());
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
        });

        // Required fields validation
        const requiredFields = [
            { name: 'name', label: 'Term Name' },
            { name: 'code', label: 'Term Code' },
            { name: 'term_number', label: 'Term Number' },
            { name: 'academic_year', label: 'Academic Year' },
            { name: 'start_date', label: 'Start Date' },
            { name: 'end_date', label: 'End Date' },
            { name: 'fee_due_date', label: 'Fee Due Date' }
        ];

        requiredFields.forEach(field => {
            const input = form.querySelector(`[name="${field.name}"]`);
            if (!input || !input.value.trim()) {
                isValid = false;
                if (input) {
                    input.classList.add('border-red-500');
                    showFieldError(input, `${field.label} is required`);
                    if (!firstInvalidField) firstInvalidField = input;
                }
            }
        });

        // Date validation
        const startDate = form.querySelector('[name="start_date"]');
        const endDate = form.querySelector('[name="end_date"]');
        const feeDueDate = form.querySelector('[name="fee_due_date"]');

        if (startDate && endDate && startDate.value && endDate.value) {
            if (new Date(endDate.value) < new Date(startDate.value)) {
                isValid = false;
                endDate.classList.add('border-red-500');
                showFieldError(endDate, 'End date cannot be before start date');
                if (!firstInvalidField) firstInvalidField = endDate;
            }
        }

        if (startDate && feeDueDate && startDate.value && feeDueDate.value) {
            if (new Date(feeDueDate.value) < new Date(startDate.value)) {
                isValid = false;
                feeDueDate.classList.add('border-red-500');
                showFieldError(feeDueDate, 'Fee due date must be on or after start date');
                if (!firstInvalidField) firstInvalidField = feeDueDate;
            }
        }

        // Campus validation
        const campusScope = form.querySelector('[name="campus_scope"]:checked');
        if (campusScope && campusScope.value === 'specific') {
            const campusSelect = form.querySelector('[name="campus_id"]');
            if (campusSelect && !campusSelect.value) {
                isValid = false;
                campusSelect.classList.add('border-red-500');
                showFieldError(campusSelect, 'Please select a campus');
                if (!firstInvalidField) firstInvalidField = campusSelect;
            }
        }

        // Scroll to first invalid field
        if (!isValid && firstInvalidField) {
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return isValid;
    }

    function showFieldError(input, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-red-500 text-xs mt-1';
        errorDiv.innerText = message;

        if (input.parentNode.classList.contains('relative')) {
            input.parentNode.parentNode.appendChild(errorDiv);
        } else {
            input.parentNode.appendChild(errorDiv);
        }
    }

    function showNotification(message, type = 'error') {
        alert(message);
    }

    // ============ REAL-TIME VALIDATION ============
    function initializeRealTimeValidation() {
        const form = document.getElementById('createForm');
        if (!form) return;

        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.value.trim()) {
                    this.classList.remove('border-red-500');
                    const errorMsg = this.parentNode.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });

            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('border-red-500');
                    const errorMsg = this.parentNode.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });
        });

        // Date validation in real-time
        const startDate = form.querySelector('[name="start_date"]');
        const endDate = form.querySelector('[name="end_date"]');

        if (startDate && endDate) {
            endDate.addEventListener('change', function() {
                if (startDate.value && this.value) {
                    if (new Date(this.value) < new Date(startDate.value)) {
                        this.classList.add('border-red-500');
                        showFieldError(this, 'End date cannot be before start date');
                    } else {
                        this.classList.remove('border-red-500');
                        const errorMsg = this.parentNode.querySelector('.error-message');
                        if (errorMsg) errorMsg.remove();
                    }
                }
            });
        }
    }

    // ============ TABLE FUNCTIONS ============
    function viewTerm(termId) {
        fetch(`/admin/tvet/academic-terms/${termId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('showModalContent').innerHTML = html;
                document.getElementById('showModalTitle').textContent = 'Term Details';
                openModal('showModal');
            })
            .catch(error => {
                console.error('Error loading term details:', error);
                alert('Failed to load term details');
            });
    }

    function editTerm(termId) {
        fetch(`/admin/tvet/academic-terms/${termId}/edit`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editFormContent').innerHTML = html;
                openModal('editModal');
            })
            .catch(error => {
                console.error('Error loading edit form:', error);
                alert('Failed to load edit form');
            });
    }

    function editFromShow() {
        const termIdElement = document.querySelector('#showModalContent [data-term-id]');
        if (termIdElement) {
            const termId = termIdElement.dataset.termId;
            closeModal('showModal');
            editTerm(termId);
        }
    }

    function submitEditForm() {
        const form = document.getElementById('editForm');
        if (form) {
            form.submit();
        }
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

    // ============ CREATE MODAL FUNCTIONS ============
    function generateCreateCode() {
        const name = document.getElementById('create_name').value;
        const codeInput = document.getElementById('create_code');
        if (name && codeInput) {
            const yearMatch = name.match(/\d{4}/);
            const year = yearMatch ? yearMatch[0] : new Date().getFullYear();

            let termCode = '';
            if (name.toLowerCase().includes('q1')) termCode = 'Q1';
            else if (name.toLowerCase().includes('q2')) termCode = 'Q2';
            else if (name.toLowerCase().includes('q3')) termCode = 'Q3';
            else if (name.toLowerCase().includes('q4')) termCode = 'Q4';
            else if (name.toLowerCase().includes('term 1')) termCode = 'T1';
            else if (name.toLowerCase().includes('term 2')) termCode = 'T2';
            else if (name.toLowerCase().includes('term 3')) termCode = 'T3';
            else if (name.toLowerCase().includes('term 4')) termCode = 'T4';
            else {
                const termMatch = name.match(/\b([1-4])\b/);
                if (termMatch) termCode = `T${termMatch[1]}`;
            }

            if (termCode) {
                codeInput.value = `${termCode}-${year}`;
            }
        }
    }

    function toggleCreateCampusSelect() {
        const isSpecific = document.getElementById('create_scope_specific')?.checked;
        const container = document.getElementById('create_campus_select_container');

        if (container) {
            if (isSpecific) {
                container.classList.remove('hidden');
                document.querySelector('[name="campus_id"]').required = true;
            } else {
                container.classList.add('hidden');
                document.querySelector('[name="campus_id"]').required = false;
                const campusSelect = document.querySelector('[name="campus_id"]');
                if (campusSelect) campusSelect.value = '';
            }
        }
    }

    // ============ DATE VALIDATION ============
    function initializeDateValidation() {
        const startDate = document.getElementById('create_start_date');
        const endDate = document.getElementById('create_end_date');
        const feeDueDate = document.getElementById('create_fee_due_date');

        if (startDate) {
            startDate.addEventListener('change', function() {
                if (endDate && !endDate.value) {
                    endDate.value = this.value;
                }
                if (feeDueDate && !feeDueDate.value) {
                    feeDueDate.value = this.value;
                }
            });
        }

        if (endDate) {
            endDate.addEventListener('change', function() {
                if (startDate && new Date(this.value) < new Date(startDate.value)) {
                    alert('End date cannot be before start date');
                    this.value = startDate.value;
                }
            });
        }

        if (feeDueDate) {
            feeDueDate.addEventListener('change', function() {
                if (startDate && new Date(this.value) < new Date(startDate.value)) {
                    alert('Fee due date should be on or after term start date');
                    this.value = startDate.value;
                }
            });
        }
    }

    // ============ MODAL FUNCTIONS ============
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';

        if (modalId === 'editModal') {
            document.getElementById('editFormContent').innerHTML = '';
        }
        if (modalId === 'showModal') {
            document.getElementById('showModalContent').innerHTML = '';
        }
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

    .required:after {
        content: " *";
        color: #EF4444;
    }

    .hidden {
        display: none !important;
    }

    .border-red-500 {
        border-color: #EF4444 !important;
    }

    .error-message {
        color: #EF4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
