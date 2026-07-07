@extends('ktvtc.finance.layouts.app')

@section('title', 'Outstanding Balance Report')
@section('subtitle', 'View all students with outstanding fee balances')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Student Fees</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Outstanding Balance</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
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
<div class="w-full max-w-full space-y-6 overflow-hidden">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Total Outstanding</p>
                    <h3 class="text-2xl font-bold text-red-600 mt-1 break-words">
                        KES {{ number_format($totalOutstanding ?? 0, 2) }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">All outstanding fees</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-yellow-100 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Students with Balance</p>
                    <h3 class="text-2xl font-bold text-yellow-600 mt-1">
                        {{ number_format($totalStudents ?? 0) }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Active students with balance</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center text-yellow-600 flex-shrink-0">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500">Average Balance</p>
                    <h3 class="text-2xl font-bold text-blue-600 mt-1 break-words">
                        KES {{ number_format(($totalStudents ?? 0) > 0 ? ($totalOutstanding ?? 0) / max($totalStudents, 1) : 0, 2) }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Per student average</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                    <i class="fas fa-calculator text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <form method="GET" action="{{ route('finance.student-fees.reports.outstanding') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-5">
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input type="text" name="search" placeholder="Student name or number..." value="{{ request('search') }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            @if(isset($campuses) && count($campuses) > 0)
            <div class="md:col-span-4">
                <label class="text-xs font-semibold text-gray-600">Campus</label>
                <select name="campus_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">All Campuses</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                            {{ $campus->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="md:col-span-3 flex flex-wrap gap-2">
                <button type="submit" class="px-5 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition font-semibold">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>

                <a href="{{ route('finance.student-fees.reports.outstanding') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="w-full overflow-x-auto">
            <table class="min-w-[950px] w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4">
                            <input type="checkbox" id="selectAll" onchange="toggleAll(this)" class="rounded border-gray-300 text-primary focus:ring-primary">
                        </th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fees</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                        <th class="text-right py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="text-center py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($enrollments ?? [] as $enrollment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox"
                                       class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                       value="{{ $enrollment->id }}"
                                       data-student="{{ $enrollment->student_id }}"
                                       data-phone="{{ $enrollment->student->phone ?? '' }}">
                            </td>

                            <td class="py-3 px-4">
                                <div>
                                    <span class="font-medium text-gray-900">
                                        {{ $enrollment->student->full_name ?? $enrollment->student_name }}
                                    </span>
                                    <span class="text-xs text-gray-500 block">
                                        {{ $enrollment->student->student_number ?? $enrollment->student_number }}
                                    </span>
                                </div>
                            </td>

                            <td class="py-3 px-4 text-sm text-gray-600">
                                {{ $enrollment->course->name ?? $enrollment->course_name }}
                            </td>

                            <td class="py-3 px-4 text-right font-medium text-gray-800">
                                KES {{ number_format($enrollment->total_fees ?? 0, 2) }}
                            </td>

                            <td class="py-3 px-4 text-right text-green-600 font-medium">
                                KES {{ number_format($enrollment->amount_paid ?? 0, 2) }}
                            </td>

                            <td class="py-3 px-4 text-right font-bold
                                @if(($enrollment->balance ?? 0) > 50000) text-red-600
                                @elseif(($enrollment->balance ?? 0) > 20000) text-orange-600
                                @else text-yellow-600 @endif">
                                KES {{ number_format($enrollment->balance ?? 0, 2) }}
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <a href="{{ route('finance.students.financial', $enrollment->student) }}"
                                       class="text-primary hover:text-primary-dark transition"
                                       title="View Financial Details">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <button type="button"
                                            onclick="sendSingleReminder({{ $enrollment->id }})"
                                            class="text-yellow-600 hover:text-yellow-800 transition"
                                            title="Send Reminder">
                                        <i class="fas fa-sms"></i>
                                    </button>

                                    <a href="{{ route('finance.student-fees.create', ['enrollment_id' => $enrollment->id]) }}"
                                       class="text-green-600 hover:text-green-800 transition"
                                       title="Record Payment">
                                        <i class="fas fa-credit-card"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl text-green-300 mb-3 block"></i>
                                <p class="text-lg font-medium">No outstanding balances found</p>
                                <p class="text-sm text-gray-400 mt-1">All students are fully paid!</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if(isset($enrollments) && $enrollments && $enrollments->count() > 0)
                <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                    <tr>
                        <td colspan="3" class="py-2 px-4 font-bold text-gray-800">Total</td>
                        <td class="py-2 px-4 text-right font-bold text-gray-800">
                            KES {{ number_format($enrollments->sum('total_fees'), 2) }}
                        </td>
                        <td class="py-2 px-4 text-right font-bold text-green-600">
                            KES {{ number_format($enrollments->sum('amount_paid'), 2) }}
                        </td>
                        <td class="py-2 px-4 text-right font-bold text-red-600">
                            KES {{ number_format($enrollments->sum('balance'), 2) }}
                        </td>
                        <td class="py-2 px-4"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if(isset($enrollments))
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50 overflow-x-auto">
            {{ $enrollments->links() }}
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>

            <button type="button" onclick="sendBulkReminders()" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-sms mr-2"></i> Send Reminders
            </button>

            <button type="button" onclick="exportSelected()" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-semibold text-sm">
                <i class="fas fa-file-export mr-2"></i> Export Selected
            </button>

            <span class="text-xs text-gray-500 sm:ml-auto" id="selectedCountInfo">0 students selected</span>
        </div>
    </div>
</div>

<div id="reminderModal" class="hidden fixed inset-0 z-[1200] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal('reminderModal')"></div>

        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Send Fee Reminder</h3>
                <button type="button" onclick="closeModal('reminderModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="reminderForm" method="POST" action="{{ route('finance.student-fees.send-reminders') }}">
                @csrf

                <div id="reminderIdsContainer"></div>

                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700">Template</label>
                    <select name="template" id="templateSelect" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="standard">Standard</option>
                        <option value="urgent">Urgent</option>
                        <option value="friendly">Friendly</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                <div class="mb-4 hidden" id="customMessageContainer">
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
                    <button type="button" onclick="closeModal('reminderModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>

                    <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold transition">
                        <i class="fas fa-sms mr-2"></i> Send Reminders
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

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
        const checked = document.querySelectorAll('.row-checkbox:checked');
        selectedStudents = Array.from(checked).map(cb => cb.value);

        const selectedCount = document.getElementById('selectedCount');
        const selectedCountInfo = document.getElementById('selectedCountInfo');

        if (selectedCount) {
            selectedCount.textContent = selectedStudents.length;
        }

        if (selectedCountInfo) {
            selectedCountInfo.textContent = selectedStudents.length + ' students selected';
        }

        const selectAll = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('.row-checkbox');

        if (selectAll && allCheckboxes.length > 0) {
            selectAll.checked = selectedStudents.length === allCheckboxes.length;
        }
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateSelectedCount();
        }
    });

    function sendReminders() {
        const checked = document.querySelectorAll('.row-checkbox:checked');

        if (checked.length === 0) {
            toastr.warning('Please select at least one student');
            return;
        }

        const modal = document.getElementById('reminderModal');
        const container = document.getElementById('reminderIdsContainer');

        container.innerHTML = '';

        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'enrollment_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('selectedCount').textContent = checked.length;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function sendSingleReminder(id) {
        if (!confirm('Send fee reminder to this student?')) {
            return;
        }

        showLoading('Sending reminder...');

        $.ajax({
            url: '/finance/student-fees/send-reminder/' + id,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                hideLoading();
                toastr.success('Reminder sent successfully');
            },
            error: function (xhr) {
                hideLoading();
                toastr.error(xhr.responseJSON?.message || 'Failed to send reminder');
            }
        });
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
        window.location.href = "{{ route('finance.student-fees.export') }}?ids=" + ids.join(',');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const templateSelect = document.getElementById('templateSelect');
        const customMessageContainer = document.getElementById('customMessageContainer');

        if (templateSelect && customMessageContainer) {
            templateSelect.addEventListener('change', function () {
                if (this.value === 'custom') {
                    customMessageContainer.classList.remove('hidden');
                } else {
                    customMessageContainer.classList.add('hidden');
                }
            });
        }

        const reminderForm = document.getElementById('reminderForm');

        if (reminderForm) {
            reminderForm.addEventListener('submit', function (e) {
                e.preventDefault();

                showLoading('Sending reminders...');

                $.ajax({
                    url: this.action,
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        hideLoading();
                        closeModal('reminderModal');
                        toastr.success(response.message || 'Reminders sent successfully');
                    },
                    error: function (xhr) {
                        hideLoading();
                        toastr.error(xhr.responseJSON?.message || 'Failed to send reminders');
                    }
                });
            });
        }

        updateSelectedCount();
    });

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);

        if (modal) {
            modal.classList.add('hidden');
        }

        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal('reminderModal');
        }
    });
</script>
@endpush
