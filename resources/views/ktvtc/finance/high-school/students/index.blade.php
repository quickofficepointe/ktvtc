@extends('ktvtc.finance.layouts.app')

@section('title', 'High School Students')
@section('subtitle', 'Manage all high school students')

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('hs-students.create') }}" class="bg-primary hover:bg-primary-dark text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-plus mr-2"></i> Add Student
    </a>
    <a href="{{ route('hs-students.import') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-import mr-2"></i> Import
    </a>
    <div class="relative inline-block text-left" x-data="{ open: false }">
        <button @click="open = !open" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-file-export mr-2"></i> Export
            <i class="fas fa-chevron-down ml-2 text-xs"></i>
        </button>
        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border z-10 py-1">
            <a href="{{ route('hs-students.export') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">Export All</a>
            <a href="{{ route('hs-students.export') }}?status=active" class="block px-4 py-2 text-sm hover:bg-gray-100">Export Active</a>
            <a href="{{ route('hs-students.export') }}?status=graduated" class="block px-4 py-2 text-sm hover:bg-gray-100">Export Graduated</a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="finance-card p-3">
            <p class="text-xs text-gray-500">Total Students</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($totalStudents ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-xl font-bold text-green-600">{{ number_format($activeStudents ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">With Cards</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($studentsWithCards ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Without Cards</p>
            <p class="text-xl font-bold text-yellow-600">{{ number_format($studentsWithoutCards ?? 0) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="finance-card p-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Admission, Phone..." class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary w-48 sm:w-64 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Class</label>
                <select name="class" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class }}" {{ request('class') == $class ? 'selected' : '' }}>{{ $class }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Status</label>
                <select name="status" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Gender</label>
                <select name="gender" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All</option>
                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Boarder</label>
                <select name="is_boarder" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All</option>
                    <option value="yes" {{ request('is_boarder') == 'yes' ? 'selected' : '' }}>Boarders</option>
                    <option value="no" {{ request('is_boarder') == 'no' ? 'selected' : '' }}>Day Scholars</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('hs-students.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm ml-1">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="finance-card p-3 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="students-table">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-2 py-2 w-8">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300">
                        </th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600 min-w-[180px]">Student</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Admission</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Class</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">DOB</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Gender</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Boarder</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Parent Phone</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Card</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Balance</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600 w-40">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($students ?? [] as $student)
                        <tr class="hover:bg-gray-50 student-row" data-student-id="{{ $student->id }}">
                            <td class="px-2 py-2">
                                <input type="checkbox" class="student-checkbox rounded border-gray-300" value="{{ $student->id }}">
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center">
                                    <!-- Profile Picture -->
                                    <div class="relative group">
                                        <div class="w-9 h-9 rounded-full bg-gray-200 overflow-hidden flex-shrink-0 cursor-pointer student-avatar"
                                             data-student-id="{{ $student->id }}"
                                             onclick="openPhotoModal({{ $student->id }}, '{{ addslashes($student->full_name) }}')"
                                             title="Click to update profile picture">
                                            @if($student->profile_picture)
                                                <img src="{{ asset('storage/' . $student->profile_picture) }}"
                                                     alt="{{ $student->full_name }}"
                                                     class="w-full h-full object-cover"
                                                     id="avatar-{{ $student->id }}">
                                            @else
                                                <i class="fas fa-user text-gray-400 w-full h-full flex items-center justify-center text-sm"
                                                   id="avatar-{{ $student->id }}"></i>
                                            @endif

                                            <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-full">
                                                <i class="fas fa-camera text-white text-xs"></i>
                                            </div>
                                        </div>

                                        <input type="file"
                                               id="photo-upload-{{ $student->id }}"
                                               class="hidden"
                                               accept="image/*"
                                               capture="environment"
                                               onchange="handleDirectUpload({{ $student->id }}, this)">
                                    </div>
                                    <span class="ml-2 font-medium text-gray-800 text-sm truncate max-w-[120px]">{{ $student->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-sm">{{ $student->admission_number }}</td>
                            <td class="px-3 py-2 text-sm">{{ $student->class }}</td>
                            <td class="px-3 py-2 text-sm">{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm">{{ ucfirst($student->gender ?? 'N/A') }}</td>
                            <td class="px-3 py-2 text-sm">
                                <span class="text-xs px-2 py-0.5 rounded {{ $student->is_boarder ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $student->is_boarder ? 'Boarder' : 'Day' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-sm">{{ $student->parent_phone ?? 'N/A' }}</td>
                            <td class="px-3 py-2">
                                @if($student->cardAccount)
                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-600 rounded">Issued</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded">No Card</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right font-medium">
                                KES {{ number_format($student->cardAccount->balance ?? 0, 2) }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="text-xs px-2 py-0.5 rounded
                                    @if($student->status === 'active') bg-green-100 text-green-600
                                    @elseif($student->status === 'inactive') bg-gray-100 text-gray-500
                                    @else bg-blue-100 text-blue-600 @endif">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <!-- View Button -->
                                    <a href="{{ route('hs-students.show', $student) }}"
                                       class="text-primary hover:text-primary-dark p-1"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <!-- Edit Button -->
                                    <a href="{{ route('hs-students.edit', $student) }}"
                                       class="text-blue-600 hover:text-blue-800 p-1"
                                       title="Edit Student">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- ✅ NEW: Send Funding Link Button -->
                                    @if($student->parent_phone)
                                        <button onclick="sendFundingLink({{ $student->id }}, '{{ addslashes($student->full_name) }}')"
                                                class="text-green-600 hover:text-green-800 p-1"
                                                title="Send Funding Link to Parent">
                                            <i class="fas fa-sms"></i>
                                        </button>
                                    @else
                                        <span class="text-gray-300 p-1" title="No parent phone number">
                                            <i class="fas fa-sms"></i>
                                        </span>
                                    @endif

                                    <!-- Issue Card Button -->
                                    @if(!$student->cardAccount)
                                        <a href="{{ route('cards.create', ['student_id' => $student->id]) }}"
                                           class="text-green-600 hover:text-green-800 p-1"
                                           title="Issue Card">
                                            <i class="fas fa-credit-card"></i>
                                        </a>
                                    @endif

                                    <!-- Delete Button -->
                                    <button onclick="deleteStudent({{ $student->id }}, '{{ addslashes($student->full_name) }}')"
                                            class="text-red-600 hover:text-red-800 p-1"
                                            title="Delete Student">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-3 py-8 text-center text-gray-500">
                                <i class="fas fa-users text-4xl text-gray-300 mb-2 block"></i>
                                No students found. Click "Add Student" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bulk Actions & Pagination -->
        <div class="mt-3 border-t pt-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm text-gray-600">With selected:</span>
                <select id="bulk-action" class="px-3 py-1 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Choose action...</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="delete">Delete</option>
                    <option value="boarder">Make Boarders</option>
                    <option value="day">Make Day Scholars</option>
                    {{-- ✅ NEW: Send Funding Links --}}
                    <option value="send-funding-link">Send Funding Links</option>
                </select>
                <button onclick="bulkAction()" class="px-4 py-1 bg-primary text-white rounded-lg hover:bg-primary-dark text-sm transition">
                    Apply
                </button>
                <span id="selected-count" class="text-sm text-gray-500 ml-2"></span>
            </div>
            <div>
                {{ $students->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div id="photoModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Update Profile Picture</h3>
            <button onclick="closePhotoModal()" class="text-gray-500 hover:text-gray-700 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <p id="modal-student-name" class="text-sm text-gray-600 mb-4">Updating photo for: <span class="font-semibold"></span></p>

        <!-- Camera/Upload Tabs -->
        <div class="flex border-b mb-4">
            <button onclick="switchPhotoTab('upload')" id="tab-upload" class="px-4 py-2 text-sm font-medium text-primary border-b-2 border-primary">
                <i class="fas fa-upload mr-1"></i> Upload
            </button>
            <button onclick="switchPhotoTab('camera')" id="tab-camera" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent">
                <i class="fas fa-camera mr-1"></i> Camera
            </button>
        </div>

        <!-- Upload Tab -->
        <div id="upload-tab">
            <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-primary transition"
                 onclick="document.getElementById('modal-file-input').click()"
                 ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-blue-50')"
                 ondragleave="event.preventDefault(); this.classList.remove('border-primary', 'bg-blue-50')"
                 ondrop="event.preventDefault(); handleDrop(event)">

                <div id="drop-zone-content">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3 block"></i>
                    <p class="text-gray-600 text-sm">Click or drag and drop to upload</p>
                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG up to 2MB</p>
                </div>

                <div id="preview-container" class="hidden">
                    <img id="preview-image" src="" alt="Preview" class="w-32 h-32 rounded-full object-cover mx-auto mb-3 border-4 border-primary">
                    <p id="preview-name" class="text-sm font-medium text-gray-700"></p>
                    <p id="preview-size" class="text-xs text-gray-500"></p>
                </div>
            </div>

            <input type="file" id="modal-file-input" class="hidden" accept="image/*" onchange="handleFileSelect(event)">
        </div>

        <!-- Camera Tab -->
        <div id="camera-tab" class="hidden">
            <div class="relative bg-black rounded-lg overflow-hidden" style="min-height: 300px;">
                <video id="video" class="w-full h-auto" autoplay playsinline></video>
                <canvas id="canvas" class="hidden"></canvas>

                <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-4">
                    <button onclick="switchCamera()" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm transition">
                        <i class="fas fa-sync-alt mr-1"></i> Switch
                    </button>
                    <button onclick="capturePhoto()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm transition">
                        <i class="fas fa-camera mr-1"></i> Capture
                    </button>
                </div>

                <div id="camera-loading" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="text-white text-center">
                        <i class="fas fa-spinner fa-spin text-3xl mb-2 block"></i>
                        <p class="text-sm">Starting camera...</p>
                    </div>
                </div>
            </div>

            <div id="captured-preview" class="hidden mt-3">
                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                    <div class="flex items-center">
                        <img id="captured-image" src="" alt="Captured" class="w-16 h-16 rounded-full object-cover mr-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Photo Captured</p>
                            <p class="text-xs text-gray-500">Click "Use Photo" to upload</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="retakePhoto()" class="text-sm text-gray-500 hover:text-gray-700">
                            <i class="fas fa-undo"></i> Retake
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="modal-student-id">
        <input type="hidden" id="captured-data">

        <div class="flex justify-end space-x-3 mt-4 pt-4 border-t">
            <button onclick="closePhotoModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">Cancel</button>
            <button onclick="uploadModalPhoto()" id="upload-btn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i class="fas fa-upload mr-2"></i> Upload
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Confirm Delete</h3>
            <p class="text-gray-600 text-sm mt-2">Are you sure you want to delete this student? This action cannot be undone.</p>
            <p id="delete-student-name" class="text-sm font-semibold text-red-600 mt-2"></p>
            <div class="flex justify-center space-x-3 mt-4">
                <button onclick="closeDeleteModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">Cancel</button>
                <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

@push('scripts')
<script>
    // ============================================
    // SEND FUNDING LINK - NEW FUNCTION
    // ============================================

    function sendFundingLink(studentId, studentName) {
        if (!confirm(`Send funding link to parent of ${studentName}?`)) {
            return;
        }

        showToast('Sending funding link...', 'info');

        fetch(`/finance/hs-students/${studentId}/send-funding-link`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
})
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Funding link sent successfully!', 'success');
            } else {
                showToast(data.message || 'Failed to send funding link', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to send funding link', 'error');
        });
    }

    // ============================================
    // BULK ACTION - UPDATED
    // ============================================

    function bulkAction() {
        const action = document.getElementById('bulk-action').value;
        if (!action) {
            showToast('Please select an action', 'warning');
            return;
        }

        const selected = document.querySelectorAll('.student-checkbox:checked');
        if (selected.length === 0) {
            showToast('Please select at least one student', 'warning');
            return;
        }

        const studentIds = Array.from(selected).map(cb => cb.value);

        // ✅ NEW: Handle send-funding-link action
        if (action === 'send-funding-link') {
            if (!confirm(`Send funding links to ${selected.length} parent(s)?`)) {
                return;
            }

            showToast('Sending funding links...', 'info');

            const formData = new FormData();
            formData.append('student_ids', JSON.stringify(studentIds));
            formData.append('_token', '{{ csrf_token() }}');

            fetch('/hs-students/bulk/send-funding-links', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Funding links sent successfully!', 'success');
                } else {
                    showToast(data.message || 'Failed to send funding links', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to send funding links', 'error');
            });
            return;
        }

        // Existing bulk actions...
        if (!confirm(`Are you sure you want to ${action} ${selected.length} student(s)?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('student_ids', JSON.stringify(studentIds));
        formData.append('_token', '{{ csrf_token() }}');

        let url = '';
        switch(action) {
            case 'activate':
                url = '/hs-students/bulk/activate';
                break;
            case 'deactivate':
                url = '/hs-students/bulk/deactivate';
                break;
            case 'delete':
                url = '/hs-students/bulk/delete';
                break;
            case 'boarder':
                url = '/hs-students/bulk/boarding';
                formData.append('is_boarder', 'yes');
                break;
            case 'day':
                url = '/hs-students/bulk/boarding';
                formData.append('is_boarder', 'no');
                break;
            default:
                showToast('Invalid action selected', 'error');
                return;
        }

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Action completed successfully', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast(data.message || 'Action failed', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Action failed', 'error');
        });
    }

    // ============================================
    // SELECT ALL / CHECKBOX
    // ============================================

    document.getElementById('select-all')?.addEventListener('change', function() {
        document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = this.checked);
        updateSelectedCount();
    });

    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.student-checkbox:checked').length;
        document.getElementById('selected-count').textContent = selected > 0 ? `${selected} selected` : '';
    }

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================

    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        toast.className = `${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg flex items-center transition-all duration-300 transform translate-x-full`;
        toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info} mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(toast);

        setTimeout(() => toast.classList.remove('translate-x-full'), 10);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // ============================================
    // PHOTO MODAL FUNCTIONS (Keep existing)
    // ============================================

    function openPhotoModal(studentId, studentName) {
        selectedStudentId = studentId;
        document.getElementById('modal-student-id').value = studentId;
        document.getElementById('modal-student-name').querySelector('span').textContent = studentName;
        document.getElementById('photoModal').classList.remove('hidden');
        document.getElementById('drop-zone-content').classList.remove('hidden');
        document.getElementById('preview-container').classList.add('hidden');
        document.getElementById('upload-btn').disabled = true;
        selectedFile = null;
        capturedFile = null;
        document.getElementById('modal-file-input').value = '';
        document.getElementById('captured-preview').classList.add('hidden');
        document.getElementById('captured-data').value = '';
        document.body.style.overflow = 'hidden';
        switchPhotoTab('upload');
    }

    function closePhotoModal() {
        document.getElementById('photoModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        selectedFile = null;
        selectedStudentId = null;
        capturedFile = null;
        stopCamera();
    }

    function switchPhotoTab(tab) {
        currentTab = tab;
        if (tab === 'upload') {
            document.getElementById('tab-upload').className = 'px-4 py-2 text-sm font-medium text-primary border-b-2 border-primary';
            document.getElementById('tab-camera').className = 'px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent';
            document.getElementById('upload-tab').classList.remove('hidden');
            document.getElementById('camera-tab').classList.add('hidden');
            stopCamera();
        } else {
            document.getElementById('tab-camera').className = 'px-4 py-2 text-sm font-medium text-primary border-b-2 border-primary';
            document.getElementById('tab-upload').className = 'px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent';
            document.getElementById('upload-tab').classList.add('hidden');
            document.getElementById('camera-tab').classList.remove('hidden');
            startCamera();
        }
    }

    // Camera functions (keep existing)
    async function startCamera() {
        try {
            document.getElementById('camera-loading').classList.remove('hidden');
            const constraints = {
                video: { facingMode: facingMode, width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            };
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            const video = document.getElementById('video');
            video.srcObject = stream;
            await video.play();
            document.getElementById('camera-loading').classList.add('hidden');
        } catch (error) {
            console.error('Camera error:', error);
            document.getElementById('camera-loading').classList.add('hidden');
            showToast('Unable to access camera. Please check permissions or use upload option.', 'error');
            switchPhotoTab('upload');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        const video = document.getElementById('video');
        video.srcObject = null;
    }

    function switchCamera() {
        facingMode = facingMode === 'environment' ? 'user' : 'environment';
        stopCamera();
        setTimeout(startCamera, 500);
    }

    function capturePhoto() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob(function(blob) {
            const file = new File([blob], 'captured-photo.jpg', { type: 'image/jpeg' });
            capturedFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('captured-image').src = e.target.result;
                document.getElementById('captured-preview').classList.remove('hidden');
                document.getElementById('upload-btn').disabled = false;
                selectedFile = file;
                showToast('Photo captured successfully!', 'success');
            };
            reader.readAsDataURL(file);
        }, 'image/jpeg', 0.9);
    }

    function retakePhoto() {
        capturedFile = null;
        selectedFile = null;
        document.getElementById('captured-preview').classList.add('hidden');
        document.getElementById('upload-btn').disabled = true;
        document.getElementById('captured-data').value = '';
    }

    function handleFileSelect(event) {
        if (event.target.files && event.target.files[0]) {
            validateAndPreview(event.target.files[0]);
        }
    }

    function handleDrop(event) {
        event.preventDefault();
        document.getElementById('drop-zone').classList.remove('border-primary', 'bg-blue-50');
        if (event.dataTransfer.files && event.dataTransfer.files[0]) {
            validateAndPreview(event.dataTransfer.files[0]);
        }
    }

    function validateAndPreview(file) {
        if (file.size > 2 * 1024 * 1024) {
            showToast('File size must be less than 2MB', 'error');
            return;
        }
        if (!file.type.startsWith('image/')) {
            showToast('Please select an image file', 'error');
            return;
        }
        selectedFile = file;
        capturedFile = null;
        document.getElementById('captured-preview').classList.add('hidden');
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview-name').textContent = file.name;
            document.getElementById('preview-size').textContent = (file.size / 1024).toFixed(2) + ' KB';
            document.getElementById('drop-zone-content').classList.add('hidden');
            document.getElementById('preview-container').classList.remove('hidden');
            document.getElementById('upload-btn').disabled = false;
        };
        reader.readAsDataURL(file);
    }

    function uploadModalPhoto() {
        if (!selectedFile || !selectedStudentId) {
            showToast('No photo selected', 'error');
            return;
        }
        const formData = new FormData();
        formData.append('profile_picture', selectedFile);
        formData.append('_token', '{{ csrf_token() }}');
        const btn = document.getElementById('upload-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Uploading...';
        const updateUrl = '/hs-students/' + selectedStudentId + '/update-photo';
        fetch(updateUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const avatar = document.getElementById('avatar-' + selectedStudentId);
                if (avatar) {
                    if (avatar.tagName === 'IMG') {
                        avatar.src = data.photo_url + '?t=' + Date.now();
                    } else {
                        avatar.outerHTML = `<img src="${data.photo_url}?t=${Date.now()}" alt="Student" class="w-full h-full object-cover" id="avatar-${selectedStudentId}">`;
                    }
                }
                showToast('Profile picture updated successfully', 'success');
                closePhotoModal();
            } else {
                showToast(data.message || 'Failed to update photo', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to update photo', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload mr-2"></i> Upload';
        });
    }

    function handleDirectUpload(studentId, input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                showToast('File size must be less than 2MB', 'error');
                input.value = '';
                return;
            }
            if (!file.type.startsWith('image/')) {
                showToast('Please select an image file', 'error');
                input.value = '';
                return;
            }
            const formData = new FormData();
            formData.append('profile_picture', file);
            formData.append('_token', '{{ csrf_token() }}');
            const avatar = document.getElementById('avatar-' + studentId);
            if (avatar) avatar.classList.add('opacity-50');
            const updateUrl = '/hs-students/' + studentId + '/update-photo';
            fetch(updateUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const avatarEl = document.getElementById('avatar-' + studentId);
                    if (avatarEl) {
                        if (avatarEl.tagName === 'IMG') {
                            avatarEl.src = data.photo_url + '?t=' + Date.now();
                        } else {
                            avatarEl.outerHTML = `<img src="${data.photo_url}?t=${Date.now()}" alt="Student" class="w-full h-full object-cover" id="avatar-${studentId}">`;
                        }
                    }
                    showToast('Profile picture updated successfully', 'success');
                } else {
                    showToast(data.message || 'Failed to update photo', 'error');
                }
                if (avatar) avatar.classList.remove('opacity-50');
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to update photo', 'error');
                if (avatar) avatar.classList.remove('opacity-50');
            });
        }
    }

    // Delete functions
    function deleteStudent(studentId, studentName) {
        deleteStudentId = studentId;
        deleteStudentName = studentName;
        document.getElementById('delete-student-name').textContent = '"' + studentName + '"';
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        deleteStudentId = null;
        deleteStudentName = '';
    }

    function confirmDelete() {
        if (!deleteStudentId) return;
        const btn = document.querySelector('#deleteModal .bg-red-600');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';
        const deleteUrl = '/hs-students/' + deleteStudentId;
        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Student deleted successfully', 'success');
                const row = document.querySelector(`.student-row[data-student-id="${deleteStudentId}"]`);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => row.remove(), 300);
                }
                closeDeleteModal();
            } else {
                showToast(data.message || 'Failed to delete student', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to delete student', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash mr-2"></i> Delete';
        });
    }

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCount();
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhotoModal();
            closeDeleteModal();
        }
    });

    // Click outside to close
    document.getElementById('photoModal')?.addEventListener('click', function(e) {
        if (e.target === this) closePhotoModal();
    });
    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Variables for photo upload
    let selectedStudentId = null;
    let selectedFile = null;
    let deleteStudentId = null;
    let deleteStudentName = '';
    let currentTab = 'upload';
    let stream = null;
    let facingMode = 'environment';
    let capturedFile = null;
</script>
@endpush

@push('styles')
<style>
    .finance-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .student-avatar {
        transition: all 0.3s ease;
        position: relative;
    }
    .student-avatar:hover {
        transform: scale(1.08);
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }
    #drop-zone {
        transition: all 0.3s ease;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #drop-zone:hover {
        border-color: #3B82F6;
        background-color: #EFF6FF;
    }
    #photoModal, #deleteModal { animation: fadeIn 0.2s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    #photoModal .bg-white, #deleteModal .bg-white { animation: slideUp 0.3s ease; }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    #toast-container > div {
        transition: all 0.3s ease;
        min-width: 250px;
        max-width: 500px;
    }
    #toast-container > div.translate-x-full { transform: translateX(100%); }
</style>
@endpush
@endsection