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
                                    {{ $template->template_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <p>Name: {{ $template->name_x }},{{ $template->name_y }}</p>
                                    <p>Course: {{ $template->course_x }},{{ $template->course_y }}</p>
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
                                    <button onclick="confirmDelete('{{ route('certificate-templates.destroy', $template->template_id) }}')"
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">PDF Template *</label>
                            <input type="file" id="templateFile" name="template_file" accept=".pdf"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <p class="text-sm text-gray-500 mt-1" id="currentFile"></p>
                        </div>

                        <!-- Coordinates -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student Name X</label>
                                <input type="number" id="nameX" name="name_x" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student Name Y</label>
                                <input type="number" id="nameY" name="name_y" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="120">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Name X</label>
                                <input type="number" id="courseX" name="course_x" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Name Y</label>
                                <input type="number" id="courseY" name="course_y" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="150">
                            </div>
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
// Open create modal
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Template';
    document.getElementById('templateForm').action = '{{ route("certificate-templates.store") }}';
    document.getElementById('templateForm').method = 'POST';

    // Reset form
    document.getElementById('templateId').value = '';
    document.getElementById('templateName').value = '';
    document.getElementById('templateType').value = '';
    document.getElementById('templateFile').required = true;
    document.getElementById('currentFile').textContent = '';
    document.getElementById('nameX').value = '50';
    document.getElementById('nameY').value = '120';
    document.getElementById('courseX').value = '50';
    document.getElementById('courseY').value = '150';

    document.getElementById('templateModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Open edit modal
function openEditModal(templateId) {
    fetch(`/certificate-templates/${templateId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Template';
            document.getElementById('templateForm').action = `/certificate-templates/${templateId}`;
            document.getElementById('templateForm').method = 'PUT';

            document.getElementById('templateId').value = data.template_id;
            document.getElementById('templateName').value = data.template_name;
            document.getElementById('templateType').value = data.template_type;
            document.getElementById('templateFile').required = false;
            document.getElementById('currentFile').textContent = `Current: ${data.template_file.split('/').pop()}`;
            document.getElementById('nameX').value = data.name_x;
            document.getElementById('nameY').value = data.name_y;
            document.getElementById('courseX').value = data.course_x;
            document.getElementById('courseY').value = data.course_y;

            document.getElementById('templateModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        })
        .catch(error => {
            console.error('Error loading template:', error);
            alert('Error loading template data');
        });
}

// Test template
function testTemplate(templateId) {
    document.getElementById('testForm').action = `/certificate-templates/${templateId}/test`;
    document.getElementById('testModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Delete confirmation
function confirmDelete(deleteUrl) {
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
</script>
@endsection
