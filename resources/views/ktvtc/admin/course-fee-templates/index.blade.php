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
    <button onclick="openModal('createModal')"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Create Template</span>
    </button>
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
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Templates by Exam Body</h3>
            <div class="relative">
                <button onclick="toggleChartMenu()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div id="chartMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                    <div class="py-1">
                        <button onclick="exportChart('exam-body')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Type</label>
                    <select name="exam_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Exam Types</option>
                        @foreach($examTypes as $examType)
                            <option value="{{ $examType->id }}" {{ request('exam_type_id') == $examType->id ? 'selected' : '' }}>
                                {{ $examType->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="yes" {{ request('is_active') == 'yes' ? 'selected' : '' }}>Active</option>
                        <option value="no" {{ request('is_active') == 'no' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default</label>
                    <select name="is_default" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All</option>
                        <option value="yes" {{ request('is_default') == 'yes' ? 'selected' : '' }}>Default Templates</option>
                        <option value="no" {{ request('is_default') == 'no' ? 'selected' : '' }}>Non-Default</option>
                    </select>
                </div>

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
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewTemplate({{ $template->id }})">
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
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium"
                              style="background-color: {{ $template->examType->examBody->color ?? '#F3F4F6' }}20; color: {{ $template->examType->examBody->color ?? '#374151' }}">
                            <i class="fas fa-certificate mr-1"></i>
                            {{ $template->examType->full_name ?? 'N/A' }}
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
                            <button onclick="viewTemplate({{ $template->id }})"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editTemplate({{ $template->id }})"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Template">
                                <i class="fas fa-edit"></i>
                            </button>
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
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Create Template
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

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

<!-- ============================================ -->
<!-- CREATE MODAL -->
<!-- ============================================ -->
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('createModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Create Fee Template</h3>
                        <p class="text-sm text-gray-600">Add a new course fee template</p>
                    </div>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.tvet.course-fee-templates.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Basic Information
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Template Name
                                    </label>
                                    <input type="text"
                                           name="name"
                                           id="create_name"
                                           value="{{ old('name') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="e.g., Diploma in IT - NITA Package"
                                           required
                                           onkeyup="generateCreateCode()">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Template Code
                                    </label>
                                    <div class="flex">
                                        <input type="text"
                                               name="code"
                                               id="create_code"
                                               value="{{ old('code') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                                               placeholder="Auto-generated">
                                        <button type="button"
                                                onclick="generateCreateCode()"
                                                class="ml-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Course
                                    </label>
                                    <select name="course_id" id="create_course_id" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Course</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" data-code="{{ $course->code }}">
                                                {{ $course->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Exam Type
                                    </label>
                                    <select name="exam_type_id" id="create_exam_type_id" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Exam Type</option>
                                        @foreach($examTypes as $examType)
                                            <option value="{{ $examType->id }}" data-code="{{ $examType->code }}">
                                                {{ $examType->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Duration & Structure -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-clock text-primary mr-2"></i>
                                Duration & Structure
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Number of Terms
                                    </label>
                                    <select name="total_terms" required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Terms</option>
                                        @foreach($totalTerms as $term)
                                            <option value="{{ $term }}">{{ $term }} Term{{ $term > 1 ? 's' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Duration (Months)
                                    </label>
                                    <select name="duration_months"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Select Duration</option>
                                        @foreach($durations as $duration)
                                            <option value="{{ $duration }}">{{ $duration }} Month{{ $duration > 1 ? 's' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Intake Periods
                                    </label>
                                    <div class="space-y-2">
                                        @foreach($intakePeriods as $period)
                                            <label class="flex items-center">
                                                <input type="checkbox" name="intake_periods[]" value="{{ $period }}"
                                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                                <span class="ml-2 text-sm text-gray-700">{{ $period }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Campus -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-building text-primary mr-2"></i>
                                Additional Information
                            </h4>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea name="description"
                                              rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                              placeholder="Brief description of this fee template...">{{ old('description') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Internal Notes
                                    </label>
                                    <textarea name="notes"
                                              rows="2"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                              placeholder="Internal notes (only visible to staff)">{{ old('notes') }}</textarea>
                                </div>

                                @if(auth()->user()->role == 2)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Campus
                                    </label>
                                    <select name="campus_id"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Global Template (All Campuses)</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Status Settings -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-flag text-primary mr-2"></i>
                                Status Settings
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" checked
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_default" value="1"
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Set as Default</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_public" value="1" checked
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">Public (Visible to Students)</span>
                                </label>
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
                <button onclick="document.getElementById('createForm').submit()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Create Template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT MODAL -->
<!-- ============================================ -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('editModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Edit Fee Template</h3>
                        <p class="text-sm text-gray-600" id="editTemplateSubtitle">Update course fee template details</p>
                    </div>
                    <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="editFormLoading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-primary text-2xl"></i>
                    <p class="text-gray-600 mt-2">Loading template data...</p>
                </div>

                <div id="editFormContent" style="display: none;">
                    <form id="editForm" method="POST" action="">
                        @csrf
                        @method('PUT')

                        <!-- Template Information Tabs -->
                        <div class="border-b border-gray-200 mb-6">
                            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="editTabs">
                                <li class="mr-2">
                                    <a href="#basic-info" class="inline-block p-4 border-b-2 border-primary text-primary rounded-t-lg active" onclick="switchEditTab('basic-info', event)">
                                        <i class="fas fa-info-circle mr-2"></i>Basic Info
                                    </a>
                                </li>
                                <li class="mr-2">
                                    <a href="#fee-items" class="inline-block p-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 rounded-t-lg" onclick="switchEditTab('fee-items', event)">
                                        <i class="fas fa-receipt mr-2"></i>Fee Items
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Basic Info Tab -->
                        <div id="basic-info-tab" class="edit-tab">
                            <div class="space-y-6">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-info-circle text-primary mr-2"></i>
                                        Basic Information
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Template Name
                                            </label>
                                            <input type="text"
                                                   name="name"
                                                   id="edit_name"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                   required>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Template Code
                                            </label>
                                            <input type="text"
                                                   name="code"
                                                   id="edit_code"
                                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Course
                                            </label>
                                            <select name="course_id" id="edit_course_id" required
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="">Select Course</option>
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Exam Type
                                            </label>
                                            <select name="exam_type_id" id="edit_exam_type_id" required
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="">Select Exam Type</option>
                                                @foreach($examTypes as $examType)
                                                    <option value="{{ $examType->id }}">{{ $examType->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-clock text-primary mr-2"></i>
                                        Duration & Structure
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                                Number of Terms
                                            </label>
                                            <select name="total_terms" id="edit_total_terms" required
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="">Select Terms</option>
                                                @foreach($totalTerms as $term)
                                                    <option value="{{ $term }}">{{ $term }} Term{{ $term > 1 ? 's' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Duration (Months)
                                            </label>
                                            <select name="duration_months" id="edit_duration_months"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="">Select Duration</option>
                                                @foreach($durations as $duration)
                                                    <option value="{{ $duration }}">{{ $duration }} Month{{ $duration > 1 ? 's' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Intake Periods
                                            </label>
                                            <div class="space-y-2" id="edit_intake_periods_container">
                                                @foreach($intakePeriods as $period)
                                                    <label class="flex items-center">
                                                        <input type="checkbox" name="intake_periods[]" value="{{ $period }}"
                                                               class="edit_intake_checkbox w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                                        <span class="ml-2 text-sm text-gray-700">{{ $period }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-building text-primary mr-2"></i>
                                        Additional Information
                                    </h4>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Description
                                            </label>
                                            <textarea name="description"
                                                      id="edit_description"
                                                      rows="3"
                                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Internal Notes
                                            </label>
                                            <textarea name="notes"
                                                      id="edit_notes"
                                                      rows="2"
                                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                        </div>

                                        @if(auth()->user()->role == 2)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Campus
                                            </label>
                                            <select name="campus_id" id="edit_campus_id"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                                <option value="">Global Template (All Campuses)</option>
                                                @foreach($campuses as $campus)
                                                    <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-flag text-primary mr-2"></i>
                                        Status Settings
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_active" value="1" id="edit_is_active"
                                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_default" value="1" id="edit_is_default"
                                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Set as Default</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_public" value="1" id="edit_is_public"
                                                   class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                            <span class="ml-2 text-sm text-gray-700">Public (Visible to Students)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fee Items Tab -->
                        <div id="fee-items-tab" class="edit-tab hidden">
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-medium text-gray-800 flex items-center">
                                        <i class="fas fa-receipt text-primary mr-2"></i>
                                        Fee Items
                                    </h4>
                                    <button type="button" onclick="openAddFeeItemModal()"
                                            class="px-3 py-1.5 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm flex items-center">
                                        <i class="fas fa-plus-circle mr-1"></i>
                                        Add Fee Item
                                    </button>
                                </div>

                                <!-- Fee Items Table -->
                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-4 py-2 text-left">Category</th>
                                                <th class="px-4 py-2 text-left">Item Name</th>
                                                <th class="px-4 py-2 text-right">Amount</th>
                                                <th class="px-4 py-2 text-center">Qty</th>
                                                <th class="px-4 py-2 text-right">Total</th>
                                                <th class="px-4 py-2 text-center">Terms</th>
                                                <th class="px-4 py-2 text-center">Required</th>
                                                <th class="px-4 py-2 text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="feeItemsList" class="divide-y">
                                            <tr>
                                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                                    <i class="fas fa-receipt text-gray-300 text-2xl mb-2"></i>
                                                    <p>No fee items added yet</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-gray-50 font-medium">
                                            <tr>
                                                <td colspan="4" class="px-4 py-3 text-right">Totals:</td>
                                                <td class="px-4 py-3 text-right" id="totalTuition">KES 0</td>
                                                <td colspan="3" class="px-4 py-3 text-right" id="totalOther">KES 0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('editModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="document.getElementById('editForm').submit()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- ADD FEE ITEM MODAL -->
<!-- ============================================ -->
<div id="addFeeItemModal" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('addFeeItemModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Add Fee Item</h3>
                    <button onclick="closeModal('addFeeItemModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="addFeeItemForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="template_id" id="fee_item_template_id">

                    <div class="space-y-4">
                        <!-- Fee Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Fee Category
                            </label>
                            <select name="fee_category_id" id="fee_category_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Category</option>
                                @foreach($feeCategories as $category)
                                    <option value="{{ $category->id }}"
                                            data-icon="{{ $category->icon ?? 'fa-tag' }}"
                                            data-color="{{ $category->color ?? '#3B82F6' }}">
                                        {{ $category->name }}
                                        @if($category->is_mandatory)
                                            (Mandatory)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div id="categoryPreview" class="mt-2 hidden flex items-center p-2 bg-gray-50 rounded-lg">
                                <div class="w-6 h-6 rounded flex items-center justify-center mr-2" id="categoryColorPreview">
                                    <i class="fas fa-tag text-xs" id="categoryIconPreview"></i>
                                </div>
                                <span class="text-sm text-gray-600" id="categoryNamePreview"></span>
                            </div>
                        </div>

                        <!-- Item Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Item Name
                            </label>
                            <input type="text"
                                   name="item_name"
                                   id="item_name"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Tuition Fee, Registration Fee">
                            <div id="suggestedItemsContainer" class="mt-2 hidden">
                                <p class="text-xs text-gray-500 mb-1">Suggested from category:</p>
                                <div id="suggestedItemsList" class="flex flex-wrap gap-2">
                                    <!-- Suggested items will appear here -->
                                </div>
                            </div>
                        </div>

                        <!-- Amount and Quantity -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                    Amount (KES)
                                </label>
                                <input type="number"
                                       name="amount"
                                       id="amount"
                                       required
                                       min="0"
                                       step="0.01"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Quantity
                                </label>
                                <input type="number"
                                       name="quantity"
                                       id="quantity"
                                       value="1"
                                       min="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>

                        <!-- Applicable Terms -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                Applicable Terms
                            </label>
                            <select name="applicable_terms" id="applicable_terms" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Terms</option>
                                <option value="all">All Terms</option>
                                <option value="term1">Term 1 Only</option>
                                <option value="term2">Term 2 Only</option>
                                <option value="term3">Term 3 Only</option>
                                <option value="term1,term2">Terms 1 & 2</option>
                                <option value="term2,term3">Terms 2 & 3</option>
                                <option value="term1,term2,term3">All Terms</option>
                            </select>
                        </div>

                        <!-- Due Day Offset -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Due Day Offset
                                <span class="text-xs text-gray-500 ml-1">(Days from term start)</span>
                            </label>
                            <input type="number"
                                   name="due_day_offset"
                                   id="due_day_offset"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., 7 for first week">
                        </div>

                        <!-- Checkbox Options -->
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_required" value="1" checked
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Required</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="is_refundable" value="1"
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Refundable</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="is_advance_payment" value="1"
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Advance Payment</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="is_visible_to_student" value="1" checked
                                       class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-700">Visible to Student</span>
                            </label>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order
                                <span class="text-xs text-gray-500 ml-1">(Lower numbers appear first)</span>
                            </label>
                            <input type="number"
                                   name="sort_order"
                                   id="sort_order"
                                   min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Auto-assigned if empty">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('addFeeItemModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitAddFeeItem()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EDIT FEE ITEM MODAL -->
<!-- ============================================ -->
<div id="editFeeItemModal" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('editFeeItemModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Edit Fee Item</h3>
                    <button onclick="closeModal('editFeeItemModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="editFeeItemForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <!-- Same fields as add modal, populated via JavaScript -->
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('editFeeItemModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitEditFeeItem()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE CONFIRMATION MODAL -->
<!-- ============================================ -->
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

<!-- ============================================ -->
<!-- VIEW MODAL -->
<!-- ============================================ -->
<div id="viewModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('viewModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Template Details</h3>
                        <p class="text-sm text-gray-600" id="viewTemplateName"></p>
                    </div>
                    <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="viewLoading" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-primary text-2xl"></i>
                    <p class="text-gray-600 mt-2">Loading template details...</p>
                </div>

                <div id="viewContent" style="display: none;">
                    <!-- Template details will be loaded here via AJAX -->
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                <button onclick="closeModal('viewModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Close
                </button>
                <button onclick="editTemplateFromView()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Template
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DELETE FEE ITEM CONFIRMATION MODAL -->
<!-- ============================================ -->
<div id="deleteFeeItemModal" class="hidden fixed inset-0 z-[60] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteFeeItemModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Delete Fee Item</h3>
                    <button onclick="closeModal('deleteFeeItemModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600">
                        Are you sure you want to delete this fee item?
                    </p>
                </div>
                <form id="deleteFeeItemForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteFeeItemModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitDeleteFeeItem()"
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
    // ============ GLOBAL VARIABLES ============
    let currentTemplateId = null;
    let currentFeeItemId = null;

    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        initializeQuickSearch();
    });

    // ============ CHARTS ============
    function initializeCharts() {
        const examTypeCtx = document.getElementById('examTypeChart')?.getContext('2d');
        if (examTypeCtx) {
            new Chart(examTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($examTypeBreakdown ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($examTypeBreakdown ?? [])) !!},
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#6B7280'],
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

        const termsCtx = document.getElementById('termsChart')?.getContext('2d');
        if (termsCtx) {
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
                        y: { beginAtZero: true, grid: { color: '#E5E7EB' } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { display: false } }
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
    }

    // ============ TABLE FUNCTIONS ============
    function viewTemplate(templateId) {
        currentTemplateId = templateId;
        openModal('viewModal');
        document.getElementById('viewLoading').style.display = 'block';
        document.getElementById('viewContent').style.display = 'none';

        fetch(`/admin/tvet/course-fee-templates/${templateId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('viewLoading').style.display = 'none';
                document.getElementById('viewContent').style.display = 'block';
                document.getElementById('viewContent').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('viewLoading').style.display = 'none';
                document.getElementById('viewContent').style.display = 'block';
                document.getElementById('viewContent').innerHTML = '<p class="text-red-600 text-center py-4">Error loading template details.</p>';
            });
    }

    function editTemplate(templateId) {
        currentTemplateId = templateId;
        openModal('editModal');
        document.getElementById('editFormLoading').style.display = 'block';
        document.getElementById('editFormContent').style.display = 'none';

        fetch(`/admin/tvet/course-fee-templates/${templateId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editFormLoading').style.display = 'none';
                document.getElementById('editFormContent').style.display = 'block';

                document.getElementById('editForm').action = `/admin/tvet/course-fee-templates/${templateId}`;
                document.getElementById('editTemplateSubtitle').textContent = `Editing: ${data.template.name}`;

                // Populate basic info
                document.getElementById('edit_name').value = data.template.name || '';
                document.getElementById('edit_code').value = data.template.code || '';
                document.getElementById('edit_course_id').value = data.template.course_id || '';
                document.getElementById('edit_exam_type_id').value = data.template.exam_type_id || '';
                document.getElementById('edit_total_terms').value = data.template.total_terms || '';
                document.getElementById('edit_duration_months').value = data.template.duration_months || '';
                document.getElementById('edit_description').value = data.template.description || '';
                document.getElementById('edit_notes').value = data.template.notes || '';

                if (document.getElementById('edit_campus_id')) {
                    document.getElementById('edit_campus_id').value = data.template.campus_id || '';
                }

                document.getElementById('edit_is_active').checked = data.template.is_active || false;
                document.getElementById('edit_is_default').checked = data.template.is_default || false;
                document.getElementById('edit_is_public').checked = data.template.is_public || false;

                document.querySelectorAll('.edit_intake_checkbox').forEach(checkbox => {
                    checkbox.checked = data.selectedIntakes && data.selectedIntakes.includes(checkbox.value);
                });

                // Load fee items
                loadFeeItems(templateId);
            })
            .catch(error => {
                document.getElementById('editFormLoading').style.display = 'none';
                alert('Error loading template data. Please try again.');
                closeModal('editModal');
            });
    }

    function editTemplateFromView() {
        closeModal('viewModal');
        if (currentTemplateId) {
            editTemplate(currentTemplateId);
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

    // ============ TAB FUNCTIONS ============
    function switchEditTab(tabId, event) {
        if (event) {
            event.preventDefault();
        }

        document.querySelectorAll('.edit-tab').forEach(tab => {
            tab.classList.add('hidden');
        });

        document.getElementById(`${tabId}-tab`).classList.remove('hidden');

        document.querySelectorAll('#editTabs a').forEach(link => {
            link.classList.remove('border-primary', 'text-primary');
            link.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
        });

        if (event) {
            event.currentTarget.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            event.currentTarget.classList.add('border-primary', 'text-primary');
        }
    }

    // ============ FEE ITEM FUNCTIONS ============
    function loadFeeItems(templateId) {
        fetch(`/admin/tvet/course-fee-templates/${templateId}/fee-items`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('feeItemsList');
                if (data.items && data.items.length > 0) {
                    let html = '';
                    let totalTuition = 0;
                    let totalOther = 0;

                    data.items.forEach(item => {
                        const isTuition = item.fee_category?.is_tuition || false;
                        const itemTotal = item.amount * (item.quantity || 1);

                        if (isTuition) {
                            totalTuition += itemTotal;
                        } else {
                            totalOther += itemTotal;
                        }

                        html += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">${item.fee_category?.name || 'N/A'}</td>
                                <td class="px-4 py-2 font-medium">${item.item_name}</td>
                                <td class="px-4 py-2 text-right">KES ${formatNumber(item.amount)}</td>
                                <td class="px-4 py-2 text-center">${item.quantity || 1}</td>
                                <td class="px-4 py-2 text-right font-medium">KES ${formatNumber(itemTotal)}</td>
                                <td class="px-4 py-2 text-center">${formatTerms(item.applicable_terms)}</td>
                                <td class="px-4 py-2 text-center">
                                    ${item.is_required ?
                                        '<span class="text-green-600"><i class="fas fa-check-circle"></i></span>' :
                                        '<span class="text-gray-400"><i class="fas fa-circle"></i></span>'}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button onclick="editFeeItem(${item.id})" class="text-amber-600 hover:text-amber-800 mx-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteFeeItem(${item.id})" class="text-red-600 hover:text-red-800 mx-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button onclick="duplicateFeeItem(${item.id})" class="text-blue-600 hover:text-blue-800 mx-1">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    tbody.innerHTML = html;
                    document.getElementById('totalTuition').textContent = `KES ${formatNumber(totalTuition)}`;
                    document.getElementById('totalOther').textContent = `KES ${formatNumber(totalOther)}`;
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-receipt text-gray-300 text-2xl mb-2"></i>
                                <p>No fee items added yet</p>
                            </td>
                        </tr>
                    `;
                }
            });
    }

    function openAddFeeItemModal() {
        if (!currentTemplateId) return;
        document.getElementById('fee_item_template_id').value = currentTemplateId;
        document.getElementById('addFeeItemForm').action = `/admin/tvet/course-fee-templates/${currentTemplateId}/fee-items`;
        openModal('addFeeItemModal');
    }

    function submitAddFeeItem() {
        const form = document.getElementById('addFeeItemForm');
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('addFeeItemModal');
                loadFeeItems(currentTemplateId);
                form.reset();
            } else {
                alert(data.message || 'Error adding fee item');
            }
        })
        .catch(error => {
            alert('Error adding fee item');
        });
    }

    function editFeeItem(itemId) {
        currentFeeItemId = itemId;
        fetch(`/admin/tvet/fee-items/${itemId}/edit`)
            .then(response => response.json())
            .then(data => {
                // Populate edit form
                document.getElementById('editFeeItemForm').action = `/admin/tvet/fee-items/${itemId}`;
                // ... populate fields
                openModal('editFeeItemModal');
            });
    }

    function deleteFeeItem(itemId) {
        currentFeeItemId = itemId;
        document.getElementById('deleteFeeItemForm').action = `/admin/tvet/fee-items/${itemId}`;
        openModal('deleteFeeItemModal');
    }

    function submitDeleteFeeItem() {
        const form = document.getElementById('deleteFeeItemForm');
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('deleteFeeItemModal');
                loadFeeItems(currentTemplateId);
            }
        });
    }

    function duplicateFeeItem(itemId) {
        fetch(`/admin/tvet/fee-items/${itemId}/duplicate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadFeeItems(currentTemplateId);
            }
        });
    }

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
        window.location.href = `/admin/tvet/course-fee-templates/${templateId}/duplicate`;
    }

    function deleteTemplate(templateId) {
        document.getElementById('deleteForm').action = `/admin/tvet/course-fee-templates/${templateId}`;
        openModal('deleteModal');
    }

    function submitDeleteForm() {
        document.getElementById('deleteForm').submit();
    }

    // ============ CODE GENERATION ============
    function generateCreateCode() {
        const name = document.getElementById('create_name').value;
        const courseSelect = document.getElementById('create_course_id');
        const examSelect = document.getElementById('create_exam_type_id');
        const courseCode = courseSelect.options[courseSelect.selectedIndex]?.dataset?.code || '';
        const examCode = examSelect.options[examSelect.selectedIndex]?.dataset?.code || '';
        const year = new Date().getFullYear();

        if (courseCode && examCode) {
            document.getElementById('create_code').value = `${courseCode}-${examCode}-${year}`.toUpperCase();
        } else if (name) {
            const words = name.split(' ');
            let code = '';
            words.forEach(word => {
                if (word.length > 0) code += word[0].toUpperCase();
            });
            code = code.substring(0, 8);
            document.getElementById('create_code').value = `${code}-${year}`;
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

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    // ============ CHART FUNCTIONS ============
    function toggleChartMenu() {
        const menu = document.getElementById('chartMenu');
        menu.classList.toggle('hidden');
    }

    function exportChart(chartType) {
        let canvas;
        if (chartType === 'exam-body') {
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

    // ============ UTILITY FUNCTIONS ============
    function formatNumber(num) {
        return new Intl.NumberFormat('en-KE', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
    }

    function formatTerms(terms) {
        if (!terms) return 'N/A';
        if (terms === 'all') return 'All Terms';
        return terms.split(',').map(t => t.replace('term', 'T')).join(', ');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = ['createModal', 'editModal', 'viewModal', 'deleteModal', 'addFeeItemModal', 'editFeeItemModal', 'deleteFeeItemModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });
            document.body.style.overflow = 'auto';
        }
    });
    // ============ FEE ITEM CATEGORY HANDLING ============
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('fee_category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const icon = selectedOption.dataset.icon || 'fa-tag';
                const color = selectedOption.dataset.color || '#3B82F6';
                const name = selectedOption.text.replace(/\s*\(Mandatory\)\s*$/, '');

                // Show preview
                const preview = document.getElementById('categoryPreview');
                const colorPreview = document.getElementById('categoryColorPreview');
                const iconPreview = document.getElementById('categoryIconPreview');
                const namePreview = document.getElementById('categoryNamePreview');

                colorPreview.style.backgroundColor = color + '20';
                iconPreview.style.color = color;
                iconPreview.className = `fas ${icon}`;
                namePreview.textContent = name;
                preview.classList.remove('hidden');

                // Load suggested items for this category
                loadSuggestedItems(selectedOption.value);
            } else {
                document.getElementById('categoryPreview').classList.add('hidden');
                document.getElementById('suggestedItemsContainer').classList.add('hidden');
            }
        });
    }
});

function loadSuggestedItems(categoryId) {
    fetch(`/admin/tvet/fee-categories/${categoryId}/suggested-items`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('suggestedItemsContainer');
            const list = document.getElementById('suggestedItemsList');

            if (data.items && data.items.length > 0) {
                list.innerHTML = '';
                data.items.forEach(item => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition-colors';
                    button.textContent = item;
                    button.onclick = () => {
                        document.getElementById('item_name').value = item;
                    };
                    list.appendChild(button);
                });
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        });
}
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

    .required:after {
        content: " *";
        color: #EF4444;
    }

    .hidden {
        display: none;
    }
</style>
@endsection
