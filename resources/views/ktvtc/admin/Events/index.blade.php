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
            <table id="eventApplicationsTable" class="min-w-full divide-y divide-gray-200">
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

@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.tailwindcss.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>

<script>
// Make functions globally available
window.exportToExcel = function() {
    console.log('Export function called');

    // Check if we have data to export
    const hasData = $('#eventApplicationsTable tbody tr').length > 0 &&
                    !$('#eventApplicationsTable tbody tr td[colspan="9"]').length;

    if (!hasData) {
        if (typeof showToast === 'function') {
            showToast('No data to export', 'warning', 3000);
        } else {
            alert('No data to export');
        }
        return;
    }

    // Show loading indicator
    if (typeof showGlobalLoading === 'function') {
        showGlobalLoading('Preparing Excel export...');
    }

    setTimeout(() => {
        try {
            // Get the table element
            const table = document.getElementById('eventApplicationsTable');
            const rows = [];

            // Get headers (excluding Actions column)
            const headers = [];
            const headerCells = table.querySelectorAll('thead tr th');
            for (let i = 0; i < headerCells.length - 1; i++) {
                headers.push(headerCells[i].innerText.trim());
            }
            rows.push(headers);

            // Get data rows
            const dataRows = table.querySelectorAll('tbody tr');
            for (const row of dataRows) {
                // Skip "no data" row
                if (row.querySelector('td[colspan]')) continue;

                const rowData = [];
                const cells = row.querySelectorAll('td');
                for (let i = 0; i < cells.length - 1; i++) {
                    let cellText = cells[i].innerText || cells[i].textContent;

                    // Clean up specific columns
                    if (i === 3) { // Attendees column
                        const match = cellText.match(/\d+/);
                        cellText = match ? match[0] : '0';
                    } else if (i === 4) { // Amount column
                        const match = cellText.match(/[\d,]+/);
                        cellText = match ? match[0].replace(/,/g, '') : '0';
                    } else if (i === 6) { // Status column
                        cellText = cellText.trim().toUpperCase();
                    } else if (i === 5) { // MPESA Reference
                        cellText = cellText.trim();
                    } else if (i === 7) { // Date column
                        cellText = cellText.trim();
                    }

                    rowData.push(cellText.trim());
                }
                if (rowData.length > 0) {
                    rows.push(rowData);
                }
            }

            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(rows);

            // Auto-size columns
            const colWidths = [];
            rows[0].forEach((header, idx) => {
                let maxLength = header.length;
                for (let i = 1; i < rows.length; i++) {
                    if (rows[i][idx] && rows[i][idx].length > maxLength) {
                        maxLength = rows[i][idx].length;
                    }
                }
                colWidths.push({ wch: Math.min(maxLength + 2, 50) });
            });
            ws['!cols'] = colWidths;

            // Create workbook
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Event Applications');

            // Generate filename with current date and time
            const now = new Date();
            const fileName = `event_applications_${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2,'0')}-${now.getDate().toString().padStart(2,'0')}_${now.getHours().toString().padStart(2,'0')}-${now.getMinutes().toString().padStart(2,'0')}-${now.getSeconds().toString().padStart(2,'0')}.xlsx`;

            // Trigger download
            XLSX.writeFile(wb, fileName);

            // Hide loading and show success message
            if (typeof hideGlobalLoading === 'function') {
                hideGlobalLoading();
            }

            if (typeof showToast === 'function') {
                showToast('Export completed successfully!', 'success', 3000);
            } else {
                alert('Export completed successfully!');
            }

        } catch (error) {
            console.error('Export error:', error);
            if (typeof hideGlobalLoading === 'function') {
                hideGlobalLoading();
            }
            if (typeof showToast === 'function') {
                showToast('Error exporting data. Please try again.', 'error', 5000);
            } else {
                alert('Error exporting data. Please try again.');
            }
        }
    }, 100);
};

window.updateStatus = function(applicationId, status) {
    if (confirm('Are you sure you want to update this application status to ' + status.toUpperCase() + '?')) {
        const form = document.getElementById('statusForm');
        form.action = `/admin/event-applications/${applicationId}/status`;
        document.getElementById('statusInput').value = status;
        form.submit();
    }
};

