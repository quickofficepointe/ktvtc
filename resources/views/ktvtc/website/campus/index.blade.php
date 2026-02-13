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
        <h1 class="text-2xl font-bold text-gray-800">Campuses Management</h1>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-md transition">
            Add New Campus
        </button>
    </div>

    {{-- Campuses List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($campuses as $index => $campus)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ($campuses->currentPage() - 1) * $campuses->perPage() + $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $campus->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-dark">{{ $campus->name }}</div>
                            @if($campus->description)
                                <div class="text-sm text-gray-500 mt-1">{{ Str::limit($campus->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-700">
                                @if($campus->address)
                                    <div>{{ $campus->address }}</div>
                                @endif
                                @if($campus->city || $campus->state)
                                    <div class="text-sm">{{ $campus->city }}{{ $campus->city && $campus->state ? ', ' : '' }}{{ $campus->state }}</div>
                                @endif
                            </div>
                            @if($campus->google_map_link)
                                <a href="{{ $campus->google_map_link }}" target="_blank"
                                   class="text-primary hover:underline text-sm inline-flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    View Map
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($campus->phone)
                                <div class="text-sm">{{ $campus->phone }}</div>
                            @endif
                            @if($campus->email)
                                <div class="text-sm text-gray-600">{{ $campus->email }}</div>
                            @endif
                            @if($campus->website)
                                <a href="{{ $campus->website }}" target="_blank"
                                   class="text-sm text-primary hover:underline">
                                    Website
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($campus->opening_time && $campus->closing_time)
                                <div class="text-sm">
                                    {{ \Carbon\Carbon::parse($campus->opening_time)->format('h:i A') }} -
                                    {{ \Carbon\Carbon::parse($campus->closing_time)->format('h:i A') }}
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($campus->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap space-x-2">
                            <button onclick="openEditModal({{ $campus->id }}, '{{ addslashes($campus->name) }}', '{{ addslashes($campus->code) }}', `{!! addslashes($campus->description) !!}`, '{{ addslashes($campus->address) }}', '{{ addslashes($campus->city) }}', '{{ addslashes($campus->state) }}', '{{ addslashes($campus->country) }}', '{{ addslashes($campus->postal_code) }}', '{{ addslashes($campus->phone) }}', '{{ addslashes($campus->email) }}', '{{ addslashes($campus->website) }}', '{{ addslashes($campus->google_map_link) }}', '{{ $campus->opening_time }}', '{{ $campus->closing_time }}', '{{ addslashes($campus->timezone) }}', {{ $campus->is_active }})"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                Edit
                            </button>
                            <button onclick="confirmDelete('{{ route('campuses.destroy', $campus->id) }}')"
                                class="text-red-600 hover:text-red-900 text-sm font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No Campuses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($campuses->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $campuses->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50" onclick="closeCreateModal(event)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 relative max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <button onclick="closeCreateModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Campus</h3>
            <form action="{{ route('campuses.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code *</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent uppercase"
                               required maxlength="10">
                        <p class="mt-1 text-xs text-gray-500">Unique campus code (e.g., MAIN, DTWN)</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Brief description of the campus">{{ old('description') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="2"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Full physical address">{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="city" value="{{ old('city') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">State/Province</label>
                        <input type="text" name="state" value="{{ old('state') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country</label>
                        <input type="text" name="country" value="{{ old('country', 'US') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Website</label>
                        <input type="url" name="website" value="{{ old('website') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="https://example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Google Map Link</label>
                        <input type="url" name="google_map_link" value="{{ old('google_map_link') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="https://maps.google.com/...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Opening Time</label>
                        <input type="time" name="opening_time" value="{{ old('opening_time') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Closing Time</label>
                        <input type="time" name="closing_time" value="{{ old('closing_time') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Timezone</label>
                        <select name="timezone" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="UTC" {{ old('timezone', 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>Eastern Time (ET)</option>
                            <option value="America/Chicago" {{ old('timezone') == 'America/Chicago' ? 'selected' : '' }}>Central Time (CT)</option>
                            <option value="America/Denver" {{ old('timezone') == 'America/Denver' ? 'selected' : '' }}>Mountain Time (MT)</option>
                            <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (PT)</option>
                            <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>London (GMT)</option>
                            <option value="Europe/Paris" {{ old('timezone') == 'Europe/Paris' ? 'selected' : '' }}>Paris (CET)</option>
                            <option value="Asia/Tokyo" {{ old('timezone') == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo (JST)</option>
                            <option value="Australia/Sydney" {{ old('timezone') == 'Australia/Sydney' ? 'selected' : '' }}>Sydney (AEST)</option>
                            <option value="Africa/Nairobi" {{ old('timezone') == 'Africa/Nairobi' ? 'selected' : '' }}>Nairobi (EAT)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Active Campus</label>
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
                        Add Campus
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
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Campus</h3>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name *</label>
                        <input type="text" id="editName" name="name"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code *</label>
                        <input type="text" id="editCode" name="code"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent uppercase"
                               required maxlength="10">
                        <p class="mt-1 text-xs text-gray-500">Unique campus code</p>
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
                        <label class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" id="editCity" name="city"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">State/Province</label>
                        <input type="text" id="editState" name="state"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country</label>
                        <input type="text" id="editCountry" name="country"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" id="editPostalCode" name="postal_code"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" id="editPhone" name="phone"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="editEmail" name="email"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Website</label>
                        <input type="url" id="editWebsite" name="website"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Google Map Link</label>
                        <input type="url" id="editGoogleMapLink" name="google_map_link"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Opening Time</label>
                        <input type="time" id="editOpeningTime" name="opening_time"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Closing Time</label>
                        <input type="time" id="editClosingTime" name="closing_time"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Timezone</label>
                        <select id="editTimezone" name="timezone" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">Eastern Time (ET)</option>
                            <option value="America/Chicago">Central Time (CT)</option>
                            <option value="America/Denver">Mountain Time (MT)</option>
                            <option value="America/Los_Angeles">Pacific Time (PT)</option>
                            <option value="Europe/London">London (GMT)</option>
                            <option value="Europe/Paris">Paris (CET)</option>
                            <option value="Asia/Tokyo">Tokyo (JST)</option>
                            <option value="Australia/Sydney">Sydney (AEST)</option>
                            <option value="Africa/Nairobi">Nairobi (EAT)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="editIsActive" name="is_active" value="1"
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700">Active Campus</label>
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
                        Update Campus
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
    function openEditModal(id, name, code, description, address, city, state, country, postalCode, phone, email, website, googleMapLink, openingTime, closingTime, timezone, isActive) {
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');

        // Set form action
        document.getElementById('editForm').action = `/website/campuses/${id}`;

        // Fill form fields
        document.getElementById('editName').value = name;
        document.getElementById('editCode').value = code;
        document.getElementById('editDescription').value = description;
        document.getElementById('editAddress').value = address;
        document.getElementById('editCity').value = city;
        document.getElementById('editState').value = state;
        document.getElementById('editCountry').value = country;
        document.getElementById('editPostalCode').value = postalCode;
        document.getElementById('editPhone').value = phone;
        document.getElementById('editEmail').value = email;
        document.getElementById('editWebsite').value = website;
        document.getElementById('editGoogleMapLink').value = googleMapLink;
        document.getElementById('editOpeningTime').value = openingTime;
        document.getElementById('editClosingTime').value = closingTime;
        document.getElementById('editTimezone').value = timezone || 'UTC';
        document.getElementById('editIsActive').checked = isActive;
    }

    function closeEditModal(event = null) {
        if(event && event.target !== document.getElementById('editModal')) return;
        document.getElementById('editModal').classList.add('hidden');
    }

    // Delete Confirmation
    function confirmDelete(url) {
        if(confirm('Are you sure you want to delete this Campus? All associated data will be lost.')) {
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
