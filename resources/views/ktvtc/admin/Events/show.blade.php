@extends('ktvtc.admin.layout.adminlayout')
@section('content')

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div class="mb-4 md:mb-0">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Event Application Details</h1>
            <p class="text-gray-600 mt-2">Application for {{ $application->event->title ?? 'Event' }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('admin.event-applications.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to List
            </a>
            <div class="relative">
                <button type="button"
                        class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-800 transition-colors">
                    Update Status
                    <i class="fas fa-chevron-down ml-2"></i>
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden hover:block group-hover:block">
                    <div class="py-1">
                        <a href="#" onclick="updateStatus('pending')"
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Pending</a>
                        <a href="#" onclick="updateStatus('confirmed')"
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Confirmed</a>
                        <a href="#" onclick="updateStatus('cancelled')"
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Cancelled</a>
                        <a href="#" onclick="updateStatus('completed')"
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Completed</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Parent/Guardian Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-primary px-6 py-4">
                    <div class="flex items-center">
                        <i class="fas fa-user text-white mr-3"></i>
                        <h2 class="text-lg font-semibold text-white">Parent/Guardian Information</h2>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <p class="text-gray-900">{{ $application->parent_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <a href="mailto:{{ $application->parent_email }}"
                                   class="text-primary hover:text-red-800 transition-colors">
                                    {{ $application->parent_email }}
                                </a>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <a href="tel:{{ $application->parent_contact }}"
                                   class="text-primary hover:text-red-800 transition-colors">
                                    {{ $application->parent_contact }}
                                </a>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">MPESA Reference</label>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">{{ $application->mpesa_reference_number }}</code>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
                                <p class="text-lg font-semibold text-green-600">
                                    KSh {{ number_format($application->total_amount) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Application Status</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $application->status_badge }}">
                                    {{ ucfirst($application->application_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-500 px-6 py-4">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt text-white mr-3"></i>
                        <h2 class="text-lg font-semibold text-white">Event Information</h2>
                    </div>
                </div>
                <div class="p-6">
                    @if($application->event)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                                <p class="text-gray-900">{{ $application->event->title }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($application->event->event_type) }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Date</label>
                                <p class="text-gray-900">
                                    {{ $application->event->event_start_date->format('l, F j, Y') }}
                                    @if($application->event->event_start_date->format('H:i') != '00:00')
                                        at {{ $application->event->event_start_date->format('g:i A') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <p class="text-gray-900">{{ $application->event->location }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price per Person</label>
                                <p class="text-gray-900">
                                    KSh {{ number_format($application->event->is_paid ? $application->event->price : 0) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                                <p class="text-gray-900">
                                    {{ $application->event->registered_attendees }} /
                                    {{ $application->event->max_attendees ?? 'Unlimited' }} registered
                                </p>
                            </div>
                        </div>
                    </div>
                    @if($application->event->short_description)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Description</label>
                        <p class="text-gray-700">{{ $application->event->short_description }}</p>
                    </div>
                    @endif
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                            <p class="text-yellow-800">Event information not available. This event may have been deleted.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Attendees Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-green-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-users text-white mr-3"></i>
                            <h2 class="text-lg font-semibold text-white">Attendees Information</h2>
                        </div>
                        <span class="bg-white text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                            {{ $application->number_of_people }} person(s)
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Full Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">School/Institution</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Age</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($application->attendees as $index => $attendee)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $attendee->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $attendee->school }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $attendee->age }} years
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-yellow-500 px-6 py-4">
                    <div class="flex items-center">
                        <i class="fas fa-bolt text-yellow-900 mr-3"></i>
                        <h2 class="text-lg font-semibold text-yellow-900">Quick Actions</h2>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="mailto:{{ $application->parent_email }}?subject=Regarding your event application for {{ $application->event->title ?? 'the event' }}&body=Dear {{ $application->parent_name }},"
                           class="w-full flex items-center justify-center px-4 py-2 border border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                            <i class="fas fa-envelope mr-2"></i>
                            Email Parent
                        </a>
                        <a href="tel:{{ $application->parent_contact }}"
                           class="w-full flex items-center justify-center px-4 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-600 hover:text-white transition-colors">
                            <i class="fas fa-phone mr-2"></i>
                            Call Parent
                        </a>
                        <a href="https://wa.me/{{ $application->parent_contact }}?text=Hello {{ $application->parent_name }}, regarding your event application for {{ $application->event->title ?? 'the event' }}"
                           target="_blank"
                           class="w-full flex items-center justify-center px-4 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-600 hover:text-white transition-colors">
                            <i class="fab fa-whatsapp mr-2"></i>
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <!-- Application Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gray-600 px-6 py-4">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-white mr-3"></i>
                        <h2 class="text-lg font-semibold text-white">Application Details</h2>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Application Date</label>
                            <p class="text-gray-900">
                                {{ $application->created_at ? $application->created_at->format('F j, Y \\a\\t g:i A') : 'Date not available' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Number of Attendees</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary text-white">
                                {{ $application->number_of_people }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount Paid</label>
                            <p class="text-xl font-semibold text-green-600">
                                KSh {{ number_format($application->total_amount) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Reference</label>
                            <code class="block bg-gray-100 px-3 py-2 rounded text-sm font-mono break-all">
                                {{ $application->mpesa_reference_number }}
                            </code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-lg shadow-sm border border-red-300 overflow-hidden">
                <div class="bg-red-600 px-6 py-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-white mr-3"></i>
                        <h2 class="text-lg font-semibold text-white">Danger Zone</h2>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Deleting this application will permanently remove it from the system and cannot be undone.
                    </p>
                    <button type="button"
                            onclick="deleteApplication()"
                            class="w-full flex items-center justify-center px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-colors">
                        <i class="fas fa-trash mr-2"></i>
                        Delete Application
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form id="statusForm" method="POST" action="}" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="status" id="statusInput">
</form>

<!-- Delete Form -->
<form id="deleteForm" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function updateStatus(status) {
    if (confirm('Are you sure you want to update this application status to "' + status + '"?')) {
        document.getElementById('statusInput').value = status;
        document.getElementById('statusForm').submit();
    }
}

function deleteApplication() {
    if (confirm('⚠️ ARE YOU SURE?\n\nThis will permanently delete this event application and all associated attendee records. This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownButton = document.querySelector('.relative button');
    const dropdownMenu = document.querySelector('.absolute');

    dropdownButton.addEventListener('click', function() {
        dropdownMenu.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
});
</script>

<style>
.bg-warning { background-color: #f59e0b; }
.bg-success { background-color: #10b981; }
.bg-danger { background-color: #ef4444; }
.bg-info { background-color: #3b82f6; }
</style>
@endsection
