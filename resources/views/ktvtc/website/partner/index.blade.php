
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

    {{-- Add Partner Button --}}
    <div class="flex justify-end mb-4">
        <button onclick="openModal('addPartnerModal')"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary">
            Add Partner
        </button>
    </div>

    {{-- Partners Table --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-dark mb-4">Partners</h2>
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">#</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Logo</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Name</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Website</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Status</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($partners as $index => $partner)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">
                            @if($partner->logo_path)
                                <img src="{{ asset('storage/'.$partner->logo_path) }}" alt="{{ $partner->name }}" class="h-10">
                            @else
                                <span class="text-gray-400">No Logo</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 font-semibold text-dark">{{ $partner->name }}</td>
                        <td class="px-4 py-2">
                            @if($partner->website)
                                <a href="{{ $partner->website }}" target="_blank"
                                   class="text-primary hover:underline">{{ $partner->website }}</a>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($partner->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 space-x-2">
                            <button onclick="openModal('editPartnerModal{{ $partner->id }}')"
                                    class="px-3 py-1 text-sm bg-accent text-dark rounded hover:bg-primary hover:text-white">
                                Edit
                            </button>
                            <button onclick="openModal('deletePartnerModal{{ $partner->id }}')"
                                    class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                Delete
                            </button>
                        </td>
                    </tr>

                    {{-- Edit Partner Modal --}}
                    <div id="editPartnerModal{{ $partner->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                            <h3 class="text-lg font-semibold mb-4">Edit Partner</h3>
                            <form action="{{ route('partners.update', $partner->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" name="name" value="{{ $partner->name }}"
                                               class="w-full border rounded-lg px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Logo</label>
                                        <input type="file" name="logo_path" class="w-full">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Website</label>
                                        <input type="url" name="website" value="{{ $partner->website }}"
                                               class="w-full border rounded-lg px-3 py-2">
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" {{ $partner->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 text-primary border-gray-300 rounded">
                                        <label class="ml-2 text-sm">Active</label>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button type="button" onclick="closeModal('editPartnerModal{{ $partner->id }}')"
                                            class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                    <button type="submit"
                                            class="px-4 py-2 bg-primary text-white rounded">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Delete Partner Modal --}}
                    <div id="deletePartnerModal{{ $partner->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="bg-white rounded-lg p-6 w-full max-w-md">
                            <h3 class="text-lg font-semibold mb-4">Confirm Delete</h3>
                            <p>Are you sure you want to delete <strong>{{ $partner->name }}</strong>?</p>
                            <form action="{{ route('partners.destroy', $partner->id) }}" method="POST" class="mt-4 flex justify-end space-x-2">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="closeModal('deletePartnerModal{{ $partner->id }}')"
                                        class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">No partners available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Partner Modal --}}
<div id="addPartnerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4">Add Partner</h3>
        <form action="{{ route('partners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Logo</label>
                    <input type="file" name="logo_path" class="w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Website</label>
                    <input type="url" name="website" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="h-4 w-4 text-primary border-gray-300 rounded">
                    <label class="ml-2 text-sm">Active</label>
                </div>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <button type="button" onclick="closeModal('addPartnerModal')"
                        class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded">Add</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Scripts --}}
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection
