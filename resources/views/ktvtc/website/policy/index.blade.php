
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

    {{-- Add Policy Button --}}
    <div class="flex justify-end mb-4">
        <button onclick="openModal('addPolicyModal')"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary">
            Add Policy
        </button>
    </div>

    {{-- Policies Table --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-dark mb-4">Policies</h2>
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">#</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Title</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Content</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Status</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($policies as $index => $policy)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 font-semibold text-dark">{{ $policy->title }}</td>
                        <td class="px-4 py-2 text-gray-700">{{ Str::limit($policy->content, 100) }}</td>
                        <td class="px-4 py-2">
                            @if($policy->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 space-x-2">
                            <button onclick="openModal('editPolicyModal{{ $policy->id }}')"
                                    class="px-3 py-1 text-sm bg-accent text-dark rounded hover:bg-primary hover:text-white">
                                Edit
                            </button>
                            <button onclick="openModal('deletePolicyModal{{ $policy->id }}')"
                                    class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                Delete
                            </button>
                        </td>
                    </tr>

                    {{-- Edit Policy Modal --}}
                    <div id="editPolicyModal{{ $policy->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-full max-w-lg relative">
                            <h3 class="text-lg font-semibold mb-4">Edit Policy</h3>
                            <form action="{{ route('policies.update', $policy->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Title</label>
                                        <input type="text" name="title" value="{{ $policy->title }}"
                                               class="w-full border rounded-lg px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Content</label>
                                        <textarea name="content" rows="4" id="summernote"
                                                  class="w-full border rounded-lg px-3 py-2">{{ $policy->content }}</textarea>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 text-primary border-gray-300 rounded">
                                        <label class="ml-2 text-sm">Active</label>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button type="button" onclick="closeModal('editPolicyModal{{ $policy->id }}')"
                                            class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                    <button type="submit"
                                            class="px-4 py-2 bg-primary text-white rounded">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Delete Policy Modal --}}
                    <div id="deletePolicyModal{{ $policy->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-full max-w-md relative">
                            <h3 class="text-lg font-semibold mb-4">Confirm Delete</h3>
                            <p>Are you sure you want to delete <strong>{{ $policy->title }}</strong>?</p>
                            <form action="{{ route('policies.destroy', $policy->id) }}" method="POST" class="mt-4 flex justify-end space-x-2">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="closeModal('deletePolicyModal{{ $policy->id }}')"
                                        class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No Policies available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Policy Modal --}}
<div id="addPolicyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg relative">
        <h3 class="text-lg font-semibold mb-4">Add Policy</h3>
        <form action="{{ route('policies.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Content</label>
                    <textarea name="content" rows="4" id="summernote"
                              class="w-full border rounded-lg px-3 py-2"></textarea>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="h-4 w-4 text-primary border-gray-300 rounded">
                    <label class="ml-2 text-sm">Active</label>
                </div>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <button type="button" onclick="closeModal('addPolicyModal')"
                        class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded">Add</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Script --}}
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
</script>
@endsection

