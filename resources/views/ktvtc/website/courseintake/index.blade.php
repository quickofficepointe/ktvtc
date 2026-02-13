@extends('ktvtc.website.layout.websitelayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded bg-green-100 text-green-800 border-l-4 border-green-600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded bg-red-100 text-red-800 border-l-4 border-red-600">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header with Add Button --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Course Intakes Management</h1>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-md transition">
            Add New Intake
        </button>
    </div>

    {{-- Intake List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($intakes as $index => $intake)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-dark">{{ $intake->course->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $intake->month }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $intake->year }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            {{ $intake->application_deadline ? \Carbon\Carbon::parse($intake->application_deadline)->format('M d, Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            {{ $intake->notes ? Str::limit($intake->notes, 50) : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($intake->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap space-x-2">
                            <button onclick="openEditModal({{ $intake->id }}, {{ $intake->course_id }}, '{{ $intake->month }}', {{ $intake->year }}, '{{ $intake->application_deadline }}', `{!! addslashes($intake->notes) !!}`, {{ $intake->is_active }})"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Edit
                            </button>
                            <button onclick="confirmDelete('{{ route('course-intakes.destroy', $intake->id) }}')"
                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No intakes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeCreateModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 relative" onclick="event.stopPropagation()">
            <button onclick="closeCreateModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Intake</h3>
            <form action="{{ route('course-intakes.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Course -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Course *</label>
                        <select name="course_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Month *</label>
                        <select name="month" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <option value="">-- Select Month --</option>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                                <option value="{{ $month }}" {{ old('month') == $month ? 'selected' : '' }}>
                                    {{ $month }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year *</label>
                        <input type="number" name="year" value="{{ old('year', date('Y')) }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="2025" required>
                    </div>

                    <!-- Deadline -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Application Deadline</label>
                        <input type="date" name="application_deadline" value="{{ old('application_deadline') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="is_active" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" class="summernote" rows="3"
                              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Additional notes about this intake">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeCreateModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">
                        Add Intake
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeEditModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 relative" onclick="event.stopPropagation()">
            <button onclick="closeEditModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Intake</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Course -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Course *</label>
                        <select id="editCourseId" name="course_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Month -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Month *</label>
                        <select id="editMonth" name="month" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent" required>
                            <option value="">-- Select Month --</option>
                            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                                <option value="{{ $month }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year *</label>
                        <input type="number" id="editYear" name="year"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="2025" required>
                    </div>

                    <!-- Deadline -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Application Deadline</label>
                        <input type="date" id="editDeadline" name="application_deadline"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="editIsActive" name="is_active" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea id="editNotes" class="summernote" name="notes" rows="3"
                              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Additional notes about this intake"></textarea>
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">
                        Update Intake
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }

    function closeCreateModal(event = null) {
        if(event && event.target !== document.getElementById('createModal')) return;
        document.getElementById('createModal').classList.add('hidden');
    }

    // Edit Modal Functions
    function openEditModal(id, courseId, month, year, deadline, notes, isActive) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');

        // Set form action
        document.getElementById('editForm').action = `/course-intakes/${id}`;

        // Fill form fields
        document.getElementById('editCourseId').value = courseId;
        document.getElementById('editMonth').value = month;
        document.getElementById('editYear').value = year;
        document.getElementById('editDeadline').value = deadline;
        document.getElementById('editNotes').value = notes;
        document.getElementById('editIsActive').value = isActive ? '1' : '0';
    }

    function closeEditModal(event = null) {
        if(event && event.target !== document.getElementById('editModal')) return;
        document.getElementById('editModal').classList.add('hidden');
    }

    // Delete Confirmation
    function confirmDelete(url) {
        if(confirm('Are you sure you want to delete this intake?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';

            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>
@endsection
