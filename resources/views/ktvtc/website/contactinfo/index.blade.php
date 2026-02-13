@extends('ktvtc.website.layout.websitelayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded bg-green-100 text-green-800 border-l-4 border-green-600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded bg-red-100 text-red-800 border-l-4 border-red-600">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li> {{ $error }} </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Add Contact Info Button --}}
    <div class="flex justify-end mb-4">
        <button onclick="openModal('addContactModal')"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary">
            Add Contact Info
        </button>
    </div>

    {{-- Contact Info Table --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-dark mb-4">Contact Information</h2>
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Address</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Phone</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Email</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Map Link</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $contact->address }}</td>
                        <td class="px-4 py-2">{{ $contact->phone }}</td>
                        <td class="px-4 py-2">{{ $contact->email }}</td>
                        <td class="px-4 py-2">
                            @if($contact->map_link)
                                <a href="{{ $contact->map_link }}" target="_blank"
                                   class="text-primary hover:underline">View Map</a>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 space-x-2">
                            <button onclick="openModal('editContactModal{{ $contact->id }}')"
                                    class="px-3 py-1 text-sm bg-accent text-dark rounded hover:bg-primary hover:text-white">
                                Edit
                            </button>
                            <button onclick="openModal('deleteContactModal{{ $contact->id }}')"
                                    class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                Delete
                            </button>
                        </td>
                    </tr>

                    {{-- Edit Contact Modal --}}
                    <div id="editContactModal{{ $contact->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                            <h3 class="text-lg font-semibold mb-4">Edit Contact Info</h3>
                            <form action="{{ route('contact-infos.update', $contact->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Address</label>
                                        <input type="text" name="address" value="{{ $contact->address }}" class="w-full border rounded-lg px-3 py-2" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="text" name="phone" value="{{ $contact->phone }}" class="w-full border rounded-lg px-3 py-2" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" value="{{ $contact->email }}" class="w-full border rounded-lg px-3 py-2" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Map Link</label>
                                        <input type="text" name="map_link" value="{{ $contact->map_link }}" class="w-full border rounded-lg px-3 py-2">
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button type="button" onclick="closeModal('editContactModal{{ $contact->id }}')" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Delete Contact Modal --}}
                    <div id="deleteContactModal{{ $contact->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="bg-white rounded-lg p-6 w-full max-w-md">
                            <h3 class="text-lg font-semibold mb-4">Confirm Delete</h3>
                            <p>Are you sure you want to delete <strong>{{ $contact->address }}</strong>?</p>
                            <form action="{{ route('contact-infos.destroy', $contact->id) }}" method="POST" class="mt-4 flex justify-end space-x-2">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="closeModal('deleteContactModal{{ $contact->id }}')" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No contact info found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Contact Modal --}}
<div id="addContactModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">Add Contact Info</h3>
        <form action="{{ route('contact-infos.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <input type="text" name="address" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="w-full border rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Map Link</label>
                    <input type="text" name="map_link" class="w-full border rounded-lg px-3 py-2">
                </div>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <button type="button" onclick="closeModal('addContactModal')" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Scripts --}}
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>
@endsection
