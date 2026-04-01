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
                <h1 class="text-3xl font-bold text-gray-900">Certificate Templates</h1>
                <p class="text-gray-600 mt-2">Manage certificate templates</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Template
            </button>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coordinates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($templates as $template)
                        <tr>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium">{{ $template->template_name }}</p>
                                    <p class="text-sm text-gray-500">{{ basename($template->template_file) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($template->template_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <p>Name: {{ $template->name_x }}, {{ $template->name_y }}</p>
                                    <p>Course: {{ $template->course_x }}, {{ $template->course_y }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditModal({{ $template->template_id }})"
                                            class="text-primary hover:text-red-700">
                                        Edit
                                    </button>
                                    <button onclick="testTemplate({{ $template->template_id }})"
                                            class="text-green-600 hover:text-green-800">
                                        Test
                                    </button>
                                    <button onclick="confirmDelete({{ $template->template_id }})"
                                            class="text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No templates found. Create your first template.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Template Modal -->
<div id="templateModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeTemplateModal()"></div>

        <div class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-primary px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-xl font-bold text-white" id="modalTitle">Create Template</h3>
                    </div>
                    <button onclick="closeTemplateModal()" class="text-white hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="templateForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">
                    <input type="hidden" id="templateId" name="template_id">

                    <div class="space-y-6">
                        <!-- Template Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                            <input type="text" id="templateName" name="template_name" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Template Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Template Type *</label>
                            <select id="templateType" name="template_type" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Type</option>
                                <option value="completion">Completion</option>
                                <option value="participation">Participation</option>
                                <option value="achievement">Achievement</option>
                                <option value="recognition">Recognition</option>
                            </select>
                        </div>

                        <!-- PDF File -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PDF Template <span id="fileRequired">*</span></label>
                            <input type="file" id="templateFile" name="template_file" accept=".pdf"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <p class="text-sm text-gray-500 mt-1" id="currentFile"></p>
                        </div>

                        <!-- Coordinates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student Name X (points)</label>
                                <input type="number" id="nameX" name="name_x" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="420">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student Name Y (points)</label>
                                <input type="number" id="nameY" name="name_y" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="250">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Name X (points)</label>
                                <input type="number" id="courseX" name="course_x" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="425">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Name Y (points)</label>
                                <input type="number" id="courseY" name="course_y" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="300">
                            </div>
                        </div>

                        <!-- Coordinate Help Text -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-800">
                                <strong>Tip:</strong> Based on your certificate design, these coordinates should place text correctly:
                                <br>• Student Name: X=420, Y=250 (≈ 148mm, 88mm)
                                <br>• Course Name: X=425, Y=300 (≈ 150mm, 106mm)
                                <br>Use the <strong>Test</strong> button to preview and adjust if needed.
                            </p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="mt-8 flex justify-end space-x-3">
                        <button type="button" onclick="closeTemplateModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-red-700">
                            Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Test Template Modal -->
<div id="testModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeTestModal()"></div>

        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Template</h3>

                <form id="testForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Student Name</label>
                            <input type="text" name="test_name" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3"
                                   value="John Doe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Course Name</label>
                            <input type="text" name="test_course" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3"
                                   value="Web Development">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeTestModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-red-700">
                            Test PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeDeleteModal()"></div>

        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Template</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this template? This action cannot be undone.</p>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                        Cancel
                    </button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Store base URLs for routes
const routes = {
    store: '{{ route("mschool.certificate-templates.store") }}',
    edit: '{{ route("mschool.certificate-templates.edit", ["certificateTemplate" => ":id"]) }}',
    update: '{{ route("mschool.certificate-templates.update", ["certificateTemplate" => ":id"]) }}',
    test: '{{ route("mschool.certificate-templates.test", ["certificateTemplate" => ":id"]) }}',
    destroy: '{{ route("mschool.certificate-templates.destroy", ["certificateTemplate" => ":id"]) }}'
};

// Helper function to replace route parameter
function replaceRouteParam(route, id) {
    return route.replace(':id', id);
}

// Open create modal
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Template';
    document.getElementById('methodField').value = 'POST';
    document.getElementById('templateForm').action = routes.store;

    // Reset form with default coordinates
    document.getElementById('templateId').value = '';
    document.getElementById('templateName').value = '';
    document.getElementById('templateType').value = '';
    document.getElementById('templateFile').required = true;
    document.getElementById('fileRequired').textContent = '*';
    document.getElementById('currentFile').textContent = '';
    document.getElementById('nameX').value = '420';
    document.getElementById('nameY').value = '250';
    document.getElementById('courseX').value = '425';
    document.getElementById('courseY').value = '300';

    document.getElementById('templateModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Open edit modal
function openEditModal(templateId) {
    const editUrl = replaceRouteParam(routes.edit, templateId);

    fetch(editUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load template');
            }

            document.getElementById('modalTitle').textContent = 'Edit Template';
            document.getElementById('methodField').value = 'PUT';
            const updateUrl = replaceRouteParam(routes.update, templateId);
            document.getElementById('templateForm').action = updateUrl;

            document.getElementById('templateId').value = data.template_id;
            document.getElementById('templateName').value = data.template_name;
            document.getElementById('templateType').value = data.template_type;
            document.getElementById('templateFile').required = false;
            document.getElementById('fileRequired').textContent = ' (optional)';
            document.getElementById('currentFile').textContent = `Current file: ${data.template_file.split('/').pop()}`;
            document.getElementById('nameX').value = data.name_x;
            document.getElementById('nameY').value = data.name_y;
            document.getElementById('courseX').value = data.course_x;
            document.getElementById('courseY').value = data.course_y;

            document.getElementById('templateModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        })
        .catch(error => {
            console.error('Error loading template:', error);
            alert('Error loading template data: ' + error.message);
        });
}

// Test template
function testTemplate(templateId) {
    const testUrl = replaceRouteParam(routes.test, templateId);
    document.getElementById('testForm').action = testUrl;
    document.getElementById('testModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Delete confirmation
function confirmDelete(templateId) {
    const deleteUrl = replaceRouteParam(routes.destroy, templateId);
    document.getElementById('deleteForm').action = deleteUrl;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Close modals
function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeTemplateModal();
        closeTestModal();
        closeDeleteModal();
    }
});

// Handle create/edit form submission
document.getElementById('templateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const url = this.action;
    const method = document.getElementById('methodField').value;

    // For PUT requests, add the _method field
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // Check if response is a redirect
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }

        // Check content type
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        }

        // If not JSON, assume it's a redirect
        return response.text().then(text => {
            if (text.includes('redirect')) {
                window.location.reload();
            }
            throw new Error('Unexpected response format');
        });
    })
    .then(data => {
        if (data && !data.success) {
            throw new Error(data.message || 'An error occurred');
        }
        // Close modal and reload on success
        closeTemplateModal();
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Handle test form submission
document.getElementById('testForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const url = this.action;
    const formData = new FormData(this);

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Generating...';
    submitBtn.disabled = true;

    // Create a new window to show the PDF
    const pdfWindow = window.open('', '_blank');
    if (pdfWindow) {
        pdfWindow.document.write('<div style="text-align:center; padding:50px; font-family:sans-serif;">');
        pdfWindow.document.write('<h2>Generating Certificate...</h2>');
        pdfWindow.document.write('<p>Please wait while we create your test certificate.</p>');
        pdfWindow.document.write('<div style="margin-top:20px;"><div class="spinner"></div></div>');
        pdfWindow.document.write('</div>');
        pdfWindow.document.write('<style>.spinner { border: 4px solid #f3f3f3; border-top: 4px solid #B91C1C; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>');
    }

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }
    })
    .then(response => {
        if (response.headers.get('content-type') === 'application/pdf') {
            return response.blob();
        }
        return response.json().then(data => {
            throw new Error(data.message || 'Failed to generate certificate');
        });
    })
    .then(blob => {
        const pdfUrl = URL.createObjectURL(blob);
        if (pdfWindow) {
            pdfWindow.location.href = pdfUrl;
        } else {
            window.open(pdfUrl, '_blank');
        }
        closeTestModal();
        URL.revokeObjectURL(pdfUrl);
    })
    .catch(error => {
        if (pdfWindow) {
            pdfWindow.close();
        }
        alert('Error: ' + error.message);
        console.error('Test error:', error);
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Handle delete form submission
document.getElementById('deleteForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const url = this.action;
    const formData = new FormData(this);

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Deleting...';
    submitBtn.disabled = true;

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && !data.success) {
            throw new Error(data.message || 'An error occurred');
        }
        closeDeleteModal();
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message);
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Add modal close on outside click
document.addEventListener('click', function(event) {
    const modal = document.getElementById('templateModal');
    const testModal = document.getElementById('testModal');
    const deleteModal = document.getElementById('deleteModal');

    if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
        closeTemplateModal();
        closeTestModal();
        closeDeleteModal();
    }
});

// Log to verify routes are loaded correctly
console.log('Routes loaded:', routes);
</script>
@endsection
