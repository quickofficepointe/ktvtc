@extends('ktvtc.admin.layout.adminlayout')
@section('content')

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6">
        <div class="mb-4 lg:mb-0">
            <h1 class="text-2xl font-bold text-gray-800">Application Details</h1>
            <p class="text-gray-600 mt-1">{{ $application->application_number }}</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <a href="{{ route('admin.applications.index') }}" class="flex items-center justify-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>

            <!-- Status Dropdown -->
            <div class="relative inline-block text-left">
                <button type="button"
                        class="flex items-center justify-center gap-2 bg-primary hover:bg-red-800 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                        onclick="toggleStatusDropdown()">
                    <span>Update Status</span>
                    <i class="fas fa-chevron-down text-sm"></i>
                </button>

                <div id="statusDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div class="py-1">
                        <a href="#" onclick="updateStatus('pending')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-primary transition-colors duration-150">Pending</a>
                        <a href="#" onclick="updateStatus('under_review')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-primary transition-colors duration-150">Under Review</a>
                        <a href="#" onclick="updateStatus('accepted')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-primary transition-colors duration-150">Accepted</a>
                        <a href="#" onclick="updateStatus('rejected')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-primary transition-colors duration-150">Rejected</a>
                        <a href="#" onclick="updateStatus('waiting_list')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-primary transition-colors duration-150">Waiting List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content - 2/3 width -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Applicant Information Card -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="bg-primary text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-user-graduate"></i>
                        Applicant Information
                    </h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Full Name</p>
                                <p class="text-gray-900">{{ $application->first_name }} {{ $application->last_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">ID Type & Number</p>
                                <p class="text-gray-900">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                        {{ $application->id_type === 'birth_certificate' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $application->id_type_label }}
                                    </span>
                                    <span class="ml-2">{{ $application->id_number }}</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Date of Birth</p>
                                <p class="text-gray-900">{{ $application->date_of_birth->format('M j, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Gender</p>
                                <p class="text-gray-900">{{ ucfirst($application->gender) }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email</p>
                                <p class="text-gray-900">{{ $application->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Phone</p>
                                <p class="text-gray-900">{{ $application->phone }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Application Type</p>
                                <p class="text-gray-900">{{ ucfirst($application->application_type) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status</p>
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
                                    {{ $application->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course & Campus Information Card -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="bg-red-700 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-book"></i>
                        Course & Campus Information
                    </h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Course</p>
                                <p class="text-gray-900">{{ $application->course->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Preferred Campus</p>
                                <p class="text-gray-900">
                                    {{ $application->campus->name ?? 'Not Specified' }}
                                    @if($application->campus)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $application->campus->address }}, {{ $application->campus->city }}
                                    </div>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Intake Period</p>
                                <p class="text-gray-900">{{ $application->intake_period }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Study Mode</p>
                                <p class="text-gray-900">{{ $application->study_mode_label }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Education Level</p>
                                <p class="text-gray-900">{{ $application->education_level }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">School</p>
                                <p class="text-gray-900">{{ $application->school_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Graduation Year</p>
                                <p class="text-gray-900">{{ $application->graduation_year }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Mean Grade</p>
                                <p class="text-gray-900">{{ $application->mean_grade }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Emergency Information Card -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-address-book"></i>
                        Contact & Emergency Information
                    </h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <h6 class="font-medium text-gray-900 border-b pb-2">Contact Information</h6>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Address</p>
                                <p class="text-gray-900">{{ $application->address ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">City</p>
                                <p class="text-gray-900">{{ $application->city ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">County</p>
                                <p class="text-gray-900">{{ $application->county ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Postal Code</p>
                                <p class="text-gray-900">{{ $application->postal_code ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <h6 class="font-medium text-gray-900 border-b pb-2">Emergency Contact</h6>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Name</p>
                                <p class="text-gray-900">{{ $application->emergency_contact_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Phone</p>
                                <p class="text-gray-900">{{ $application->emergency_contact_phone }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Relationship</p>
                                <p class="text-gray-900">{{ $application->emergency_contact_relationship }}</p>
                            </div>
                        </div>
                    </div>

                    @if($application->special_needs)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h6 class="font-medium text-gray-900 mb-3">Special Needs</h6>
                        <p class="text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $application->special_needs }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar - 1/3 width -->
        <div class="space-y-6">
            <!-- Documents Card -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="bg-red-800 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-file"></i>
                        Documents
                    </h5>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ Storage::url($application->id_document) }}"
                           target="_blank"
                           class="flex items-center justify-between w-full border border-primary text-primary hover:bg-primary hover:text-white px-4 py-3 rounded-lg transition-colors duration-200">
                            <div class="flex items-center gap-2">
                                @if($application->id_type === 'birth_certificate')
                                <i class="fas fa-certificate"></i>
                                <span>Birth Certificate</span>
                                @else
                                <i class="fas fa-id-card"></i>
                                <span>National ID</span>
                                @endif
                            </div>
                            <i class="fas fa-external-link-alt text-sm"></i>
                        </a>
                        <a href="{{ Storage::url($application->education_certificates) }}"
                           target="_blank"
                           class="flex items-center justify-between w-full border border-primary text-primary hover:bg-primary hover:text-white px-4 py-3 rounded-lg transition-colors duration-200">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Education Certificates</span>
                            </div>
                            <i class="fas fa-external-link-alt text-sm"></i>
                        </a>
                        <a href="{{ Storage::url($application->passport_photo) }}"
                           target="_blank"
                           class="flex items-center justify-between w-full border border-primary text-primary hover:bg-primary hover:text-white px-4 py-3 rounded-lg transition-colors duration-200">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-camera"></i>
                                <span>Passport Photo</span>
                            </div>
                            <i class="fas fa-external-link-alt text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Application Meta Card -->
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <div class="bg-gray-700 text-white px-6 py-4 rounded-t-lg">
                    <h5 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        Application Meta
                    </h5>
                </div>
                <div class="p-6 space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Submitted</p>
                        <p class="text-gray-900">{{ $application->submitted_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Age</p>
                        <p class="text-gray-900">{{ $application->age }} years old</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">IP Address</p>
                        <p class="text-gray-900">{{ $application->ip_address }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">User Agent</p>
                        <p class="text-gray-500 text-sm">{{ Str::limit($application->user_agent, 50) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Form -->
<form id="statusForm" method="POST" action="{{ route('admin.applications.updateStatus', $application->id) }}" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="status" id="statusInput">
</form>

<script>
function updateStatus(status) {
    if (confirm('Are you sure you want to update this application status to "' + status + '"?')) {
        document.getElementById('statusInput').value = status;
        document.getElementById('statusForm').submit();
    }
}

function toggleStatusDropdown() {
    const dropdown = document.getElementById('statusDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('statusDropdown');
    const button = event.target.closest('button');

    if (!button || !button.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
@endsection
