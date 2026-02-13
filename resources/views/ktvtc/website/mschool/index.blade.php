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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
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
                            @if($mschool->cover_image_url)
                                <div class="w-16 h-16 rounded overflow-hidden">
                                    <img src="{{ $mschool->cover_image_url }}"
                                         alt="{{ $mschool->name }}"
                                         class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-16 h-16 rounded overflow-hidden bg-gray-200 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </td>
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
                            <button onclick="openEditModal({{ $mschool->id }}, '{{ addslashes($mschool->name) }}', `{!! addslashes($mschool->description) !!}`, '{{ addslashes($mschool->address) }}', '{{ $mschool->google_map_link }}', '{{ $mschool->latitude }}', '{{ $mschool->longitude }}', '{{ addslashes($mschool->coordinator_name) }}', '{{ addslashes($mschool->coordinator_email) }}', '{{ addslashes($mschool->coordinator_phone) }}', '{{ $mschool->cover_image_url }}', {{ $mschool->is_active }}, {{ json_encode($mschool->gallery_images_urls) }})"
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
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No Mobile Schools found.</td>
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
            <form action="{{ route('mschools.store') }}" method="POST" enctype="multipart/form-data">
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

                    <!-- Cover Image Upload -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Cover Image</label>
                        <div class="mt-1 flex items-center space-x-4">
                            <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-md flex items-center justify-center bg-gray-50">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <label for="cover_image" class="cursor-pointer text-primary hover:text-red-700">
                                            Upload image
                                            <input type="file" id="cover_image" name="cover_image" class="hidden" accept="image/*" onchange="previewImage(this, 'createCoverPreview')">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id="createCoverPreview" class="w-32 h-32 hidden">
                                <img class="w-full h-full object-cover rounded-md">
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended size: 1200x800px. Max: 2MB</p>
                    </div>

                    <!-- Gallery Images Upload -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Gallery Images (Optional)</label>
                        <div class="mt-1">
                            <input type="file" id="gallery_images" name="gallery_images[]" multiple
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-red-700"
                                   accept="image/*" onchange="previewGalleryImages(this, 'createGalleryPreview')">
                        </div>
                        <div id="createGalleryPreview" class="mt-2 flex flex-wrap gap-2 hidden"></div>
                        <p class="mt-1 text-xs text-gray-500">Multiple images allowed. Max: 2MB each</p>
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
            <form id="editForm" method="POST" enctype="multipart/form-data">
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
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea id="editAddress" name="address" rows="2"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
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

                    <!-- Current Cover Image -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Current Cover Image</label>
                        <div class="mt-1 flex items-center space-x-4">
                            <div id="currentCoverImage" class="w-32 h-32">
                                <!-- Will be populated by JavaScript -->
                            </div>
                            <div id="editCoverPreview" class="w-32 h-32 hidden">
                                <img class="w-full h-full object-cover rounded-md">
                                <button type="button" onclick="clearEditCoverPreview()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center">×</button>
                            </div>
                        </div>
                        <div class="mt-2 space-y-2">
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="remove_cover_image" value="1" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Remove current cover image</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Upload New Cover Image</label>
                                <input type="file" name="cover_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" onchange="previewEditImage(this, 'editCoverPreview')">
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Images -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Gallery Images</label>
                        <div id="currentGalleryImages" class="mt-2 space-y-4">
                            <!-- Will be populated by JavaScript -->
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Add More Images</label>
                            <input type="file" name="gallery_images[]" multiple accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                                   onchange="previewEditGalleryImages(this, 'editGalleryPreview')">
                            <div id="editGalleryPreview" class="mt-2 flex flex-wrap gap-2"></div>
                        </div>
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
        resetCreateForm();
    }

    function resetCreateForm() {
        document.querySelector('#createModal form').reset();
        document.getElementById('createCoverPreview').classList.add('hidden');
        document.getElementById('createGalleryPreview').classList.add('hidden');
        document.getElementById('createGalleryPreview').innerHTML = '';
    }

    // Edit Modal Functions
    function openEditModal(id, name, description, address, googleMapLink, latitude, longitude, coordinatorName, coordinatorEmail, coordinatorPhone, coverImageUrl, isActive, galleryImages) {
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
        document.getElementById('editIsActive').checked = isActive;

        // Show current cover image
        const currentCoverDiv = document.getElementById('currentCoverImage');
        currentCoverDiv.innerHTML = '';
        if (coverImageUrl) {
            currentCoverDiv.innerHTML = `
                <img src="${coverImageUrl}" alt="Current cover" class="w-32 h-32 object-cover rounded-md">
                <div class="text-xs text-gray-500 mt-1">Current Image</div>
            `;
        } else {
            currentCoverDiv.innerHTML = `
                <div class="w-32 h-32 bg-gray-200 rounded-md flex items-center justify-center">
                    <span class="text-gray-400 text-sm">No Image</span>
                </div>
            `;
        }

        // Show current gallery images
        const galleryDiv = document.getElementById('currentGalleryImages');
        galleryDiv.innerHTML = '';

        try {
            galleryImages = galleryImages ? JSON.parse(galleryImages) : [];
        } catch (e) {
            galleryImages = [];
        }

        if (galleryImages.length > 0) {
            galleryImages.forEach((imageUrl, index) => {
                galleryDiv.innerHTML += `
                    <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-md">
                        <div class="w-20 h-20">
                            <img src="${imageUrl}" alt="Gallery image ${index + 1}" class="w-full h-full object-cover rounded">
                        </div>
                        <div class="flex-grow">
                            <div class="text-sm text-gray-600">Gallery Image ${index + 1}</div>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remove_gallery_images[]" value="${index}" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-red-600">Remove</span>
                            </label>
                        </div>
                    </div>
                `;
            });
        } else {
            galleryDiv.innerHTML = '<p class="text-gray-500 text-sm">No gallery images</p>';
        }

        // Reset previews
        document.getElementById('editCoverPreview').classList.add('hidden');
        document.getElementById('editGalleryPreview').innerHTML = '';
    }

    function closeEditModal(event = null) {
        if(event && event.target !== document.getElementById('editModal')) return;
        document.getElementById('editModal').classList.add('hidden');
    }

    // Image Preview Functions
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewGalleryImages(input, previewId) {
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';

        if (input.files) {
            preview.classList.remove('hidden');
            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative w-20 h-20';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}" class="w-full h-full object-cover rounded">
                        <button type="button" onclick="removeGalleryPreview(this)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">×</button>
                    `;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function previewEditImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.querySelector('img').src = e.target.result;
                preview.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewEditGalleryImages(input, previewId) {
        const preview = document.getElementById(previewId);

        if (input.files) {
            Array.from(input.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative w-20 h-20';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="New preview ${index + 1}" class="w-full h-full object-cover rounded">
                    `;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    function clearEditCoverPreview() {
        const preview = document.getElementById('editCoverPreview');
        preview.classList.add('hidden');
        preview.querySelector('img').src = '';
        // Also clear the file input
        const fileInput = document.querySelector('#editForm input[name="cover_image"]');
        fileInput.value = '';
    }

    function removeGalleryPreview(button) {
        button.parentElement.remove();
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
