@extends('ktvtc.mschool.layout.mschoollayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Certificate Management</h1>
                <p class="text-gray-600 mt-2">Assign and manage student certificates</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button id="assignTab" onclick="switchTab('assign')"
                        class="border-b-2 border-primary text-primary px-3 py-2 text-sm font-medium">
                    Assign Certificates
                </button>
                <button id="issuedTab" onclick="switchTab('issued')"
                        class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-3 py-2 text-sm font-medium">
                    Issued Certificates
                </button>
            </nav>
        </div>
    </div>

    <!-- Assign Certificates Tab Content -->
    <div id="assignContent" class="tab-content">
        <div class="card mb-6">
            <div class="card-header bg-gray-50 border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Students Ready for Certificates</h3>
                        <p class="text-sm text-gray-600 mt-1">Only students with completed courses are shown</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span id="selectedCount" class="text-sm text-gray-600">0 selected</span>
                        <button id="assignBtn" onclick="assignCertificates()" disabled
                                class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            Assign Selected
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-6">
                <!-- Loading State -->
                <div id="loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    <p class="mt-2 text-gray-600">Loading students...</p>
                </div>

                <!-- Student List Table -->
                <div id="studentList" class="hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate Types</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody" class="divide-y divide-gray-200">
                                <!-- Dynamic content -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="hidden text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-4 text-sm font-medium text-gray-900">No students found</h3>
                        <p class="mt-1 text-sm text-gray-500">All students with completed courses have been issued certificates.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Issued Certificates Tab Content -->
    <div id="issuedContent" class="tab-content hidden">
        <div class="card">
            <div class="card-header bg-gray-50 border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Issued Certificates</h3>
                    <div class="flex space-x-2">
                        <input type="text" id="searchIssued" placeholder="Search..."
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>

            <div class="card-body p-6">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="issuedTableBody" class="divide-y divide-gray-200">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="issuedEmptyState" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-900">No certificates issued yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Start by assigning certificates to students.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Certificate Preview Modal -->
<div id="previewModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closePreviewModal()"></div>

        <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-primary px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-white" id="previewTitle">Certificate Preview</h3>
                    </div>
                    <button onclick="closePreviewModal()" class="text-white hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div class="mb-6 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Student</p>
                        <p class="font-medium" id="previewStudent"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Course</p>
                        <p class="font-medium" id="previewCourse"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Certificate Type</p>
                        <p class="font-medium" id="previewType"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Certificate Number</p>
                        <p class="font-medium" id="previewNumber"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Issue Date</p>
                        <p class="font-medium" id="previewDate"></p>
                    </div>
                </div>

                <!-- PDF Preview -->
                <div class="border border-gray-300 rounded-lg overflow-hidden">
                    <div id="pdfPreview" class="min-h-[500px] flex items-center justify-center bg-gray-50">
                        <p class="text-gray-500">Loading certificate...</p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button onclick="closePreviewModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                        Close
                    </button>
                    <a id="downloadBtn" href="#" target="_blank"
                       class="px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-red-700">
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let selectedAssignments = {};
let currentTab = 'assign';

// Tab Switching
function switchTab(tab) {
    currentTab = tab;

    // Update tab buttons
    document.getElementById('assignTab').classList.remove('border-primary', 'text-primary');
    document.getElementById('issuedTab').classList.remove('border-primary', 'text-primary');
    document.getElementById('assignTab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('issuedTab').classList.add('border-transparent', 'text-gray-500');

    if (tab === 'assign') {
        document.getElementById('assignTab').classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('assignTab').classList.add('border-primary', 'text-primary');
        document.getElementById('assignContent').classList.remove('hidden');
        document.getElementById('issuedContent').classList.add('hidden');
        loadAssignableStudents();
    } else {
        document.getElementById('issuedTab').classList.remove('border-transparent', 'text-gray-500');
        document.getElementById('issuedTab').classList.add('border-primary', 'text-primary');
        document.getElementById('assignContent').classList.add('hidden');
        document.getElementById('issuedContent').classList.remove('hidden');
        loadIssuedCertificates();
    }
}

// Load assignable students
function loadAssignableStudents() {
    fetch('{{ route("certificates.assignable") }}')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('studentsTableBody');
            const loading = document.getElementById('loading');
            const studentList = document.getElementById('studentList');
            const emptyState = document.getElementById('emptyState');

            loading.classList.add('hidden');

            if (data.length === 0) {
                studentList.classList.add('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            studentList.classList.remove('hidden');
            emptyState.classList.add('hidden');
            tbody.innerHTML = '';

            data.forEach(student => {
                student.enrollments.forEach(enrollment => {
                    const rowId = `row_${student.student_id}_${enrollment.enrollment_id}`;
                    const row = document.createElement('tr');
                    row.id = rowId;
                    row.innerHTML = `
                        <td class="px-4 py-3">
                            <input type="checkbox" class="student-checkbox rounded border-gray-300 text-primary"
                                   data-student-id="${student.student_id}"
                                   data-enrollment-id="${enrollment.enrollment_id}"
                                   data-course-id="${enrollment.course_id}"
                                   onchange="toggleStudentSelection(this)">
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <p class="font-medium">${student.full_name}</p>
                                <p class="text-sm text-gray-500">${student.student_code || 'No code'}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium">${enrollment.course_name}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p>${enrollment.completion_date ? new Date(enrollment.completion_date).toLocaleDateString() : 'N/A'}</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="cert-type rounded border-gray-300 text-primary"
                                           value="completion"
                                           onchange="updateCertificateType(this, ${student.student_id}, ${enrollment.enrollment_id})">
                                    <span class="ml-2 text-sm">Completion</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="cert-type rounded border-gray-300 text-primary"
                                           value="participation"
                                           onchange="updateCertificateType(this, ${student.student_id}, ${enrollment.enrollment_id})">
                                    <span class="ml-2 text-sm">Participation</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="cert-type rounded border-gray-300 text-primary"
                                           value="achievement"
                                           onchange="updateCertificateType(this, ${student.student_id}, ${enrollment.enrollment_id})">
                                    <span class="ml-2 text-sm">Achievement</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="cert-type rounded border-gray-300 text-primary"
                                           value="recognition"
                                           onchange="updateCertificateType(this, ${student.student_id}, ${enrollment.enrollment_id})">
                                    <span class="ml-2 text-sm">Recognition</span>
                                </label>
                            </div>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            });
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('loading').innerHTML = '<p class="text-red-500">Error loading students</p>';
        });
}

// Load issued certificates
function loadIssuedCertificates() {
    fetch('{{ route("certificates.issued.list") }}')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('issuedTableBody');
            const emptyState = document.getElementById('issuedEmptyState');

            if (data.data.length === 0) {
                tbody.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            tbody.innerHTML = '';

            data.data.forEach(cert => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-4 py-3">
                        <p class="font-medium">${cert.certificate_number}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p>${cert.student_name}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p>${cert.course_name}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                            ${cert.certificate_type}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <p>${cert.issue_date}</p>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex space-x-2">
                            <button onclick="viewCertificate(${cert.certificate_id})"
                                    class="text-primary hover:text-red-700">
                                View
                            </button>
                            <a href="{{ route('certificates.download', '') }}/${cert.certificate_id}"
                               class="text-green-600 hover:text-green-800">
                                Download
                            </a>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        });
}

// Student selection
function toggleStudentSelection(checkbox) {
    const studentId = checkbox.dataset.studentId;
    const enrollmentId = checkbox.dataset.enrollmentId;
    const courseId = checkbox.dataset.courseId;

    const key = `${studentId}_${enrollmentId}`;

    if (checkbox.checked) {
        selectedAssignments[key] = {
            student_id: studentId,
            enrollment_id: enrollmentId,
            course_id: courseId,
            template_types: []
        };
    } else {
        delete selectedAssignments[key];
        const row = checkbox.closest('tr');
        row.querySelectorAll('.cert-type').forEach(cb => cb.checked = false);
    }

    updateAssignButton();
}

// Certificate type selection
function updateCertificateType(checkbox, studentId, enrollmentId) {
    const key = `${studentId}_${enrollmentId}`;

    if (selectedAssignments[key]) {
        if (checkbox.checked) {
            if (!selectedAssignments[key].template_types.includes(checkbox.value)) {
                selectedAssignments[key].template_types.push(checkbox.value);
            }
        } else {
            selectedAssignments[key].template_types =
                selectedAssignments[key].template_types.filter(t => t !== checkbox.value);
        }

        if (selectedAssignments[key].template_types.length === 0) {
            delete selectedAssignments[key];
            const row = document.getElementById(`row_${key}`);
            if (row) {
                row.querySelector('.student-checkbox').checked = false;
            }
        }
    }

    updateAssignButton();
}

// Update assign button state
function updateAssignButton() {
    const assignments = Object.values(selectedAssignments);
    const hasSelections = assignments.some(a => a.template_types.length > 0);

    const btn = document.getElementById('assignBtn');
    const countSpan = document.getElementById('selectedCount');

    const totalSelections = assignments.reduce((sum, a) => sum + a.template_types.length, 0);

    btn.disabled = !hasSelections;
    countSpan.textContent = `${totalSelections} certificate(s) selected`;
}

// Assign certificates
function assignCertificates() {
    const assignments = Object.values(selectedAssignments).filter(a => a.template_types.length > 0);

    if (assignments.length === 0) {
        alert('Please select at least one certificate type for a student');
        return;
    }

    const totalCertificates = assignments.reduce((sum, a) => sum + a.template_types.length, 0);

    if (!confirm(`Issue ${totalCertificates} certificate(s) to ${assignments.length} student(s)?`)) {
        return;
    }

    const assignBtn = document.getElementById('assignBtn');
    const originalText = assignBtn.innerHTML;
    assignBtn.innerHTML = 'Processing...';
    assignBtn.disabled = true;

    fetch('{{ route("certificates.assign") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ assignments: assignments })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success alert
            showAlert('success', data.message);

            // Refresh lists
            loadAssignableStudents();
            loadIssuedCertificates();

            // Clear selections
            selectedAssignments = {};
            document.getElementById('selectAll').checked = false;
            document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
            updateAssignButton();

        } else {
            showAlert('error', data.message || 'Failed to issue certificates');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error issuing certificates. Please try again.');
    })
    .finally(() => {
        assignBtn.innerHTML = originalText;
        assignBtn.disabled = false;
    });
}

// View certificate in modal
function viewCertificate(certificateId) {
    fetch(`/certificates/${certificateId}/preview`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('previewTitle').textContent = `Certificate: ${data.student_name}`;
            document.getElementById('previewStudent').textContent = data.student_name;
            document.getElementById('previewCourse').textContent = data.course_name;
            document.getElementById('previewType').textContent = data.certificate_type;
            document.getElementById('previewNumber').textContent = data.certificate_number;
            document.getElementById('previewDate').textContent = data.issue_date;

            // Set PDF preview
            document.getElementById('pdfPreview').innerHTML = `
                <iframe src="${data.pdf_url}" width="100%" height="500px" style="border: none;"></iframe>
            `;

            // Set download link
            document.getElementById('downloadBtn').href = data.download_url;

            // Show modal
            document.getElementById('previewModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        })
        .catch(error => {
            console.error('Error loading certificate:', error);
            showAlert('error', 'Error loading certificate preview.');
        });
}

function closePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Select all students
document.getElementById('selectAll').addEventListener('change', function(e) {
    const isChecked = e.target.checked;
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        cb.checked = isChecked;
        cb.dispatchEvent(new Event('change'));
    });
});

// Search issued certificates
document.getElementById('searchIssued').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#issuedTableBody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Helper function to show alerts
function showAlert(type, message) {
    const container = document.querySelector('.container');
    const alertHtml = `
        <div class="mb-6 p-4 rounded-lg ${type === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}">
            <div class="flex items-center">
                ${type === 'success' ?
                    '<svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' :
                    '<svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                }
                <span class="${type === 'success' ? 'text-green-800' : 'text-red-800'} font-medium">${message}</span>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('afterbegin', alertHtml);

    // Remove alert after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.bg-green-50, .bg-red-50');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAssignableStudents();

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePreviewModal();
        }
    });
});
</script>
@endsection
