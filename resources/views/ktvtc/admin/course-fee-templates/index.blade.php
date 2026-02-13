@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Course Fee Templates')
@section('subtitle', 'Manage fee packages for courses and exam types')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Course Fee Templates</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.tvet.course-fee-templates.create') }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Create Template</span>
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Templates</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalTemplates ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-file-invoice text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-circle text-success mr-1"></i>
                <span>{{ number_format($activeTemplates ?? 0) }} active</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Templates</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($activeTemplates ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-success text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-percentage text-success mr-1"></i>
                <span>{{ $totalTemplates > 0 ? round(($activeTemplates / $totalTemplates) * 100, 1) : 0 }}% of total</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Default Templates</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($defaultTemplates ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-star text-purple-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-double text-purple-600 mr-1"></i>
                <span>Auto-selected for enrollments</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Public Templates</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($publicTemplates ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-globe text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-users text-blue-600 mr-1"></i>
                <span>Visible to students</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Avg. Total Fee</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">
                    KES {{ number_format($templates->avg('total_amount') ?? 0, 0) }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-calculator text-amber-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Exam Type Distribution Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Templates by Exam Type</h3>
            <div class="relative">
                <button onclick="toggleChartMenu()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div id="chartMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                    <div class="py-1">
                        <button onclick="exportChart('exam-type')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                            <i class="fas fa-download mr-2"></i>
                            Download Chart
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="h-64">
            <canvas id="examTypeChart"></canvas>
        </div>
    </div>

    <!-- Terms Distribution Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Templates by Duration</h3>
        </div>
        <div class="h-64">
            <canvas id="termsChart"></canvas>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter fee templates by course, exam type, campus and status</p>
            </div>
        </div>
    </div>
    <div class="p-6">
        <form id="filterForm" action="{{ route('admin.tvet.course-fee-templates.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Course Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Exam Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Type</label>
                    <select name="exam_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Exam Types</option>
                        @foreach($examTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('exam_type') == $value ? 'selected' : '' }}>
                                {{ $label }}
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
                        <option value="global" {{ request('campus_id') == 'global' ? 'selected' : '' }}>Global Templates</option>
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

                <!-- Default Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default</label>
                    <select name="is_default" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('is_default') == 'yes' ? 'selected' : '' }}>Default Templates</option>
                        <option value="no" {{ request('is_default') == 'no' ? 'selected' : '' }}>Non-Default</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by template name, code or course..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.course-fee-templates.index') }}"
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

<!-- Fee Templates Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Course Fee Templates</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $templates->total() }} templates found</p>
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
        <table class="w-full" id="templatesTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Type</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fee</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($templates as $template)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewTemplate('{{ $template->id }}')">
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas fa-file-invoice text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $template->name }}</span>
                                @if($template->code)
                                    <span class="text-xs font-mono text-gray-500 block">{{ $template->code }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $template->course->name ?? 'N/A' }}</p>
                            @if($template->intake_periods)
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ is_array($template->intake_periods) ? implode(', ', $template->intake_periods) : $template->intake_periods }}
                                </p>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                            @if($template->exam_type == 'nita') bg-blue-100 text-blue-800
                            @elseif($template->exam_type == 'cdacc') bg-green-100 text-green-800
                            @elseif($template->exam_type == 'school_assessment') bg-amber-100 text-amber-800
                            @else bg-purple-100 text-purple-800
                            @endif">
                            <i class="fas
                                @if($template->exam_type == 'nita') fa-certificate
                                @elseif($template->exam_type == 'cdacc') fa-graduation-cap
                                @elseif($template->exam_type == 'school_assessment') fa-school
                                @else fa-mixed
                                @endif mr-1">
                            </i>
                            {{ $template->exam_type_label }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm">
                            @if($template->duration_months)
                                <span class="text-gray-900">{{ $template->duration_months }} months</span>
                            @endif
                            @if($template->total_terms)
                                <span class="text-xs text-gray-500 block">{{ $template->total_terms }} term(s)</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="text-sm font-bold text-gray-900">KES {{ number_format($template->total_amount, 2) }}</p>
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <span class="mr-2">T: KES {{ number_format($template->total_tuition_fee, 0) }}</span>
                                <span>O: KES {{ number_format($template->total_other_fees, 0) }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @if($template->campus)
                            <span class="text-sm text-gray-900">{{ $template->campus->name }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-globe mr-1"></i> Global
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColor = $template->is_active ? 'green' : 'gray';
                            $statusText = $template->is_active ? 'Active' : 'Inactive';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i>
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @if($template->is_default)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-star mr-1"></i>
                                Default
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-star-o mr-1"></i>
                                Not Default
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.tvet.course-fee-templates.show', $template) }}"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.tvet.course-fee-templates.edit', $template) }}"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Template">
                                <i class="fas fa-edit"></i>
                            </a>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $template->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $template->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if(!$template->is_default)
                                        <button onclick="setDefault('{{ $template->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-star mr-2"></i>
                                            Set as Default
                                        </button>
                                        @endif

                                        @if($template->is_active)
                                        <button onclick="updateStatus('{{ $template->id }}', 'deactivate')"
                                                class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-pause-circle mr-2"></i>
                                            Deactivate
                                        </button>
                                        @else
                                        <button onclick="updateStatus('{{ $template->id }}', 'activate')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Activate
                                        </button>
                                        @endif

                                        <button onclick="duplicateTemplate('{{ $template->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-copy mr-2"></i>
                                            Duplicate
                                        </button>

                                        <hr class="my-1 border-gray-200">

                                        <button onclick="deleteTemplate('{{ $template->id }}')"
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
                            <i class="fas fa-file-invoice text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No fee templates found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first fee template</p>
                            <a href="{{ route('admin.tvet.course-fee-templates.create') }}"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Create Template
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($templates instanceof \Illuminate\Pagination\LengthAwarePaginator && $templates->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $templates->firstItem() }}</span> to
                <span class="font-medium">{{ $templates->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($templates->total()) }}</span> templates
            </div>
            <div class="flex items-center space-x-2">
                {{ $templates->links() }}
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Fee Template</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this fee template?
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        initializeQuickSearch();
    });

    // ============ CHARTS ============
    function initializeCharts() {
        // Exam Type Chart
        const examTypeCtx = document.getElementById('examTypeChart')?.getContext('2d');
        if (examTypeCtx) {
            new Chart(examTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($examTypeBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($examTypeBreakdown ?? [])) !!},
                        backgroundColor: [
                            '#3B82F6', // nita - blue
                            '#10B981', // cdacc - green
                            '#F59E0B', // school_assessment - amber
                            '#8B5CF6'  // mixed - purple
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Terms Chart
        const termsCtx = document.getElementById('termsChart')?.getContext('2d');
        if (termsCtx) {
            // Group templates by number of terms
            const termGroups = {!! json_encode($templates->groupBy('total_terms')->map->count() ?? []) !!};

            new Chart(termsCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(termGroups).map(terms => `${terms} Term${terms > 1 ? 's' : ''}`),
                    datasets: [{
                        label: 'Number of Templates',
                        data: Object.values(termGroups),
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: '#E5E7EB'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    }

    // ============ TABLE FUNCTIONS ============
    function viewTemplate(templateId) {
        window.location.href = `/admin/tvet/course-fee-templates/${templateId}`;
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
    function toggleActionMenu(templateId) {
        const menu = document.getElementById(`actionMenu-${templateId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${templateId}`) {
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

    // ============ TEMPLATE ACTIONS ============
    function setDefault(templateId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/tvet/course-fee-templates/${templateId}/set-default`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function updateStatus(templateId, action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action === 'activate'
            ? `/admin/tvet/course-fee-templates/${templateId}/activate`
            : `/admin/tvet/course-fee-templates/${templateId}/deactivate`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function duplicateTemplate(templateId) {
        // Implement duplicate functionality
        window.location.href = `/admin/tvet/course-fee-templates/${templateId}/duplicate`;
    }

    function deleteTemplate(templateId) {
        document.getElementById('deleteForm').action = `/admin/tvet/course-fee-templates/${templateId}`;
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

    // ============ CHART FUNCTIONS ============
    function toggleChartMenu() {
        const menu = document.getElementById('chartMenu');
        menu.classList.toggle('hidden');
    }

    function exportChart(chartType) {
        let canvas;
        if (chartType === 'exam-type') {
            canvas = document.getElementById('examTypeChart');
        }

        if (canvas) {
            const link = document.createElement('a');
            link.download = `${chartType}-chart.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        toggleChartMenu();
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
    #templatesTable {
        min-width: 1400px;
    }

    @media (max-width: 768px) {
        #templatesTable {
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
