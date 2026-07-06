@extends('ktvtc.finance.layouts.app')

@section('title', 'Outstanding Balance Report')
@section('subtitle', 'View all students with outstanding fee balances')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.student-fees.index') }}" class="text-gray-600 hover:text-primary">Student Fees</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">Outstanding Balance</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <button onclick="sendReminders()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-sms mr-2"></i> Send Reminders
    </button>
    <a href="{{ route('finance.student-fees.export', ['report' => 'outstanding']) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center transition">
        <i class="fas fa-print mr-2"></i> Print
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="finance-card p-4 bg-red-50 border-red-200">
            <p class="text-sm text-gray-600">Total Outstanding</p>
            <p class="text-2xl font-bold text-red-600">KES {{ number_format($totalOutstanding ?? 0, 2) }}</p>
        </div>
        <div class="finance-card p-4 bg-yellow-50 border-yellow-200">
            <p class="text-sm text-gray-600">Students with Balance</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($totalStudents ?? 0) }}</p>
        </div>
        <div class="finance-card p-4 bg-blue-50 border-blue-200">
            <p class="text-sm text-gray-600">Average Balance</p>
            <p class="text-2xl font-bold text-blue-600">
                KES {{ number_format($totalStudents > 0 ? $totalOutstanding / $totalStudents : 0, 2) }}
            </p>
        </div>
    </div>

    <!-- Filters -->
    <div class="finance-card p-4">
        <form method="GET" action="{{ route('finance.student-fees.reports.outstanding') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input type="text" name="search" placeholder="Student name or number..." value="{{ request('search') }}" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary w-64">
            </div>
            @if(isset($campuses) && count($campuses) > 0)
            <div>
                <label class="text-xs font-semibold text-gray-600">Campus</label>
                <select name="campus_id" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Campuses</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                            {{ $campus->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Outstanding Students Table -->
    <div class="finance-card p-4">
        <div class="overflow-x-auto">
            <table class="w-full finance-table">
                <thead>
                    <tr>
                        <th class="text-left py-3 px-4">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                        </th>
                        <th class="text-left py-3 px-4">Student</th>
                        <th class="text-left py-3 px-4">Course</th>
                        <th class="text-right py-3 px-4">Total Fees</th>
                        <th class="text-right py-3 px-4">Paid</th>
                        <th class="text-right py-3 px-4">Balance</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments ?? [] as $enrollment)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" class="row-checkbox" value="{{ $enrollment->id }}" data-student="{{ $enrollment->student_id }}" data-phone="{{ $enrollment->student->phone ?? '' }}">
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-medium">{{ $enrollment->student->full_name ?? $enrollment->student_name }}</span>
                                <span class="text-xs text-gray-500 block">{{ $enrollment->student->student_number ?? $enrollment->student_number }}</span>
                            </td>
                            <td class="py-3 px-4 text-sm">{{ $enrollment->course->name ?? $enrollment->course_name }}</td>
                            <td class="py-3 px-4 text-right font-medium">KES {{ number_format($enrollment->total_fees ?? 0, 2) }}</td>
                            <td class="py-3 px-4 text-right text-green-600 font-medium">KES {{ number_format($enrollment->amount_paid ?? 0, 2) }}</td>
                            <td class="py-3 px-4 text-right font-bold
                                @if(($enrollment->balance ?? 0) > 50000) text-red-600
                                @elseif(($enrollment->balance ?? 0) > 20000) text-orange-600
                                @else text-yellow-600 @endif">
                                KES {{ number_format($enrollment->balance ?? 0, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('finance.students.financial', $enrollment->student) }}" class="text-primary hover:text-primary-dark" title="View Financial Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="sendSingleReminder({{ $enrollment->id }})" class="text-yellow-600 hover:text-yellow-800" title="Send Reminder">
                                        <i class="fas fa-sms"></i>
                                    </button>
                                    <a href="{{ route('finance.student-fees.create', ['enrollment_id' => $enrollment->id]) }}" class="text-green-600 hover:text-green-800" title="Record Payment">
                                        <i class="fas fa-credit-card"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl text-green-300 mb-2 block"></i>
                                No outstanding balances found. All students are fully paid!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="py-2 px-4 font-bold">Total</td>
                        <td class="py-2 px-4 text-right font-bold">KES {{ number_format($enrollments->sum('total_fees'), 2) }}</td>
                        <td class="py-2 px-4 text-right font-bold text-green-600">KES {{ number_format($enrollments->sum('amount_paid'), 2) }}</td>
                        <td class="py-2 px-4 text-right font-bold text-red-600">KES {{ number_format($enrollments->sum('balance'), 2) }}</td>
                        <td class="py-2 px-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-4">
            {{ $enrollments->links() }}
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="finance-card p-4 bg-gray-50">
        <div class="flex flex-wrap items-center gap-4">
            <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>
            <button onclick="sendBulkReminders()" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition font-semibold text-sm">
                <i class="fas fa-sms mr-2"></i> Send Reminders
            </button>
            <button onclick="exportSelected()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-semibold text-sm">
                <i class="fas fa-file-export mr-2"></i> Export Selected
            </button>
        </div>
    </div>
</div>

<!-- Send Reminder Modal -->
<div id="reminderModal" class="hidden fixed inset-0 z-50 modal-overlay flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Send Fee Reminder</h3>
            <button onclick="closeModal('reminderModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="reminderForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700">Template</label>
                <select name="template" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="standard">Standard</option>
                    <option value="urgent">Urgent</option>
                    <option value="friendly">Friendly</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div class="mb-4" id="customMessageContainer" style="display:none;">
                <label class="text-sm font-medium text-gray-700">Custom Message</label>
                <textarea name="custom_message" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" rows="4"></textarea>
                <p class="text-xs text-gray-500 mt-1">Use {name}, {balance}, {link}, {course}, {student_number}</p>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1 text-primary"></i>
                    <span id="selectedCount">0</span> students selected
                </p>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('reminderModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-semibold">
                    <i class="fas fa-sms mr-2"></i> Send Reminders
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let selectedStudents = [];

    function toggleAll(checkbox) {
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        selectedStudents = Array.from(checkboxes).map(cb => cb.value);
        document.getElementById('selectedCount').textContent = selectedStudents.length;
    }

    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    function sendReminders() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            toastr.warning('Please select at least one student');
            return;
        }
        const modal = document.getElementById('reminderModal');
        const form = document.getElementById('reminderForm');
        const ids = Array.from(checked).map(cb => cb.value);

        // Create hidden inputs for each id
        form.querySelectorAll('input[name="enrollment_ids[]"]').forEach(el => el.remove());
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'enrollment_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        form.action = "{{ route('finance.student-fees.send-reminders') }}";
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        updateSelectedCount();
    }

    function sendSingleReminder(id) {
        if (confirm('Send fee reminder to this student?')) {
            showLoading('Sending reminder...');
            $.ajax({
                url: `/finance/student-fees/send-reminder/${id}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    toastr.success('Reminder sent successfully');
                },
                error: function(xhr) {
                    hideLoading();
                    toastr.error('Failed to send reminder');
                }
            });
        }
    }

    function sendBulkReminders() {
        sendReminders();
    }

    function exportSelected() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            toastr.warning('Please select at least one student');
            return;
        }
        const ids = Array.from(checked).map(cb => cb.value);
        const url = "{{ route('finance.student-fees.export') }}?ids=" + ids.join(',');
        window.location.href = url;
    }

    // Handle template selection
    document.querySelector('select[name="template"]').addEventListener('change', function() {
        const container = document.getElementById('customMessageContainer');
        if (this.value === 'custom') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    });

    // Handle form submission for reminders
    document.getElementById('reminderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        showLoading('Sending reminders...');

        $.ajax({
