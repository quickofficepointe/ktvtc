```blade
@extends('ktvtc.website.layout.websitelayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded bg-green-100 text-green-800 border-l-4 border-green-600">
            {{ session('success') }}
        </div>
    @endif

    {{-- Errors --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded bg-red-100 text-red-800 border-l-4 border-red-600">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li> {{ $error }} </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-dark mb-4">Messages</h2>
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">#</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Name</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Email</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Phone</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Message</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Status</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Received</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $index => $msg)
                    <tr class="border-t {{ $msg->is_seen ? 'bg-gray-50' : 'bg-orange-50' }}">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 font-medium text-dark">{{ $msg->name }}</td>
                        <td class="px-4 py-2 text-gray-700">{{ $msg->email }}</td>
                        <td class="px-4 py-2 text-gray-700">{{ $msg->phone ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-gray-800">{{ Str::limit($msg->message, 50) }}</td>
                        <td class="px-4 py-2">
                            @if($msg->is_seen)
                                <span class="px-2 py-1 text-xs rounded bg-green-200 text-green-800">Seen</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-yellow-200 text-yellow-800">New</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $msg->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-2 space-x-2">
                            {{-- Mark Seen --}}
                            @if(!$msg->is_seen)
                                <form action="{{ route('messages.update', $msg->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="is_seen" value="1">
                                    <button type="submit"
                                            class="px-3 py-1 text-sm bg-primary text-white rounded hover:bg-secondary">
                                        Mark Seen
                                    </button>
                                </form>
                            @endif

                            {{-- Delete --}}
                            <form action="{{ route('messages.destroy', $msg->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this message?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </form>

                            {{-- View Full --}}
                            <button onclick="toggleMessage('{{ $msg->id }}')"
                                    class="px-3 py-1 text-sm bg-accent text-dark rounded hover:bg-primary hover:text-white">
                                View
                            </button>
                        </td>
                    </tr>
                    {{-- Full message row --}}
                    <tr id="msg-{{ $msg->id }}" class="hidden bg-gray-50">
                        <td colspan="8" class="px-6 py-4 text-gray-700">
                            <strong>Message:</strong>
                            <p class="mt-2">{{ $msg->message }}</p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">No messages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleMessage(id) {
        const row = document.getElementById('msg-' + id);
        row.classList.toggle('hidden');
    }
</script>
@endsection
