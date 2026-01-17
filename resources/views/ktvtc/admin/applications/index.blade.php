@extends('ktvtc.admin.layout.adminlayout')
@section('content')

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Course Applications</h1>
        <div class="flex items-center gap-3">
            <button onclick="exportToExcel()" class="flex items-center gap-2 border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-download"></i>
                <span>Export Excel</span>
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <form id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                            <option value="waiting_list">Waiting List</option>
                        </select>
                    </div>

                    <!-- Course Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                        <select name="course_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Date To Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Apply Filters
                    </button>
                    <button type="reset" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">App Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intake</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($applications as $application)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <strong class="text-sm font-medium text-gray-900">{{ $application->application_number }}</strong>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $application->first_name }} {{ $application->last_name }}
                            </div>
                            <div class="text-sm text-gray-500">ID: {{ $application->id_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $application->course->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $application->intake_period }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $application->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $application->phone }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'under_review' => 'bg-blue-100 text-blue-800',
                                    'accepted' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'waiting_list' => 'bg-purple-100 text-purple-800'
                                ];
                                $color = $statusColors[$application->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $application->submitted_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <!-- View Button -->
                                <a href="{{ route('admin.applications.show', $application->id) }}"
                                   class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                   title="View Application">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Dropdown Menu -->
                                <div class="relative inline-block text-left">
                                    <button type="button"
                                            class="text-gray-400 hover:text-gray-600 transition-colors duration-200 focus:outline-none"
                                            onclick="toggleDropdown('dropdown-{{ $application->id }}')">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div id="dropdown-{{ $application->id }}"
                                         class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                        <div class="py-1">
                                            <a href="#"
                                               onclick="updateStatus('{{ $application->id }}', 'accepted')"
                                               class="flex items-center px-4 py-2 text-sm text-green-700 hover:bg-green-50 transition-colors duration-150">
                                                <i class="fas fa-check mr-2"></i>
                                                Accept
                                            </a>
                                            <a href="#"
                                               onclick="updateStatus('{{ $application->id }}', 'rejected')"
                                               class="flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors duration-150">
                                                <i class="fas fa-times mr-2"></i>
                                                Reject
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <a href="{{ route('admin.applications.show', $application->id) }}"
                                               class="flex items-center px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 transition-colors duration-150">
                                                <i class="fas fa-file-pdf mr-2"></i>
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-500 text-lg">No applications found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-4 border-t border-gray-200">
            <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} of {{ $applications->total() }} entries
            </div>
            <div class="flex space-x-2">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form id="statusForm" method="POST" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="status" id="statusInput">
</form>

<script>
function updateStatus(applicationId, status) {
    if (confirm('Are you sure you want to update this application status?')) {
        const form = document.getElementById('statusForm');
        form.action = `/admin/applications/${applicationId}/status`;
        document.getElementById('statusInput').value = status;
        form.submit();
    }
}

function exportToExcel() {
    // Implement Excel export functionality
    alert('Excel export functionality to be implemented');
}

// Filter form submission
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route('admin.applications.index') }}?' + params.toString();
});

// Dropdown toggle function
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.classList.toggle('hidden');

    // Close other dropdowns
    document.querySelectorAll('[id^="dropdown-"]').forEach(otherDropdown => {
        if (otherDropdown.id !== dropdownId) {
            otherDropdown.classList.add('hidden');
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.matches('[onclick*="toggleDropdown"]') && !event.target.closest('[id^="dropdown-"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});
</script>

<style>
/* Custom styles for better table alignment */
.table td {
    vertical-align: middle;
}
</style>
@endsection
