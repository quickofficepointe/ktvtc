@extends('ktvtc.admin.layout.adminlayout')
@section('content')

<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4 md:mb-0">Contact Messages</h1>
        <div class="flex gap-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                Unread: {{ $unreadCount }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary text-white">
                Total: {{ $messages->count() }}
            </span>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">From</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Message</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Received</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($messages as $message)
                        <tr class="{{ $message->is_seen ? '' : 'bg-yellow-50' }} hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <strong class="text-gray-900">{{ $message->name }}</strong>
                                    @if(!$message->is_seen)
                                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        New
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm">
                                    <a href="mailto:{{ $message->email }}" class="text-primary hover:text-red-800 transition-colors">
                                        {{ $message->email }}
                                    </a>
                                    @if($message->phone)
                                    <br>
                                    <small class="text-gray-600">{{ $message->phone }}</small>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="max-w-xs">
                                    <p class="text-sm text-gray-900 truncate">
                                        {{ Str::limit($message->message, 100) }}
                                    </p>
                                    @if(strlen($message->message) > 100)
                                    <button class="text-primary hover:text-red-800 text-sm font-medium transition-colors"
                                            onclick="openMessageModal('{{ $message->id }}')">
                                        Read more
                                    </button>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $message->action_badge }}">
                                    {{ $message->action ? ucfirst($message->action) : 'No Action' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $message->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <button type="button"
                                            onclick="openMessageModal('{{ $message->id }}')"
                                            class="inline-flex items-center p-2 border border-primary text-primary rounded-lg hover:bg-primary hover:text-white transition-colors">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    <div class="relative">
                                        <button type="button"
                                                onclick="toggleDropdown('dropdown-{{ $message->id }}')"
                                                class="inline-flex items-center p-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-ellipsis-v text-sm"></i>
                                        </button>
                                        <div id="dropdown-{{ $message->id }}"
                                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-10">
                                            <div class="py-1">
                                                <a href="#"
                                                   onclick="markAsSeen('{{ $message->id }}', true)"
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i class="fas fa-check mr-2 text-green-600"></i>
                                                    Mark as Read
                                                </a>
                                                <a href="#"
                                                   onclick="updateAction('{{ $message->id }}', 'responded')"
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i class="fas fa-reply mr-2 text-blue-600"></i>
                                                    Mark as Responded
                                                </a>
                                                <a href="#"
                                                   onclick="updateAction('{{ $message->id }}', 'pending')"
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i class="fas fa-clock mr-2 text-yellow-600"></i>
                                                    Mark as Pending
                                                </a>
                                                <div class="border-t border-gray-200 my-1"></div>
                                                <a href="#"
                                                   onclick="deleteMessage('{{ $message->id }}')"
                                                   class="flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                    <i class="fas fa-trash mr-2"></i>
                                                    Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Message Modal -->
                        <div id="messageModal{{ $message->id }}"
                             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
                            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900">Message from {{ $message->name }}</h3>
                                    <button type="button"
                                            onclick="closeMessageModal('{{ $message->id }}')"
                                            class="text-gray-400 hover:text-gray-600 transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                            <a href="mailto:{{ $message->email }}"
                                               class="text-primary hover:text-red-800 transition-colors">
                                                {{ $message->email }}
                                            </a>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                            <p class="text-gray-900">{{ $message->phone ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                            <p class="text-gray-700 whitespace-pre-wrap">{{ $message->message }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Received</label>
                                            <p class="text-gray-900">{{ $message->created_at->format('M j, Y g:i A') }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                                            <p class="text-gray-900">{{ $message->ip_address }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-end p-6 border-t border-gray-200 bg-gray-50">
                                    <a href="mailto:{{ $message->email }}?subject=Re: Your message to Kenswed College"
                                       class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-red-800 transition-colors mr-3">
                                        <i class="fas fa-reply mr-2"></i>
                                        Reply
                                    </a>
                                    <button type="button"
                                            onclick="closeMessageModal('{{ $message->id }}')"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-comments text-4xl text-gray-400 mb-3"></i>
                                    <p class="text-gray-500 text-lg">No messages found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Action Forms -->
<form id="seenForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="is_seen" id="isSeenInput">
</form>

<form id="actionForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="action" id="actionInput">
</form>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.classList.toggle('hidden');
}

function openMessageModal(messageId) {
    const modal = document.getElementById(`messageModal${messageId}`);
    modal.classList.remove('hidden');
    markAsSeen(messageId, true);
}

function closeMessageModal(messageId) {
    const modal = document.getElementById(`messageModal${messageId}`);
    modal.classList.add('hidden');
}

function markAsSeen(messageId, isSeen) {
    const form = document.getElementById('seenForm');
    form.action = `/admin/messages/${messageId}`;
    document.getElementById('isSeenInput').value = isSeen ? 1 : 0;
    form.submit();
}

function updateAction(messageId, action) {
    const form = document.getElementById('actionForm');
    form.action = `/admin/messages/${messageId}`;
    document.getElementById('actionInput').value = action;
    form.submit();
}

function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/messages/${messageId}`;
        form.submit();
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.matches('.relative button')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.matches('.fixed.inset-0')) {
        document.querySelectorAll('[id^="messageModal"]').forEach(modal => {
            modal.classList.add('hidden');
        });
    }
});
</script>

<style>
.bg-success { background-color: #10b981; }
.bg-warning { background-color: #f59e0b; }
.bg-danger { background-color: #ef4444; }
.bg-light { background-color: #f8f9fa; }
</style>
@endsection
