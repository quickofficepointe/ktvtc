@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Fee Categories')
@section('subtitle', 'Manage fee categories and their properties')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fees</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Categories</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="openModal('createModal')"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Category</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Categories</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalCategories ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-tags text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-circle text-success mr-1"></i>
                <span>{{ number_format($activeCategories ?? 0) }} active</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Categories</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($activeCategories ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-success text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-percentage text-success mr-1"></i>
                <span>{{ $totalCategories > 0 ? round(($activeCategories / $totalCategories) * 100, 1) : 0 }}% of total</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Mandatory</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($mandatoryCategories ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-star text-red-600 mr-1"></i>
                <span>Required for all students</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Refundable</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($refundableCategories ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-undo-alt text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-money-bill-wave text-purple-600 mr-1"></i>
                <span>Caution, Deposit, etc.</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Per Term</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($frequencyBreakdown['per_term'] ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-amber-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-clock text-amber-600 mr-1"></i>
                <span>Recurring fees</span>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Frequency Distribution Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Frequency Distribution</h3>
            <div class="relative">
                <button onclick="toggleChartMenu()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div id="chartMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                    <div class="py-1">
                        <button onclick="exportChart('frequency')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <i class="fas fa-download mr-2"></i>
                            Download Chart
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="h-64">
            <canvas id="frequencyChart"></canvas>
        </div>
    </div>

    <!-- Category Status Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Category Status</h3>
        </div>
        <div class="h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter fee categories by status, frequency and campus</p>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="toggleBulkActions()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                    <i class="fas fa-check-square"></i>
                    <span>Bulk Actions</span>
                </button>
            </div>
        </div>
    </div>
    <div class="p-6">
        <form id="filterForm" action="{{ route('admin.tvet.fee-categories.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="yes" {{ request('is_active') == 'yes' ? 'selected' : '' }}>Active</option>
                        <option value="no" {{ request('is_active') == 'no' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Frequency Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                    <select name="frequency" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Frequencies</option>
                        <option value="once" {{ request('frequency') == 'once' ? 'selected' : '' }}>One Time</option>
                        <option value="per_term" {{ request('frequency') == 'per_term' ? 'selected' : '' }}>Per Term</option>
                        <option value="per_year" {{ request('frequency') == 'per_year' ? 'selected' : '' }}>Per Year</option>
                        <option value="per_month" {{ request('frequency') == 'per_month' ? 'selected' : '' }}>Per Month</option>
                        <option value="per_course" {{ request('frequency') == 'per_course' ? 'selected' : '' }}>Per Course</option>
                    </select>
                </div>

                <!-- Campus Filter (Only for Admin) -->
                @if(auth()->user()->role == 2)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Campus</label>
                    <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Campuses</option>
                        <option value="global">Global Categories</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Mandatory Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mandatory</label>
                    <select name="is_mandatory" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('is_mandatory') == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('is_mandatory') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <!-- Refundable Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Refundable</label>
                    <select name="is_refundable" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('is_refundable') == 'yes' ? 'selected' : '' }}>Yes</option>
                        <option value="no" {{ request('is_refundable') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by name, code or description..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.fee-categories.index') }}"
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

<!-- Bulk Actions Bar (Hidden by default) -->
<div id="bulkActionsBar" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <i class="fas fa-check-square text-blue-600"></i>
            <span class="text-sm font-medium text-blue-800">
                <span id="selectedCount">0</span> category(ies) selected
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="bulkActivate()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>Activate</span>
            </button>
            <button onclick="bulkDeactivate()"
                    class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-pause-circle"></i>
                <span>Deactivate</span>
            </button>
            <button onclick="bulkDelete()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm flex items-center space-x-2">
                <i class="fas fa-trash"></i>
                <span>Delete</span>
            </button>
            <button onclick="toggleBulkActions()"
                    class="px-4 py-2 border border-blue-300 text-blue-700 hover:bg-blue-100 rounded-lg transition-colors text-sm">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Fee Categories Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">All Fee Categories</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $feeCategories->total() }} categories found</p>
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
        <table class="w-full" id="categoriesTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <input type="checkbox" onclick="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Properties</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($feeCategories as $category)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                               class="category-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                               onchange="updateSelectedCount()">
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center cursor-pointer" onclick="viewCategory(this)" data-category='@json($category)'>
                            <div class="w-8 h-8 rounded-lg" style="background-color: {{ $category->color ?? '#3B82F6' }}10">
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas {{ $category->icon ?? 'fa-tag' }}" style="color: {{ $category->color ?? '#3B82F6' }}"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <span class="text-sm font-mono font-medium text-gray-900">{{ $category->code }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="cursor-pointer" onclick="viewCategory(this)" data-category='@json($category)'>
                            <p class="font-medium text-gray-800">{{ $category->name }}</p>
                            @if($category->description)
                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($category->description, 50) }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                            @if($category->frequency == 'per_term') bg-blue-100 text-blue-800
                            @elseif($category->frequency == 'once') bg-green-100 text-green-800
                            @elseif($category->frequency == 'per_year') bg-purple-100 text-purple-800
                            @elseif($category->frequency == 'per_month') bg-amber-100 text-amber-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            <i class="fas
                                @if($category->frequency == 'per_term') fa-calendar-alt
                                @elseif($category->frequency == 'once') fa-check-circle
                                @elseif($category->frequency == 'per_year') fa-calendar
                                @elseif($category->frequency == 'per_month') fa-calendar-day
                                @else fa-clock
                                @endif mr-1 text-xs">
                            </i>
                            {{ $category->frequency_label ?? $category->frequency }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex flex-col space-y-1">
                            @if($category->is_mandatory)
                                <span class="inline-flex items-center text-xs text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Mandatory
                                </span>
                            @endif
                            @if($category->is_refundable)
                                <span class="inline-flex items-center text-xs text-purple-600">
                                    <i class="fas fa-undo-alt mr-1"></i> Refundable
                                </span>
                            @endif
                            @if($category->is_taxable)
                                <span class="inline-flex items-center text-xs text-amber-600">
                                    <i class="fas fa-percent mr-1"></i> Taxable
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @if($category->campus)
                            <span class="text-sm text-gray-900">{{ $category->campus->name }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-globe mr-1"></i> Global
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColor = $category->is_active ? 'green' : 'gray';
                            $statusText = $category->is_active ? 'Active' : 'Inactive';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i>
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewCategory(this)"
                                    data-category='@json($category)'
                                    class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editCategory(this)"
                                    data-category='@json($category)'
                                    class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    title="Edit Category">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $category->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $category->id }}"
                                     class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if(!$category->is_active)
                                        <button onclick="updateStatus('{{ $category->id }}', 'activate')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Activate
                                        </button>
                                        @else
                                        <button onclick="updateStatus('{{ $category->id }}', 'deactivate')"
                                                class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-pause-circle mr-2"></i>
                                            Deactivate
                                        </button>
                                        @endif
                                        <hr class="my-1 border-gray-200">
                                        <button onclick="deleteCategory('{{ $category->id }}', '{{ $category->name }}')"
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
                    <td colspan="8" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-tags text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No fee categories found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first fee category</p>
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Category
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($feeCategories instanceof \Illuminate\Pagination\LengthAwarePaginator && $feeCategories->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $feeCategories->firstItem() }}</span> to
                <span class="font-medium">{{ $feeCategories->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($feeCategories->total()) }}</span> categories
            </div>
            <div class="flex items-center space-x-2">
                {{ $feeCategories->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

<!-- ============ CREATE MODAL ============ -->
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('createModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Create Fee Category</h3>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.tvet.fee-categories.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column - Basic Info -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Basic Information Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-info-circle text-primary mr-2"></i>
                                    Basic Information
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Category Name -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Category Name
                                        </label>
                                        <input type="text"
                                               name="name"
                                               id="create_name"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., Tuition, Registration, Examination"
                                               required
                                               onkeyup="generateCreateCode()">
                                    </div>

                                    <!-- Category Code -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Category Code
                                            <span class="text-xs text-gray-500 ml-2">(Auto-generated if empty)</span>
                                        </label>
                                        <div class="flex">
                                            <input type="text"
                                                   name="code"
                                                   id="create_code"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                                                   placeholder="e.g., TUITION, REGISTRATION">
                                            <button type="button"
                                                    onclick="generateCreateCode()"
                                                    class="ml-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Description
                                        </label>
                                        <textarea name="description"
                                                  rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                  placeholder="Describe what this fee category covers..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Properties Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-cog text-primary mr-2"></i>
                                    Fee Properties
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Frequency -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                            Frequency
                                        </label>
                                        <select name="frequency"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                required>
                                            <option value="">Select Frequency</option>
                                            <option value="once">One Time</option>
                                            <option value="per_term">Per Term</option>
                                            <option value="per_year">Per Year</option>
                                            <option value="per_month">Per Month</option>
                                            <option value="per_course">Per Course</option>
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">
                                            How often this fee is charged
                                        </p>
                                    </div>

                                    <!-- Sort Order -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Sort Order
                                        </label>
                                        <input type="number"
                                               name="sort_order"
                                               value="0"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="0">
                                        <p class="mt-1 text-xs text-gray-500">
                                            Lower numbers appear first
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                                    <!-- Is Mandatory -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="is_mandatory"
                                               id="create_is_mandatory"
                                               value="1"
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="create_is_mandatory" class="ml-2 text-sm text-gray-700">
                                            Mandatory Fee
                                        </label>
                                    </div>

                                    <!-- Is Refundable -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="is_refundable"
                                               id="create_is_refundable"
                                               value="1"
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="create_is_refundable" class="ml-2 text-sm text-gray-700">
                                            Refundable
                                        </label>
                                    </div>

                                    <!-- Is Taxable -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="is_taxable"
                                               id="create_is_taxable"
                                               value="1"
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="create_is_taxable" class="ml-2 text-sm text-gray-700">
                                            Taxable
                                        </label>
                                    </div>

                                    <!-- Is Active -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="is_active"
                                               id="create_is_active"
                                               value="1"
                                               checked
                                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                        <label for="create_is_active" class="ml-2 text-sm text-gray-700">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Suggested Items Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-list text-primary mr-2"></i>
                                    Suggested Items
                                </h4>
                                <p class="text-xs text-gray-500 mb-4">
                                    Add common item names that admins can quickly select when creating fee templates
                                </p>

                                <div id="create-suggested-items-container">
                                    <div class="flex items-center space-x-2 mb-3 suggested-item-row">
                                        <input type="text"
                                               name="suggested_items[]"
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., ID Fee">
                                    </div>
                                    <div class="flex items-center space-x-2 mb-3 suggested-item-row">
                                        <input type="text"
                                               name="suggested_items[]"
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., Medical Fee">
                                    </div>
                                    <div class="flex items-center space-x-2 mb-3 suggested-item-row">
                                        <input type="text"
                                               name="suggested_items[]"
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                               placeholder="e.g., Caution Fee">
                                    </div>
                                </div>

                                <button type="button"
                                        onclick="addSuggestedItem('create')"
                                        class="mt-2 px-4 py-2 border border-primary text-primary hover:bg-primary hover:text-white rounded-lg transition-colors flex items-center space-x-2">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Add Another Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Right Column - Display Settings & Campus -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Display Settings Card -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-palette text-primary mr-2"></i>
                                    Display Settings
                                </h4>

                                <div class="space-y-4">
                                    <!-- Icon Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Icon
                                        </label>
                                        <select name="icon" id="create_icon" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="fa-tag">Tag</option>
                                            <option value="fa-money-bill">Money Bill</option>
                                            <option value="fa-book">Book</option>
                                            <option value="fa-graduation-cap">Graduation</option>
                                            <option value="fa-id-card">ID Card</option>
                                            <option value="fa-hospital">Medical</option>
                                            <option value="fa-home">Hostel</option>
                                            <option value="fa-tshirt">T-Shirt</option>
                                            <option value="fa-file-alt">Exam</option>
                                        </select>
                                        <div class="mt-2 flex items-center justify-center p-4 bg-white rounded-lg border border-gray-200">
                                            <i id="create-icon-preview" class="fas fa-tag text-3xl" style="color: #3B82F6"></i>
                                        </div>
                                    </div>

                                    <!-- Color Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Color
                                        </label>
                                        <div class="grid grid-cols-4 gap-2">
                                            @php
                                                $colors = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#6B7280'];
                                            @endphp
                                            @foreach($colors as $index => $color)
                                                <div class="relative">
                                                    <input type="radio"
                                                           name="color"
                                                           id="create_color_{{ $index }}"
                                                           value="{{ $color }}"
                                                           {{ $loop->first ? 'checked' : '' }}
                                                           class="hidden color-radio"
                                                           onchange="updateCreateColorPreview('{{ $color }}')">
                                                    <label for="create_color_{{ $index }}"
                                                           class="block w-full aspect-square rounded-lg cursor-pointer border-2 transition-all"
                                                           style="background-color: {{ $color }}; border-color: {{ $loop->first ? '#000' : 'transparent' }}">
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campus Assignment Card -->
                            @if(auth()->user()->role == 2)
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-building text-primary mr-2"></i>
                                    Campus Assignment
                                </h4>

                                <div class="space-y-4">
                                    <div>
                                        <div class="flex items-center mb-3">
                                            <input type="radio"
                                                   name="campus_scope"
                                                   id="create_scope_global"
                                                   value="global"
                                                   checked
                                                   class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                                   onchange="toggleCreateCampusSelect()">
                                            <label for="create_scope_global" class="ml-2 text-sm text-gray-700">
                                                Global Category (All Campuses)
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio"
                                                   name="campus_scope"
                                                   id="create_scope_specific"
                                                   value="specific"
                                                   class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                                   onchange="toggleCreateCampusSelect()">
                                            <label for="create_scope_specific" class="ml-2 text-sm text-gray-700">
                                                Campus-Specific
                                            </label>
                                        </div>
                                    </div>

                                    <div id="create-campus-select-container" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Select Campus
                                        </label>
                                        <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <option value="">Choose Campus</option>
                                            @foreach($campuses as $campus)
                                                <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @else
                                <input type="hidden" name="campus_id" value="{{ auth()->user()->campus_id }}">
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('createModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="document.getElementById('createForm').submit()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Create Category
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============ VIEW MODAL ============ -->
<div id="viewModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('viewModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Category Details</h3>
                    <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="viewModalContent" class="space-y-6">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button onclick="closeModal('viewModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============ EDIT MODAL ============ -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('editModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Fee Category</h3>
                    <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="editModalContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============ DELETE MODAL ============ -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Delete Fee Category</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this fee category?
                    </p>
                    <p class="text-center text-sm text-red-600 mt-2" id="deleteWarning"></p>
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

<!-- ============ BULK DELETE MODAL ============ -->
<div id="bulkDeleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('bulkDeleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Bulk Delete Categories</h3>
                    <button onclick="closeModal('bulkDeleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="bulkDeleteModalMessage">
                        Are you sure you want to delete <span id="bulkDeleteCount"></span> category(ies)?
                    </p>
                </div>
                <form id="bulkDeleteForm" method="POST" action="{{ route('admin.tvet.fee-categories.bulk.delete') }}">
                    @csrf
                    <div id="bulkDeleteInputs"></div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('bulkDeleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitBulkDelete()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template for new suggested item row -->
<template id="suggested-item-template">
    <div class="flex items-center space-x-2 mb-3 suggested-item-row">
        <input type="text"
               name="suggested_items[]"
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
               placeholder="e.g., New Item">
        <button type="button"
                onclick="removeSuggestedItem(this)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</template>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        initializeQuickSearch();
        initializeColorPreviews();
    });

    // ============ CHARTS ============
    function initializeCharts() {
        // Frequency Chart
        const frequencyCtx = document.getElementById('frequencyChart')?.getContext('2d');
        if (frequencyCtx) {
            new Chart(frequencyCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($frequencyBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($frequencyBreakdown ?? [])) !!},
                        backgroundColor: ['#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', '#6B7280'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 12, padding: 15 }
                        }
                    }
                }
            });
        }

        // Status Chart
        const statusCtx = document.getElementById('statusChart')?.getContext('2d');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: ['Active', 'Inactive'],
                    datasets: [{
                        data: [{{ $activeCategories ?? 0 }}, {{ ($totalCategories ?? 0) - ($activeCategories ?? 0) }}],
                        backgroundColor: ['#10B981', '#6B7280'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 12, padding: 15 }
                        }
                    }
                }
            });
        }
    }

    function initializeColorPreviews() {
        // Set initial icon preview for create modal
        const createIconInput = document.getElementById('create_icon');
        const createIconPreview = document.getElementById('create-icon-preview');
        if (createIconInput && createIconPreview) {
            createIconPreview.className = `fas ${createIconInput.value} text-3xl`;

            createIconInput.addEventListener('change', function() {
                createIconPreview.className = `fas ${this.value} text-3xl`;
            });
        }

        // Set initial color preview
        const selectedColor = document.querySelector('#createModal .color-radio:checked');
        if (selectedColor && createIconPreview) {
            createIconPreview.style.color = selectedColor.value;
        }
    }

    // ============ MODAL CONTROLS ============
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // ============ VIEW CATEGORY ============
    function viewCategory(element) {
        try {
            let categoryData = element.getAttribute('data-category');
            if (!categoryData) {
                console.error('No category data found');
                return;
            }

            // Parse the JSON data
            const category = JSON.parse(categoryData);

            // Format frequency label
            let frequencyLabel = category.frequency || '';
            if (frequencyLabel) {
                frequencyLabel = frequencyLabel.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }

            // Format suggested items
            let suggestedItemsHtml = '';
            if (category.suggested_items && Array.isArray(category.suggested_items) && category.suggested_items.length > 0) {
                suggestedItemsHtml = `
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h5 class="font-medium text-gray-800 mb-2">Suggested Items</h5>
                        <div class="flex flex-wrap gap-2">
                            ${category.suggested_items.map(item => `<span class="px-3 py-1 bg-white rounded-lg text-sm">${item}</span>`).join('')}
                        </div>
                    </div>
                `;
            }

            // Format dates
            const createdDate = category.created_at ? new Date(category.created_at).toLocaleDateString() : 'N/A';
            const updatedDate = category.updated_at ? new Date(category.updated_at).toLocaleDateString() : 'N/A';

            const html = `
                <div class="space-y-6">
                    <!-- Header -->
                    <div class="flex items-center">
                        <div class="w-16 h-16 rounded-lg flex items-center justify-center mr-4" style="background-color: ${category.color || '#3B82F6'}20">
                            <i class="fas ${category.icon || 'fa-tag'} text-3xl" style="color: ${category.color || '#3B82F6'}"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-gray-800">${category.name || ''}</h4>
                            <div class="flex items-center mt-2 space-x-2">
                                <span class="px-3 py-1 bg-gray-100 rounded-lg text-sm font-mono">${category.code || ''}</span>
                                <span class="px-3 py-1 ${category.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'} rounded-lg text-sm">
                                    <i class="fas fa-circle mr-1 text-xs"></i>${category.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h5 class="font-medium text-gray-800 mb-2">Description</h5>
                        <p class="text-gray-600">${category.description || 'No description provided.'}</p>
                    </div>

                    <!-- Properties -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Frequency</p>
                            <p class="font-semibold">${frequencyLabel}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Sort Order</p>
                            <p class="font-semibold">${category.sort_order || 0}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Mandatory</p>
                            <p class="font-semibold ${category.is_mandatory ? 'text-red-600' : ''}">${category.is_mandatory ? 'Yes' : 'No'}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Refundable</p>
                            <p class="font-semibold ${category.is_refundable ? 'text-purple-600' : ''}">${category.is_refundable ? 'Yes' : 'No'}</p>
                        </div>
                    </div>

                    <!-- Campus -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h5 class="font-medium text-gray-800 mb-2">Campus</h5>
                        <p>${category.campus ? category.campus.name : 'Global (All Campuses)'}</p>
                    </div>

                    ${suggestedItemsHtml}

                    <!-- Metadata -->
                    <div class="text-xs text-gray-400 border-t pt-4">
                        <p>Created: ${createdDate}</p>
                        <p class="mt-1">Last Updated: ${updatedDate}</p>
                    </div>
                </div>
            `;

            const contentDiv = document.getElementById('viewModalContent');
            if (contentDiv) {
                contentDiv.innerHTML = html;
                openModal('viewModal');
            }

        } catch (error) {
            console.error('Error parsing category data:', error);
            alert('Error loading category details');
        }
    }

    // ============ EDIT CATEGORY ============
    function editCategory(element) {
        try {
            const categoryData = element.getAttribute('data-category');
            if (!categoryData) {
                console.error('No category data found');
                return;
            }

            const category = JSON.parse(categoryData);

            // Build suggested items HTML
            let suggestedItemsHtml = '';
            if (category.suggested_items && Array.isArray(category.suggested_items) && category.suggested_items.length > 0) {
                category.suggested_items.forEach(item => {
                    suggestedItemsHtml += `
                        <div class="flex items-center space-x-2 mb-3 suggested-item-row">
                            <input type="text"
                                   name="suggested_items[]"
                                   value="${item.replace(/"/g, '&quot;')}"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., New Item">
                            <button type="button"
                                    onclick="removeSuggestedItem(this)"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                });
            } else {
                suggestedItemsHtml = `
                    <div class="flex items-center space-x-2 mb-3 suggested-item-row">
                        <input type="text"
                               name="suggested_items[]"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., New Item">
                        <button type="button"
                                onclick="removeSuggestedItem(this)"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }

            // Get campus options
            const campusOptions = document.querySelector('#create-campus-select-container select')?.innerHTML || '';

            const html = `
                <form id="editForm_${category.id}" method="POST" action="/admin/tvet/fee-categories/${category.id}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Basic Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4">Basic Information</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Category Name</label>
                                        <input type="text"
                                               name="name"
                                               value="${(category.name || '').replace(/"/g, '&quot;')}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                                               required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Category Code</label>
                                        <input type="text"
                                               name="code"
                                               value="${(category.code || '').replace(/"/g, '&quot;')}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary font-mono"
                                               required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                        <textarea name="description"
                                                  rows="3"
                                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg">${(category.description || '').replace(/"/g, '&quot;')}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Properties -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4">Fee Properties</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2 required">Frequency</label>
                                        <select name="frequency" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                            <option value="once" ${category.frequency === 'once' ? 'selected' : ''}>One Time</option>
                                            <option value="per_term" ${category.frequency === 'per_term' ? 'selected' : ''}>Per Term</option>
                                            <option value="per_year" ${category.frequency === 'per_year' ? 'selected' : ''}>Per Year</option>
                                            <option value="per_month" ${category.frequency === 'per_month' ? 'selected' : ''}>Per Month</option>
                                            <option value="per_course" ${category.frequency === 'per_course' ? 'selected' : ''}>Per Course</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                        <input type="number"
                                               name="sort_order"
                                               value="${category.sort_order || 0}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="is_mandatory"
                                               value="1"
                                               ${category.is_mandatory ? 'checked' : ''}
                                               class="mr-2"> Mandatory
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="is_refundable"
                                               value="1"
                                               ${category.is_refundable ? 'checked' : ''}
                                               class="mr-2"> Refundable
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="is_taxable"
                                               value="1"
                                               ${category.is_taxable ? 'checked' : ''}
                                               class="mr-2"> Taxable
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="is_active"
                                               value="1"
                                               ${category.is_active ? 'checked' : ''}
                                               class="mr-2"> Active
                                    </label>
                                </div>
                            </div>

                            <!-- Suggested Items -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4">Suggested Items</h4>
                                <div id="edit-suggested-items-container">
                                    ${suggestedItemsHtml}
                                </div>
                                <button type="button"
                                        onclick="addEditSuggestedItem()"
                                        class="mt-2 px-4 py-2 border border-primary text-primary hover:bg-primary hover:text-white rounded-lg transition-colors flex items-center space-x-2">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Add Another Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Display Settings -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4">Display Settings</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Icon</label>
                                        <select name="icon" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                            <option value="fa-tag" ${category.icon === 'fa-tag' ? 'selected' : ''}>Tag</option>
                                            <option value="fa-money-bill" ${category.icon === 'fa-money-bill' ? 'selected' : ''}>Money Bill</option>
                                            <option value="fa-book" ${category.icon === 'fa-book' ? 'selected' : ''}>Book</option>
                                            <option value="fa-graduation-cap" ${category.icon === 'fa-graduation-cap' ? 'selected' : ''}>Graduation</option>
                                            <option value="fa-id-card" ${category.icon === 'fa-id-card' ? 'selected' : ''}>ID Card</option>
                                            <option value="fa-hospital" ${category.icon === 'fa-hospital' ? 'selected' : ''}>Medical</option>
                                            <option value="fa-home" ${category.icon === 'fa-home' ? 'selected' : ''}>Hostel</option>
                                            <option value="fa-tshirt" ${category.icon === 'fa-tshirt' ? 'selected' : ''}>T-Shirt</option>
                                            <option value="fa-file-alt" ${category.icon === 'fa-file-alt' ? 'selected' : ''}>Exam</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                                        <input type="color"
                                               name="color"
                                               value="${category.color || '#3B82F6'}"
                                               class="w-full h-10 p-1 border rounded">
                                    </div>
                                </div>
                            </div>

                            <!-- Campus -->
                            @if(auth()->user()->role == 2)
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h4 class="text-md font-medium text-gray-800 mb-4">Campus</h4>
                                <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Global</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" ${category.campus_id == {{ $campus->id }} ? 'selected' : ''}>{{ $campus->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                        <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 border rounded-lg">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Update Category</button>
                    </div>
                </form>
            `;

            const contentDiv = document.getElementById('editModalContent');
            if (contentDiv) {
                contentDiv.innerHTML = html;
                openModal('editModal');
            }

        } catch (error) {
            console.error('Error parsing category data:', error);
            alert('Error loading edit form');
        }
    }

    // ============ CREATE FORM HELPERS ============
    function generateCreateCode() {
        const name = document.getElementById('create_name');
        const code = document.getElementById('create_code');

        if (name && name.value) {
            const generatedCode = name.value.toUpperCase()
                .replace(/\s+/g, '_')
                .replace(/[^A-Z0-9_]/g, '')
                .substring(0, 30);
            if (code) code.value = generatedCode;
        }
    }

    function updateCreateColorPreview(color) {
        const iconPreview = document.getElementById('create-icon-preview');
        if (iconPreview) {
            iconPreview.style.color = color;
        }

        document.querySelectorAll('#createModal .color-radio + label').forEach(label => {
            label.style.borderColor = 'transparent';
        });

        document.querySelectorAll('#createModal .color-radio:checked + label').forEach(label => {
            label.style.borderColor = '#000';
        });
    }

    // ============ CAMPUS SCOPE ============
    function toggleCreateCampusSelect() {
        const isSpecific = document.getElementById('create_scope_specific');
        const container = document.getElementById('create-campus-select-container');

        if (isSpecific && container) {
            if (isSpecific.checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                const select = container.querySelector('select');
                if (select) select.value = '';
            }
        }
    }

    // ============ SUGGESTED ITEMS ============
    function addSuggestedItem(modal) {
        const template = document.getElementById('suggested-item-template');
        if (!template) return;

        const clone = template.content.cloneNode(true);

        if (modal === 'create') {
            const container = document.getElementById('create-suggested-items-container');
            if (container) container.appendChild(clone);
        }
    }

    function addEditSuggestedItem() {
        const template = document.getElementById('suggested-item-template');
        if (!template) return;

        const clone = template.content.cloneNode(true);
        const container = document.getElementById('edit-suggested-items-container');
        if (container) container.appendChild(clone);
    }

    function removeSuggestedItem(button) {
        const row = button.closest('.suggested-item-row');
        if (!row) return;

        const container = row.parentNode;
        if (container.children.length > 1) {
            row.remove();
        } else {
            alert('You need at least one suggested item row');
        }
    }

    // ============ TABLE FUNCTIONS ============
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

    // ============ CHECKBOX & BULK ACTIONS ============
    function toggleBulkActions() {
        const bulkActionsBar = document.getElementById('bulkActionsBar');
        if (bulkActionsBar) {
            bulkActionsBar.classList.toggle('hidden');

            if (bulkActionsBar.classList.contains('hidden')) {
                const checkboxes = document.querySelectorAll('.category-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = false;
                });
                const mainCheckbox = document.querySelector('th input[type="checkbox"]');
                if (mainCheckbox) mainCheckbox.checked = false;
                updateSelectedCount();
            }
        }
    }

    function toggleAllCheckboxes(checkbox) {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        const count = checkboxes.length;
        const selectedCountEl = document.getElementById('selectedCount');
        if (selectedCountEl) selectedCountEl.textContent = count;

        const bulkActionsBar = document.getElementById('bulkActionsBar');
        if (bulkActionsBar) {
            if (count > 0) {
                bulkActionsBar.classList.remove('hidden');
            } else {
                bulkActionsBar.classList.add('hidden');
            }
        }
    }

    function getSelectedCategoryIds() {
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function bulkActivate() {
        const ids = getSelectedCategoryIds();
        if (ids.length === 0) {
            alert('Please select at least one category');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.tvet.fee-categories.bulk.activate") }}';
        form.innerHTML = '@csrf';

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function bulkDeactivate() {
        const ids = getSelectedCategoryIds();
        if (ids.length === 0) {
            alert('Please select at least one category');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.tvet.fee-categories.bulk.deactivate") }}';
        form.innerHTML = '@csrf';

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    function bulkDelete() {
        const ids = getSelectedCategoryIds();
        if (ids.length === 0) {
            alert('Please select at least one category');
            return;
        }

        const countEl = document.getElementById('bulkDeleteCount');
        if (countEl) countEl.textContent = ids.length;

        const inputsDiv = document.getElementById('bulkDeleteInputs');
        if (inputsDiv) {
            inputsDiv.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'category_ids[]';
                input.value = id;
                inputsDiv.appendChild(input);
            });
        }

        openModal('bulkDeleteModal');
    }

    function submitBulkDelete() {
        const form = document.getElementById('bulkDeleteForm');
        if (form) form.submit();
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(categoryId) {
        const menu = document.getElementById(`actionMenu-${categoryId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${categoryId}`) {
                m.classList.add('hidden');
            }
        });

        if (menu) menu.classList.toggle('hidden');
    }

    // Close action menus when clicking elsewhere
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // ============ SINGLE CATEGORY ACTIONS ============
    function updateStatus(categoryId, action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action === 'activate'
            ? `/admin/tvet/fee-categories/${categoryId}/activate`
            : `/admin/tvet/fee-categories/${categoryId}/deactivate`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function deleteCategory(categoryId, categoryName) {
        const deleteForm = document.getElementById('deleteForm');
        const messageEl = document.getElementById('deleteModalMessage');

        if (deleteForm) {
            deleteForm.action = `/admin/tvet/fee-categories/${categoryId}`;
        }

        if (messageEl) {
            messageEl.textContent = `Are you sure you want to delete "${categoryName}"?`;
        }

        openModal('deleteModal');
    }

    function submitDeleteForm() {
        const form = document.getElementById('deleteForm');
        if (form) form.submit();
    }

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('[id$="Modal"]');
            modals.forEach(modal => {
                if (modal && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
            document.body.style.overflow = 'auto';
        }
    });

    // ============ CHART FUNCTIONS ============
    function toggleChartMenu() {
        const menu = document.getElementById('chartMenu');
        if (menu) menu.classList.toggle('hidden');
    }

    function exportChart(chartType) {
        let canvas;
        if (chartType === 'frequency') {
            canvas = document.getElementById('frequencyChart');
        }

        if (canvas) {
            const link = document.createElement('a');
            link.download = `${chartType}-chart.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        toggleChartMenu();
    }
</script>

<style>
    #categoriesTable {
        min-width: 1200px;
    }

    @media (max-width: 768px) {
        #categoriesTable {
            min-width: 100%;
        }
    }

    tr:hover td .cursor-pointer {
        cursor: pointer;
    }

    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
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
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .required:after {
        content: " *";
        color: #EF4444;
    }

    .color-radio:checked + label {
        border-width: 3px;
    }

    .hidden {
        display: none !important;
    }
</style>
@endsection
