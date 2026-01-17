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
        <h1 class="text-2xl font-bold text-gray-800">Mobile Schools Management</h1>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-md transition">
            Add New Mobile School
        </button>
    </div>

    {{-- Mobile Schools List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coordinator</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($mschools as $index => $mschool)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-dark">{{ $mschool->name }}</div>
                            @if($mschool->description)
                                <div class="text-sm text-gray-500 mt-1">{{ Str::limit($mschool->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-700">{{ $mschool->address }}</div>
                            @if($mschool->google_map_link)
                                <a href="{{ $mschool->google_map_link }}" target="_blank"
                                   class="text-primary hover:underline text-sm inline-flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    View Map
                                </a>
                            @endif
                            @if($mschool->latitude && $mschool->longitude)
                                <div class="text-xs text-gray-500 mt-1">
                                    Lat: {{ $mschool->latitude }}, Lng: {{ $mschool->longitude }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($mschool->coordinator_name)
                                <div class="font-medium text-dark">{{ $mschool->coordinator_name }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($mschool->coordinator_email)
                                <div class="text-sm">{{ $mschool->coordinator_email }}</div>
                            @endif
                            @if($mschool->coordinator_phone)
                                <div class="text-sm text-gray-600">{{ $mschool->coordinator_phone }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($mschool->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap space-x-2">
                            <button onclick="openEditModal({{ $mschool->id }}, '{{ addslashes($mschool->name) }}', `{!! addslashes($mschool->description) !!}`, '{{ addslashes($mschool->address) }}', '{{ addslashes($mschool->google_map_link) }}', '{{ $mschool->latitude }}', '{{ $mschool->longitude }}', '{{ addslashes($mschool->coordinator_name) }}', '{{ addslashes($mschool->coordinator_email) }}', '{{ addslashes($mschool->coordinator_phone) }}', '{{ addslashes($mschool->cover_image) }}', {{ $mschool->is_active }})"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Edit
                            </button>
                            <button onclick="confirmDelete('{{ route('mschools.destroy', $mschool->id) }}')"
                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No Mobile Schools found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeCreateModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 relative max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <button onclick="closeCreateModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Mobile School</h3>
            <form action="{{ route('mschools.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Brief description of the mobile school">{{ old('description') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="2"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Full physical address">{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Latitude</label>
                        <input type="text" name="latitude" value="{{ old('latitude') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., -1.2921">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Longitude</label>
                        <input type="text" name="longitude" value="{{ old('longitude') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 36.8219">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Google Map Link</label>
                        <input type="url" name="google_map_link" value="{{ old('google_map_link') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="https://maps.google.com/...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Coordinator Name</label>
                        <input type="text" name="coordinator_name" value="{{ old('coordinator_name') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Coordinator Email</label>
                        <input type="email" name="coordinator_email" value="{{ old('coordinator_email') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Coordinator Phone</label>
                        <input type="text" name="coordinator_phone" value="{{ old('coordinator_phone') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Cover Image URL</label>
                        <input type="text" name="cover_image" value="{{ old('cover_image') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="URL to cover image">
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Active Mobile School</label>
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
                        Add Mobile School
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeEditModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 relative max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <button onclick="closeEditModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Mobile School</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Name *</label>
                        <input type="text" id="editName" name="name"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="editDescription" name="description" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('description') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea id="editAddress" name="address" rows="2"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Latitude</label>
                        <input type="text" id="editLatitude" name="latitude"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Longitude</label>
                        <input type="text" id="editLongitude" name="longitude"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Google Map Link</label>
                        <input type="url" id="editGoogleMapLink" name="google_map_link"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Coordinator Name</label>
                        <input type="text" id="editCoordinatorName" name="coordinator_name"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Coordinator Email</label>
                        <input type="email" id="editCoordinatorEmail" name="coordinator_email"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Coordinator Phone</label>
                        <input type="text" id="editCoordinatorPhone" name="coordinator_phone"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Cover Image URL</label>
                        <input type="text" id="editCoverImage" name="cover_image"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="editIsActive" name="is_active" value="1"
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Active Mobile School</label>
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
                        Update Mobile School
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
    function openEditModal(id, name, description, address, googleMapLink, latitude, longitude, coordinatorName, coordinatorEmail, coordinatorPhone, coverImage, isActive) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');

        // Set form action
        document.getElementById('editForm').action = `/mschools/${id}`;

        // Fill form fields
        document.getElementById('editName').value = name;
        document.getElementById('editDescription').value = description;
        document.getElementById('editAddress').value = address;
        document.getElementById('editGoogleMapLink').value = googleMapLink;
        document.getElementById('editLatitude').value = latitude;
        document.getElementById('editLongitude').value = longitude;
        document.getElementById('editCoordinatorName').value = coordinatorName;
        document.getElementById('editCoordinatorEmail').value = coordinatorEmail;
        document.getElementById('editCoordinatorPhone').value = coordinatorPhone;
        document.getElementById('editCoverImage').value = coverImage;
        document.getElementById('editIsActive').checked = isActive;
    }

    function closeEditModal(event = null) {
        if(event && event.target !== document.getElementById('editModal')) return;
        document.getElementById('editModal').classList.add('hidden');
    }

    // Delete Confirmation
    function confirmDelete(url) {
        if(confirm('Are you sure you want to delete this Mobile School?')) {
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
