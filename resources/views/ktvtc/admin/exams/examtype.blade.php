@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Exam Types')
@section('subtitle', 'Manage qualification levels for each exam body')

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
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Exams</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Exam Bodies</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Exam Types</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="openModal('createModal')"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Exam Type</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Types</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalTypes ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-layer-group text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Types</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($activeTypes ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-success text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Exam Bodies</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format(\App\Models\ExamBody::count() ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-building text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

   <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600">Levels</p>
            <p class="text-2xl font-bold text-gray-800 mt-2">
                {{ count(array_filter($levelBreakdown, fn($count) => $count > 0)) }}
            </p>
        </div>
        <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
            <i class="fas fa-chart-line text-amber-600 text-xl"></i>
        </div>
    </div>
</div>
</div>

<!-- Level Distribution Bar (Quick Stats) -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    @foreach($levelBreakdown ?? [] as $level => $count)
    <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
        <span class="text-xs text-gray-500">Level {{ $level }}</span>
        <p class="text-lg font-bold text-gray-800">{{ $count }}</p>
    </div>
    @endforeach
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                <p class="text-sm text-gray-600 mt-1">Filter exam types by body, level and status</p>
            </div>
        </div>
    </div>
    <div class="p-6">
        <form id="filterForm" action="{{ route('admin.tvet.exam-types.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Exam Body Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exam Body</label>
                    <select name="exam_body_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Bodies</option>
                        @foreach($examBodies as $body)
                            <option value="{{ $body->id }}" {{ request('exam_body_id') == $body->id ? 'selected' : '' }}>
                                {{ $body->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Level Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                    <select name="level" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Levels</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                                Level {{ $level }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="yes" {{ request('is_active') == 'yes' ? 'selected' : '' }}>Active</option>
                        <option value="no" {{ request('is_active') == 'no' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Name, code or description..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('admin.tvet.exam-types.index') }}"
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

<!-- Exam Types Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Exam Types / Qualifications</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $examTypes->total() }} types found</p>
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
        <table class="w-full" id="examTypesTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Type</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Body</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statistics</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($examTypes as $type)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewExamType({{ $type->id }})">
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas {{ $type->examBody->code == 'CDACC' ? 'fa-graduation-cap' : ($type->examBody->code == 'NITA' ? 'fa-certificate' : 'fa-file-alt') }} text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $type->name }}</span>
                                @if($type->description)
                                    <span class="text-xs text-gray-500 block">{{ Str::limit($type->description, 40) }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-mono font-medium text-gray-900">{{ $type->code }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-900">{{ $type->examBody->name }}</span>
                            <span class="ml-2 text-xs text-gray-500">({{ $type->examBody->code }})</span>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @if($type->level)
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Level {{ $type->level }}
                            </span>
                        @else
                            <span class="text-gray-400 text-sm">N/A</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @if($type->duration_months)
                            <span class="text-sm text-gray-900">{{ $type->duration_months }} months</span>
                        @else
                            <span class="text-gray-400 text-sm">N/A</span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColor = $type->is_active ? 'green' : 'gray';
                            $statusText = $type->is_active ? 'Active' : 'Inactive';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i>
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex space-x-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700" title="Fee Templates">
                                <i class="fas fa-file-invoice mr-1"></i>
                                {{ $type->fee_templates_count ?? 0 }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-50 text-green-700" title="Exam Registrations">
                                <i class="fas fa-user-graduate mr-1"></i>
                                {{ $type->exam_registrations_count ?? 0 }}
                            </span>
                        </div>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewExamType({{ $type->id }})"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editExamType({{ $type->id }})"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Exam Type">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $type->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $type->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($type->is_active)
                                        <button onclick="updateStatus('{{ $type->id }}', 'deactivate')"
                                                class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-pause-circle mr-2"></i>
                                            Deactivate
                                        </button>
                                        @else
                                        <button onclick="updateStatus('{{ $type->id }}', 'activate')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Activate
                                        </button>
                                        @endif

                                        <a href="{{ route('admin.tvet.course-fee-templates.index', ['exam_type_id' => $type->id]) }}"
                                           class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-file-invoice mr-2"></i>
                                            View Fee Templates
                                        </a>

                                        <a href="{{ route('admin.tvet.exam-registrations.index', ['exam_type_id' => $type->id]) }}"
                                           class="w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-user-graduate mr-2"></i>
                                            View Registrations
                                        </a>

                                        <hr class="my-1 border-gray-200">

                                        <button onclick="deleteExamType('{{ $type->id }}', '{{ $type->name }}')"
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
                            <i class="fas fa-layer-group text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No exam types found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first exam type</p>
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Exam Type
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($examTypes instanceof \Illuminate\Pagination\LengthAwarePaginator && $examTypes->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $examTypes->firstItem() }}</span> to
                <span class="font-medium">{{ $examTypes->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($examTypes->total()) }}</span> types
            </div>
            <div class="flex items-center space-x-2">
                {{ $examTypes->links() }}
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
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Create Exam Type</h3>
                        <p class="text-sm text-gray-600">Add a new qualification level for an exam body</p>
                    </div>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.tvet.exam-types.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Basic Information
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Exam Body -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Exam Body
                                    </label>
                                    <select name="exam_body_id"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                            required
                                            onchange="updateCreateCode()">
                                        <option value="">Select Exam Body</option>
                                        @foreach($examBodies as $body)
                                            <option value="{{ $body->id }}" data-code="{{ $body->code }}" {{ old('exam_body_id') == $body->id ? 'selected' : '' }}>
                                                {{ $body->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Exam Type Name
                                    </label>
                                    <input type="text"
                                           name="name"
                                           id="create_name"
                                           value="{{ old('name') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="e.g., Certificate, Diploma, Artisan"
                                           required
                                           onkeyup="updateCreateCode()">
                                </div>

                                <!-- Code -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Code
                                    </label>
                                    <div class="flex">
                                        <input type="text"
                                               name="code"
                                               id="create_code"
                                               value="{{ old('code') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent font-mono"
                                               placeholder="e.g., CERT"
                                               required>
                                        <button type="button"
                                                onclick="updateCreateCode()"
                                                class="ml-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Unique code within the exam body</p>
                                </div>

                                <!-- Level -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        NQF Level
                                    </label>
                                    <select name="level"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <option value="">Not Specified</option>
                                        @for($i = 1; $i <= 6; $i++)
                                            <option value="{{ $i }}" {{ old('level') == $i ? 'selected' : '' }}>
                                                Level {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">National Qualification Framework level</p>
                                </div>

                                <!-- Duration -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Duration (Months)
                                    </label>
                                    <input type="number"
                                           name="duration_months"
                                           value="{{ old('duration_months') }}"
                                           min="1"
                                           max="60"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="e.g., 12">
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea name="description"
                                              rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                              placeholder="Brief description of this qualification level...">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-flag text-primary mr-2"></i>
                                Status
                            </h4>

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
                        </div>

                        <!-- Quick Tips -->
                        <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                            <h4 class="text-md font-medium text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-lightbulb text-blue-600 mr-2"></i>
                                Quick Tips
                            </h4>
                            <ul class="space-y-2 text-sm text-blue-700">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                    <span><strong>Code format:</strong> CERT, DIP, ART, HDIP - unique per exam body</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                    <span><strong>Levels:</strong> Follow the NQF levels (1-6) for consistency</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                    <span><strong>Duration:</strong> Helps in calculating course length automatically</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                    <span><strong>Example:</strong> CDACC → Certificate (Level 3), Diploma (Level 5)</span>
                                </li>
                            </ul>
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
                        onclick="document.getElementById('createForm').submit()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Create Exam Type
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
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Edit Exam Type</h3>
                        <p class="text-sm text-gray-600">Update exam type information</p>
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
                    Update Exam Type
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
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-white z-10 pb-2 border-b">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800" id="showModalTitle">Exam Type Details</h3>
                        <p class="text-sm text-gray-600" id="showModalSubtitle">View exam type information</p>
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
                    Edit
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Exam Type</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this exam type?
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
<script>
    // ============ INITIALIZATION ============
    document.addEventListener('DOMContentLoaded', function() {
        initializeQuickSearch();
    });

    // ============ TABLE FUNCTIONS ============
    function viewExamType(typeId) {
        fetch(`/admin/tvet/exam-types/${typeId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('showModalContent').innerHTML = html;
                document.getElementById('showModalTitle').textContent = 'Exam Type Details';
                openModal('showModal');
            })
            .catch(error => {
                console.error('Error loading exam type details:', error);
                alert('Failed to load exam type details');
            });
    }

    function editExamType(typeId) {
        fetch(`/admin/tvet/exam-types/${typeId}/edit`)
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
        const typeIdElement = document.querySelector('#showModalContent [data-type-id]');
        if (typeIdElement) {
            const typeId = typeIdElement.dataset.typeId;
            closeModal('showModal');
            editExamType(typeId);
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

    // ============ CODE GENERATION ============
    function updateCreateCode() {
        const examBodySelect = document.querySelector('select[name="exam_body_id"]');
        const nameInput = document.getElementById('create_name');
        const codeInput = document.getElementById('create_code');

        if (!codeInput) return;

        const selectedOption = examBodySelect.options[examBodySelect.selectedIndex];
        const examBodyCode = selectedOption ? selectedOption.dataset.code : '';

        if (nameInput && nameInput.value) {
            // Generate code from name
            let nameCode = '';
            const name = nameInput.value.toUpperCase();

            if (name.includes('CERTIFICATE')) nameCode = 'CERT';
            else if (name.includes('DIPLOMA')) nameCode = 'DIP';
            else if (name.includes('ARTISAN')) nameCode = 'ART';
            else if (name.includes('HIGHER DIPLOMA')) nameCode = 'HDIP';
            else if (name.includes('MASTERS')) nameCode = 'MSC';
            else if (name.includes('BACHELOR')) nameCode = 'BSC';
            else {
                // Take first 3-4 letters
                const words = name.split(' ');
                if (words.length > 1) {
                    nameCode = words.map(w => w[0]).join('').substring(0, 4);
                } else {
                    nameCode = name.substring(0, 4);
                }
            }

            if (examBodyCode) {
                codeInput.value = `${examBodyCode}-${nameCode}`;
            } else {
                codeInput.value = nameCode;
            }
        }
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(typeId) {
        const menu = document.getElementById(`actionMenu-${typeId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${typeId}`) {
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

    // ============ EXAM TYPE ACTIONS ============
    function updateStatus(typeId, action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action === 'activate'
            ? `/admin/tvet/exam-types/${typeId}/toggle-status`
            : `/admin/tvet/exam-types/${typeId}/toggle-status`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function deleteExamType(typeId, typeName) {
        document.getElementById('deleteForm').action = `/admin/tvet/exam-types/${typeId}`;
        document.getElementById('deleteModalMessage').innerHTML = `Are you sure you want to delete <strong>${typeName}</strong>?`;
        document.getElementById('deleteWarning').innerHTML = 'This will affect fee templates and exam registrations using this type.';
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
    #examTypesTable {
        min-width: 1300px;
    }

    @media (max-width: 768px) {
        #examTypesTable {
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
</style>
@endsection
