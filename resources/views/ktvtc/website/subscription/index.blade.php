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
        <h1 class="text-2xl font-bold text-gray-800">Subscribers Management</h1>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-md transition">
            Add New Subscriber
        </button>
    </div>

    {{-- Subscriptions List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Added On</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($subscriptions as $index => $subscription)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-dark">{{ $subscription->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $subscription->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($subscription->status === 'active')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full">Unsubscribed</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $subscription->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap space-x-2">
                            <button onclick="openEditModal({{ $subscription->id }}, '{{ addslashes($subscription->name) }}', '{{ addslashes($subscription->email) }}', '{{ $subscription->status }}')"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Edit
                            </button>
                            <button onclick="confirmDelete('{{ route('subscriptions.destroy', $subscription->id) }}')"
                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No subscribers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeCreateModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 relative" onclick="event.stopPropagation()">
            <button onclick="closeCreateModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Subscriber</h3>
            <form action="{{ route('subscriptions.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name (optional)</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Enter subscriber name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Enter email address" required>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="status" value="active" checked
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label class="ml-2 text-sm text-gray-700">Active subscriber</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeCreateModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">
                        Add Subscriber
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeEditModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 relative" onclick="event.stopPropagation()">
            <button onclick="closeEditModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Subscriber</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name (optional)</label>
                        <input type="text" id="editName" name="name"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Enter subscriber name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" id="editEmail" name="email"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Enter email address" required>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="editStatus" name="status" value="active"
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label class="ml-2 text-sm text-gray-700">Active subscriber</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">
                        Update Subscriber
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
    function openEditModal(id, name, email, status) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');

        // Set form action
        document.getElementById('editForm').action = `/subscriptions/${id}`;

        // Fill form fields
        document.getElementById('editName').value = name || '';
        document.getElementById('editEmail').value = email;
        document.getElementById('editStatus').checked = status === 'active';
    }

    function closeEditModal(event = null) {
        if(event && event.target !== document.getElementById('editModal')) return;
        document.getElementById('editModal').classList.add('hidden');
    }

    // Delete Confirmation
    function confirmDelete(url) {
        if(confirm('Are you sure you want to delete this subscriber?')) {
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
