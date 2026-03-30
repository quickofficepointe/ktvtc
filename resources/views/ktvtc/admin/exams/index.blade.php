@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Exam Bodies')
@section('subtitle', 'Manage examining bodies like CDACC, NITA, KNEC')

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
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Exam Bodies</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="openModal('createModal')"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus-circle"></i>
        <span>Add Exam Body</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Bodies</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($totalBodies ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-building text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Bodies</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($activeBodies ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-check-circle text-success text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Types</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format(\App\Models\ExamType::count() ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-layer-group text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Registrations</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format(\App\Models\ExamRegistration::count() ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fas fa-file-alt text-amber-600 text-xl"></i>
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
                <p class="text-sm text-gray-600 mt-1">Filter exam bodies by status</p>
            </div>
        </div>
    </div>
    <div class="p-6">
        <form id="filterForm" action="{{ route('admin.tvet.exam-bodies.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                <a href="{{ route('admin.tvet.exam-bodies.index') }}"
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

<!-- Exam Bodies Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Exam Bodies</h3>
                <p class="text-sm text-gray-600 mt-1">{{ $examBodies->total() }} bodies found</p>
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
        <table class="w-full" id="examBodiesTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Body</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Types</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($examBodies as $body)
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewExamBody({{ $body->id }})">
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-primary-light flex items-center justify-center mr-3">
                                <i class="fas fa-building text-primary text-sm"></i>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $body->name }}</span>
                                @if($body->website)
                                    <span class="text-xs text-gray-500 block">{{ $body->website }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-mono font-medium text-gray-900">{{ $body->code }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm">
                            @if($body->contact_email)
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope text-xs mr-1 w-4"></i>
                                <span class="text-xs">{{ $body->contact_email }}</span>
                            </div>
                            @endif
                            @if($body->contact_phone)
                            <div class="flex items-center text-gray-600 mt-1">
                                <i class="fas fa-phone-alt text-xs mr-1 w-4"></i>
                                <span class="text-xs">{{ $body->contact_phone }}</span>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-layer-group mr-1"></i>
                            {{ $body->exam_types_count ?? 0 }} Types
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $statusColor = $body->is_active ? 'green' : 'gray';
                            $statusText = $body->is_active ? 'Active' : 'Inactive';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $statusColor }}-500 text-xs"></i>
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-600">
                            {{ $body->created_at->format('M j, Y') }}
                        </div>
                    </td>
                    <td class="py-3 px-6" onclick="event.stopPropagation()">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewExamBody({{ $body->id }})"
                               class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editExamBody({{ $body->id }})"
                               class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition-colors"
                               data-tooltip="Edit Exam Body">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $body->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $body->id }}"
                                     class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if($body->is_active)
                                        <button onclick="updateStatus('{{ $body->id }}', 'deactivate')"
                                                class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-pause-circle mr-2"></i>
                                            Deactivate
                                        </button>
                                        @else
                                        <button onclick="updateStatus('{{ $body->id }}', 'activate')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            Activate
                                        </button>
                                        @endif

                                        <a href="{{ route('admin.tvet.exam-types.index', ['exam_body_id' => $body->id]) }}"
                                           class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-layer-group mr-2"></i>
                                            View Exam Types
                                        </a>

                                        <hr class="my-1 border-gray-200">

                                        <button onclick="deleteExamBody('{{ $body->id }}', '{{ $body->name }}')"
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
                    <td colspan="7" class="py-12 px-6 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-building text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">No exam bodies found</p>
                            <p class="text-gray-400 text-sm mt-1">Get started by creating your first exam body</p>
                            <button onclick="openModal('createModal')"
                               class="mt-4 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Exam Body
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($examBodies instanceof \Illuminate\Pagination\LengthAwarePaginator && $examBodies->total() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $examBodies->firstItem() }}</span> to
                <span class="font-medium">{{ $examBodies->lastItem() }}</span> of
                <span class="font-medium">{{ number_format($examBodies->total()) }}</span> bodies
            </div>
            <div class="flex items-center space-x-2">
                {{ $examBodies->links() }}
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
                        <h3 class="text-lg font-semibold text-gray-800">Create Exam Body</h3>
                        <p class="text-sm text-gray-600">Add a new examining body</p>
                    </div>
                    <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('admin.tvet.exam-bodies.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Basic Information
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2 required">
                                        Exam Body Name
                                    </label>
                                    <input type="text"
                                           name="name"
                                           id="create_name"
                                           value="{{ old('name') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="e.g., Kenya National Examinations Council"
                                           required
                                           onkeyup="generateCreateCode()">
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
                                               placeholder="e.g., KNEC"
                                               required>
                                        <button type="button"
                                                onclick="generateCreateCode()"
                                                class="ml-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Short code/abbreviation</p>
                                </div>

                                <!-- Website -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Website
                                    </label>
                                    <input type="url"
                                           name="website"
                                           value="{{ old('website') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="https://www.knec.ac.ke">
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea name="description"
                                              rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                              placeholder="Brief description of the examining body...">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-address-card text-primary mr-2"></i>
                                Contact Information
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Contact Person -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Person
                                    </label>
                                    <input type="text"
                                           name="contact_person"
                                           value="{{ old('contact_person') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="John Doe">
                                </div>

                                <!-- Contact Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Email
                                    </label>
                                    <input type="email"
                                           name="contact_email"
                                           value="{{ old('contact_email') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="info@example.com">
                                </div>

                                <!-- Contact Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Phone
                                    </label>
                                    <input type="text"
                                           name="contact_phone"
                                           value="{{ old('contact_phone') }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                           placeholder="+254 700 000000">
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
                                    <span><strong>Code:</strong> Use standard abbreviations (CDACC, NITA, KNEC)</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                    <span><strong>Exam Types:</strong> You can add certificate levels after creating the body</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle mt-0.5 mr-2 text-blue-600"></i>
                                    <span><strong>Contact Info:</strong> Helps with liaison and communication</span>
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
                    Create Exam Body
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
                        <h3 class="text-lg font-semibold text-gray-800">Edit Exam Body</h3>
                        <p class="text-sm text-gray-600">Update exam body information</p>
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
                    Update Exam Body
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
                        <h3 class="text-lg font-semibold text-gray-800" id="showModalTitle">Exam Body Details</h3>
                        <p class="text-sm text-gray-600" id="showModalSubtitle">View exam body information</p>
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
                    <h3 class="text-lg font-semibold text-gray-800">Delete Exam Body</h3>
                    <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <p class="text-center text-gray-600" id="deleteModalMessage">
                        Are you sure you want to delete this exam body?
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
    function viewExamBody(bodyId) {
        fetch(`/admin/tvet/exam-bodies/${bodyId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('showModalContent').innerHTML = html;
                document.getElementById('showModalTitle').textContent = 'Exam Body Details';
                openModal('showModal');
            })
            .catch(error => {
                console.error('Error loading exam body details:', error);
                alert('Failed to load exam body details');
            });
    }

    function editExamBody(bodyId) {
        fetch(`/admin/tvet/exam-bodies/${bodyId}/edit`)
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
        const bodyIdElement = document.querySelector('#showModalContent [data-body-id]');
        if (bodyIdElement) {
            const bodyId = bodyIdElement.dataset.bodyId;
            closeModal('showModal');
            editExamBody(bodyId);
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
    function generateCreateCode() {
        const name = document.getElementById('create_name').value;
        const codeInput = document.getElementById('create_code');
        if (name && codeInput) {
            // Extract acronym from name (e.g., Kenya National Examinations Council → KNEC)
            const words = name.split(' ');
            let acronym = '';
            if (words.length > 1) {
                acronym = words.map(word => word[0]).join('').toUpperCase();
            } else {
                acronym = name.substring(0, 4).toUpperCase();
            }
            codeInput.value = acronym;
        }
    }

    // ============ ACTION MENU ============
    function toggleActionMenu(bodyId) {
        const menu = document.getElementById(`actionMenu-${bodyId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${bodyId}`) {
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

    // ============ EXAM BODY ACTIONS ============
    function updateStatus(bodyId, action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action === 'activate'
            ? `/admin/tvet/exam-bodies/${bodyId}/toggle-status`
            : `/admin/tvet/exam-bodies/${bodyId}/toggle-status`;
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }

    function deleteExamBody(bodyId, bodyName) {
        document.getElementById('deleteForm').action = `/admin/tvet/exam-bodies/${bodyId}`;
        document.getElementById('deleteModalMessage').innerHTML = `Are you sure you want to delete <strong>${bodyName}</strong>?`;
        document.getElementById('deleteWarning').innerHTML = 'This will also delete all exam types under this body.';
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
    #examBodiesTable {
        min-width: 1200px;
    }

    @media (max-width: 768px) {
        #examBodiesTable {
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
