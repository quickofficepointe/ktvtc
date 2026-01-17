@extends('ktvtc.admin.layout.adminlayout')
@section('content')

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Event Applications</h1>
        <div class="flex items-center gap-3">
            <button onclick="exportToExcel()" class="flex items-center gap-2 border border-primary text-primary hover:bg-primary hover:text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-download"></i>
                <span>Export Excel</span>
            </button>
        </div>
    </div>

    <!-- Event Applications Table -->
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent/Guardian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendees</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MPESA Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($applications as $application)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $application->event->title ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $application->event->event_type ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $application->parent_name }}</div>
                            <div class="text-sm text-gray-500">{{ $application->parent_email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $application->parent_contact }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $application->number_of_people }}
                                </span>
                                @if($application->attendees->count() > 0)
                                <button class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                        onclick="showAttendees('{{ $application->id }}')"
                                        title="{{ $application->attendees->pluck('name')->join(', ') }}">
                                    <i class="fas fa-users"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            KSh {{ number_format($application->total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm bg-gray-100 px-2 py-1 rounded text-gray-800">
                                {{ $application->mpesa_reference_number }}
                            </code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'completed' => 'bg-blue-100 text-blue-800'
                                ];
                                $color = $statusColors[$application->application_status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                {{ ucfirst($application->application_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $application->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <!-- View Button -->
                                <a href="{{ route('admin.event-applications.show', $application->id) }}"
                                   class="text-primary hover:text-red-800 transition-colors duration-200"
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
                                               onclick="updateStatus('{{ $application->id }}', 'confirmed')"
                                               class="flex items-center px-4 py-2 text-sm text-green-700 hover:bg-green-50 transition-colors duration-150">
                                                <i class="fas fa-check mr-2"></i>
                                                Confirm
                                            </a>
                                            <a href="#"
                                               onclick="updateStatus('{{ $application->id }}', 'cancelled')"
                                               class="flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 transition-colors duration-150">
                                                <i class="fas fa-times mr-2"></i>
                                                Cancel
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <a href="#"
                                               onclick="deleteApplication('{{ $application->id }}')"
                                               class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150">
                                                <i class="fas fa-trash mr-2"></i>
                                                Delete
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
                            <i class="fas fa-calendar-times text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-500 text-lg">No event applications found</p>
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

<!-- Delete Form -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
function updateStatus(applicationId, status) {
    if (confirm('Are you sure you want to update this application status?')) {
        const form = document.getElementById('statusForm');
        form.action = `/admin/event-applications/${applicationId}/status`;
        document.getElementById('statusInput').value = status;
        form.submit();
    }
}

function deleteApplication(applicationId) {
    if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/event-applications/${applicationId}`;
        form.submit();
    }
}

function exportToExcel() {
    alert('Excel export functionality to be implemented');
}

function showAttendees(applicationId) {
    // This would typically show a modal with attendee details
    // For now, we'll keep the tooltip functionality
    alert('Attendee details modal to be implemented');
}

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
@endsection