window.deleteApplication = function(applicationId) {
    if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/event-applications/${applicationId}`;
        form.submit();
    }
};

window.showAttendees = function(applicationId) {
    // Find the button that was clicked
    const button = event.target.closest('button');
    if (button && button.getAttribute('title')) {
        alert('Attendees: ' + button.getAttribute('title'));
    } else {
        alert('No attendee details available');
    }
};

window.toggleDropdown = function(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    if (dropdown) {
        dropdown.classList.toggle('hidden');

        // Close other dropdowns
        document.querySelectorAll('[id^="dropdown-"]').forEach(otherDropdown => {
            if (otherDropdown.id !== dropdownId) {
                otherDropdown.classList.add('hidden');
            }
        });
    }
};

// Initialize on page load
$(document).ready(function() {
    console.log('Document ready, initializing...');

    // Add export button loading effect
    const exportBtn = document.querySelector('[onclick="exportToExcel()"]');
    if (exportBtn) {
        exportBtn.addEventListener('click', function(e) {
            const btn = this;
            if (btn.disabled) return;

            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';

            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }, 2000);
        });
    }
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.matches('[onclick*="toggleDropdown"]') && !event.target.closest('[id^="dropdown-"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Simple toast function if not available in layout
if (typeof window.showToast !== 'function') {
    window.showToast = function(message, type = 'success', duration = 3000) {
        // Create toast container if it doesn't exist
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'fixed bottom-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }

        const toastId = 'toast-' + Date.now();
        const colors = {
            success: { bg: 'bg-green-50', border: 'border-green-500', icon: 'fa-check-circle', text: 'text-green-600' },
            error: { bg: 'bg-red-50', border: 'border-red-500', icon: 'fa-exclamation-circle', text: 'text-red-600' },
            warning: { bg: 'bg-yellow-50', border: 'border-yellow-500', icon: 'fa-exclamation-triangle', text: 'text-yellow-600' },
            info: { bg: 'bg-blue-50', border: 'border-blue-500', icon: 'fa-info-circle', text: 'text-blue-600' }
        };

        const color = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `p-4 rounded-xl shadow-lg border-l-4 ${color.border} ${color.bg} transition-all duration-300`;
        toast.style.marginBottom = '0.5rem';
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${color.icon} ${color.text} mr-3 text-lg"></i>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">${message}</p>
                </div>
                <button onclick="this.closest('[id^=toast-]').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            const toastEl = document.getElementById(toastId);
            if (toastEl) {
                toastEl.style.opacity = '0';
                toastEl.style.transform = 'translateX(100%)';
                setTimeout(() => toastEl.remove(), 300);
            }
        }, duration);
    };
}

// Simple loading functions if not available in layout
if (typeof window.showGlobalLoading !== 'function') {
    window.showGlobalLoading = function(message = 'Processing...') {
        let overlay = document.getElementById('globalLoadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'globalLoadingOverlay';
            overlay.className = 'fixed inset-0 bg-black/70 z-[100] hidden flex items-center justify-center';
            overlay.innerHTML = `
                <div class="bg-white rounded-2xl p-8 shadow-2xl flex flex-col items-center min-w-[280px]">
                    <div class="loading-spinner mb-4"></div>
                    <p id="loadingMessage" class="text-gray-800 font-semibold text-lg">Processing...</p>
                    <p class="text-gray-500 text-sm mt-2">Please wait</p>
                </div>
            `;
            document.body.appendChild(overlay);

            // Add spinner CSS if not present
            if (!document.querySelector('#loading-spinner-style')) {
                const style = document.createElement('style');
                style.id = 'loading-spinner-style';
                style.textContent = `
                    .loading-spinner {
                        border: 3px solid #F3F4F6;
                        border-top: 3px solid #B91C1C;
                        border-radius: 50%;
                        width: 40px;
                        height: 40px;
                        animation: spin 1s linear infinite;
                    }
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        const messageEl = document.getElementById('loadingMessage');
        if (messageEl) messageEl.textContent = message;
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };
}

if (typeof window.hideGlobalLoading !== 'function') {
    window.hideGlobalLoading = function() {
        const overlay = document.getElementById('globalLoadingOverlay');
        if (overlay) {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };
}
</script>

<style>
/* DataTables styling overrides - only apply if DataTables is active */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    padding: 0.75rem 1rem;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.5rem;
    margin: 0 0.5rem;
}

/* Ensure proper button styling */
button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.fa-spin {
    animation: fa-spin 2s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection
