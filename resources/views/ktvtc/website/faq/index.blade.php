@extends('ktvtc.website.layout.websitelayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded bg-green-100 text-green-800 border-l-4 border-green-600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header with Add Button --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">FAQ Management</h1>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-md transition">
            Add New FAQ
        </button>
    </div>

    {{-- FAQ List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Question</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Answer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($faqs as $index => $faq)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-semibold text-dark">{{ $faq->question }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ Str::limit($faq->answer, 80) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($faq->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $faq->position }}</td>
                        <td class="px-6 py-4 whitespace-nowrap space-x-2">
                            <button onclick="openEditModal({{ $faq->id }}, '{{ addslashes($faq->question) }}', `{!! addslashes($faq->answer) !!}`, {{ $faq->position }}, {{ $faq->is_active }})"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Edit
                            </button>
                            <button onclick="confirmDelete('{{ route('faqs.destroy', $faq->id) }}')"
                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No FAQs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeCreateModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 relative" onclick="event.stopPropagation()">
            <button onclick="closeCreateModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New FAQ</h3>
            <form action="{{ route('faqs.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Question</label>
                        <input type="text" name="question" value="{{ old('question') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Answer</label>
                        <textarea name="answer" rows="4"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  required>{{ old('answer') }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Position</label>
                            <input type="number" name="position" value="{{ old('position', 0) }}"
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Active</label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeCreateModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">
                        Add FAQ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeEditModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 relative" onclick="event.stopPropagation()">
            <button onclick="closeEditModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit FAQ</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Question</label>
                        <input type="text" id="editQuestion" name="question"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Answer</label>
                        <textarea id="editAnswer" name="answer" rows="4"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  required></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Position</label>
                            <input type="number" id="editPosition" name="position"
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="editIsActive" name="is_active" value="1"
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Active</label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">
                        Update FAQ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Create Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }

    function closeCreateModal(event = null) {
        if(event && event.target !== document.getElementById('createModal')) return;
        document.getElementById('createModal').classList.add('hidden');
    }

    // Edit Modal Functions
    function openEditModal(id, question, answer, position, isActive) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');

        // Set form action
        document.getElementById('editForm').action = `/faqs/${id}`;

        // Fill form fields
        document.getElementById('editQuestion').value = question;
        document.getElementById('editAnswer').value = answer;
        document.getElementById('editPosition').value = position;
        document.getElementById('editIsActive').checked = isActive;
    }

    function closeEditModal(event = null) {
        if(event && event.target !== document.getElementById('editModal')) return;
        document.getElementById('editModal').classList.add('hidden');
    }

    // Delete Confirmation
    function confirmDelete(url) {
        if(confirm('Are you sure you want to delete this FAQ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';

            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>
@endsection
