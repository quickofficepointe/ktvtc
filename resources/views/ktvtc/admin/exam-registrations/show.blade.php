@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Exam Registration Details')
@section('subtitle', $registration->registration_number ?? 'Registration Details')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Examinations</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Exam Registrations</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $registration->registration_number }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.exam-registrations.slip-pdf', $registration) }}" target="_blank"
       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-print"></i>
        <span>Print Exam Slip</span>
    </a>
    <a href="{{ route('admin.exam-registrations.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Registrations</span>
    </a>
</div>
@endsection

@section('content')
<!-- Header Card -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="bg-gradient-to-r from-primary/10 to-transparent px-6 py-5 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-xl bg-primary-light flex items-center justify-center">
                    <i class="fas fa-file-alt text-primary text-3xl"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h2 class="text-2xl font-bold text-gray-800">{{ $registration->registration_number }}</h2>
                        @php
                            $statusColors = [
                                'pending' => 'yellow',
                                'registered' => 'green',
                                'completed' => 'purple',
                                'failed' => 'red',
                                'deferred' => 'orange',
                            ];
                            $color = $statusColors[$registration->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            <i class="fas fa-circle mr-1 text-{{ $color }}-500 text-xs"></i>
                            {{ ucfirst($registration->status) }}
                        </span>
                    </div>
                    <p class="text-gray-600 mt-1">
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                            @if($registration->exam_body == 'KNEC') bg-red-100 text-red-800
                            @elseif($registration->exam_body == 'CDACC') bg-blue-100 text-blue-800
                            @elseif($registration->exam_body == 'NITA') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif mr-2">
                            {{ $registration->exam_body }}
                        </span>
                        {{ $registration->exam_type }}
                    </p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-2">
                @if($registration->status == 'pending')
                    <form action="{{ route('admin.exam-registrations.registered', $registration) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Mark this registration as registered?')"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm">
                            <i class="fas fa-check-circle mr-2"></i>
                            Mark Registered
                        </button>
                    </form>
                @endif
                @if($registration->status == 'registered' && !$registration->result)
                    <button onclick="enterResult()"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                        <i class="fas fa-pen mr-2"></i>
                        Enter Result
                    </button>
                @endif
                <a href="{{ route('admin.exam-registrations.edit', $registration) }}"
                   class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Main Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Examination Details -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-file-alt text-primary mr-2"></i>
                    Examination Details
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Exam Body</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($registration->exam_body == 'KNEC') bg-red-100 text-red-800
                            @elseif($registration->exam_body == 'CDACC') bg-blue-100 text-blue-800
                            @elseif($registration->exam_body == 'NITA') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $registration->exam_body }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Exam Type</p>
                        <p class="text-sm font-medium">{{ $registration->exam_type }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Registration Number</p>
                        <p class="text-sm font-mono font-medium">{{ $registration->registration_number }}</p>
                    </div>
                    @if($registration->index_number)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Index Number</p>
                        <p class="text-sm font-mono">{{ $registration->index_number }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Exam Date</p>
                        <p class="text-sm font-medium">{{ $registration->exam_date ? $registration->exam_date->format('l, F j, Y') : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Exam Time</p>
                        <p class="text-sm">{{ $registration->exam_time ?? 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-500 mb-1">Exam Venue</p>
                        <p class="text-sm">{{ $registration->exam_venue ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Information -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-user-graduate text-primary mr-2"></i>
                    Student Information
                </h3>
            </div>
            <div class="p-6">
                <div class="flex items-start">
                    <div class="w-12 h-12 rounded-full bg-primary-light flex items-center justify-center mr-4">
                        <span class="text-xl font-bold text-primary">
                            {{ substr($registration->student->full_name ?? 'S', 0, 1) }}
                        </span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-800">
                            <a href="{{ route('admin.students.show', $registration->student_id) }}" class="hover:text-primary">
                                {{ $registration->student->full_name ?? 'N/A' }}
                            </a>
                        </h4>
                        <p class="text-sm text-gray-600">{{ $registration->student->student_number ?? 'No ID' }}</p>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="text-sm">{{ $registration->student->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Phone</p>
                                <p class="text-sm">{{ $registration->student->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollment Information -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-book-open text-primary mr-2"></i>
                    Enrollment Information
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Course</p>
                        <p class="text-sm font-medium">{{ $registration->enrollment->course->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Intake</p>
                        <p class="text-sm">{{ $registration->enrollment->intake_period ?? '' }} {{ $registration->enrollment->intake_year ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Enrollment Number</p>
                        <p class="text-sm font-mono">{{ $registration->enrollment->enrollment_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Enrollment Status</p>
                        <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ ucfirst($registration->enrollment->status ?? 'N/A') }}
                        </span>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.enrollments.show', $registration->enrollment_id) }}"
                       class="text-primary hover:text-primary-dark text-sm">
                        View Full Enrollment <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="space-y-6">
        <!-- Result Card -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-star text-primary mr-2"></i>
                    Exam Result
                </h3>
            </div>
            <div class="p-6">
                @if($registration->result)
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full
                            {{ $registration->result == 'Pass' ? 'bg-green-100' : 'bg-red-100' }} mb-4">
                            <i class="fas {{ $registration->result == 'Pass' ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} text-3xl"></i>
                        </div>
                        <p class="text-2xl font-bold {{ $registration->result == 'Pass' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $registration->result }}
                        </p>
                        @if($registration->grade)
                            <p class="text-lg text-gray-600 mt-1">Grade: {{ $registration->grade }}</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-hourglass-half text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">Result not yet available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Fee Information -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                    Fee Information
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Exam Fee:</span>
                        <span class="text-sm font-medium">KES {{ number_format($registration->exam_fee ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Registration Fee:</span>
                        <span class="text-sm font-medium">KES {{ number_format($registration->registration_fee ?? 0, 2) }}</span>
                    </div>
                    <div class="border-t border-gray-200 my-2 pt-2">
                        <div class="flex justify-between font-bold">
                            <span class="text-sm">Total Fee:</span>
                            <span class="text-sm">KES {{ number_format($registration->total_fee ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Amount Paid:</span>
                        <span class="text-sm font-medium text-green-600">KES {{ number_format($registration->fee_paid ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Balance:</span>
                        <span class="text-sm font-bold {{ ($registration->total_fee - $registration->fee_paid) > 0 ? 'text-red-600' : 'text-green-600' }}">
                            KES {{ number_format(($registration->total_fee - $registration->fee_paid), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certificate Information -->
        @if($registration->certificate_number)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-certificate text-primary mr-2"></i>
                    Certificate Information
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-2">
                    <div>
                        <p class="text-xs text-gray-500">Certificate Number</p>
                        <p class="text-sm font-mono font-medium">{{ $registration->certificate_number }}</p>
                    </div>
                    @if($registration->certificate_issue_date)
                    <div>
                        <p class="text-xs text-gray-500">Issue Date</p>
                        <p class="text-sm">{{ $registration->certificate_issue_date->format('F j, Y') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Timeline -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-history text-primary mr-2"></i>
                    Timeline
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-plus text-green-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Registration Created</p>
                            <p class="text-xs text-gray-500">{{ $registration->created_at->format('M j, Y \a\t h:i A') }}</p>
                        </div>
                    </div>

                    @if($registration->registration_date && $registration->registration_date->format('Y-m-d') != $registration->created_at->format('Y-m-d'))
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-calendar-check text-blue-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Registration Date</p>
                            <p class="text-xs text-gray-500">{{ $registration->registration_date->format('M j, Y') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($registration->status == 'registered' && $registration->updated_at != $registration->created_at)
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-check-circle text-purple-600 text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Marked as Registered</p>
                            <p class="text-xs text-gray-500">{{ $registration->updated_at->format('M j, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($registration->result)
                    <div class="flex items-start">
                        <div class="w-6 h-6 rounded-full {{ $registration->result == 'Pass' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas {{ $registration->result == 'Pass' ? 'fa-check' : 'fa-times' }} {{ $registration->result == 'Pass' ? 'text-green-600' : 'text-red-600' }} text-xs"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">Result Published: {{ $registration->result }}</p>
                            <p class="text-xs text-gray-500">{{ $registration->updated_at->format('M j, Y \a\t h:i A') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="resultModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('resultModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Enter Exam Result</h3>
                    <button onclick="closeModal('resultModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="resultForm" method="POST" action="{{ route('admin.exam-registrations.result', $registration) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 required">Result</label>
                            <select name="result" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">Select Result</option>
                                <option value="Pass">Pass</option>
                                <option value="Fail">Fail</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Grade</label>
                            <input type="text" name="grade" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., A, B+, Credit">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Certificate Number</label>
                            <input type="text" name="certificate_number" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="If issued">
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('resultModal')" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                <button onclick="submitResult()" class="px-4 py-2 bg-green-600 text-white rounded-lg">Save Result</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function enterResult() {
        openModal('resultModal');
    }

    function submitResult() {
        document.getElementById('resultForm').submit();
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('resultModal');
        }
    });
</script>

<style>
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }
    .hidden {
        display: none !important;
    }
</style>
@endsection
