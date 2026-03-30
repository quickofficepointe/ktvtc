@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'User Management')
@section('breadcrumb')
    <li class="inline-flex items-center">
        <a href="{{ route('super-admin.dashboard') }}" class="text-super hover:text-super-dark">Dashboard</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-500">Users</span>
    </li>
@endsection

@section('header-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('super-admin.users.create') }}" class="px-4 py-2 bg-super text-white rounded-lg hover:bg-super-dark font-medium">
            <i class="fas fa-user-plus mr-2"></i> Create User
        </a>
        <a href="{{ route('super-admin.users.export') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
            <i class="fas fa-file-export mr-2"></i> Export
        </a>
    </div>
@endsection

@section('content')
    <!-- Filters Card -->
    <div class="super-card mb-6">
        <form method="GET" action="{{ route('super-admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Name, email, phone..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
            </div>

            <!-- Role Filter -->
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" id="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
    <option value="">All Roles</option>
    @foreach($roles as $role)
        <option value="{{ $role->id }}" {{ request('role') == (string)$role->id ? 'selected' : '' }}>
            {{ $role->name }}
        </option>
    @endforeach
</select>
            </div>

            <!-- Campus Filter -->
            <div>
                <label for="campus_id" class="block text-sm font-medium text-gray-700 mb-1">Campus</label>
                <select name="campus_id" id="campus_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
                    <option value="">All Campuses</option>
                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                            {{ $campus->name }} ({{ $campus->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="md:col-span-4 flex items-center justify-between pt-4 border-t border-gray-200">
                <button type="submit" class="px-4 py-2 bg-super text-white rounded-lg hover:bg-super-dark font-medium">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
                <a href="{{ route('super-admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="super-card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">All Users ({{ $users->total() }})</h2>
            <div class="text-sm text-gray-500">
                Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}
            </div>
        </div>

        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Campus
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Created
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50 {{ $user->trashed() ? 'bg-red-50' : '' }}">
                                <!-- User Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $user->name }}
                                                @if($user->trashed())
                                                    <span class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Deleted</span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            <div class="text-xs text-gray-400">{{ $user->phone_number ?? 'No phone' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="px-2 py-1 text-xs rounded-full font-medium {{ $user->role_badge }}">
                                            {{ $user->role_name }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Campus -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($user->campus)
                                        <div class="font-medium">{{ $user->campus->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $user->campus->code }}</div>
                                    @else
                                        <span class="text-gray-400 italic">Unassigned</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span class="px-2 py-1 text-xs rounded-full font-medium
                                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span class="px-2 py-1 text-xs rounded-full font-medium
                                            {{ $user->is_approved ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $user->is_approved ? 'Approved' : 'Pending' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Created -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $user->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('super-admin.users.show', $user) }}"
                                           class="text-blue-600 hover:text-blue-900" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('super-admin.users.edit', $user) }}"
                                           class="text-green-600 hover:text-green-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if(!$user->trashed())
                                            @if($user->id !== auth()->id())
                                                <!-- Toggle Active Status -->
                                                <button onclick="toggleUserStatus({{ $user->id }}, {{ $user->is_active ? 'false' : 'true' }})"
                                                        class="text-{{ $user->is_active ? 'red' : 'green' }}-600 hover:text-{{ $user->is_active ? 'red' : 'green' }}-900"
                                                        title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check-circle' }}"></i>
                                                </button>

                                                <!-- Toggle Approval -->
                                                <button onclick="toggleApproval({{ $user->id }}, {{ $user->is_approved ? 'false' : 'true' }})"
                                                        class="text-{{ $user->is_approved ? 'yellow' : 'blue' }}-600 hover:text-{{ $user->is_approved ? 'yellow' : 'blue' }}-900"
                                                        title="{{ $user->is_approved ? 'Unapprove' : 'Approve' }}">
                                                    <i class="fas fa-{{ $user->is_approved ? 'user-times' : 'user-check' }}"></i>
                                                </button>

                                                <!-- Reset Password -->
                                                <button onclick="resetPassword({{ $user->id }})"
                                                        class="text-purple-600 hover:text-purple-900" title="Reset Password">
                                                    <i class="fas fa-key"></i>
                                                </button>

                                                <!-- Impersonate -->
                                                @if($user->role !== 0) <!-- Don't allow impersonating super admin -->
                                                    <button onclick="impersonateUser({{ $user->id }})"
                                                            class="text-orange-600 hover:text-orange-900" title="Impersonate">
                                                        <i class="fas fa-user-secret"></i>
                                                    </button>
                                                @endif

                                                <!-- Delete -->
                                                <form action="{{ route('super-admin.users.destroy', $user) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            onclick="return confirm('Are you sure you want to delete this user?')"
                                                            class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <!-- Restore Deleted User -->
                                            <form action="{{ route('super-admin.users.restore', $user) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        onclick="return confirm('Restore this user?')"
                                                        class="text-green-600 hover:text-green-900" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                <p class="text-gray-500 mb-6">Try adjusting your filters or create a new user.</p>
                <a href="{{ route('super-admin.users.create') }}" class="px-4 py-2 bg-super text-white rounded-lg hover:bg-super-dark font-medium">
                    <i class="fas fa-user-plus mr-2"></i> Create First User
                </a>
            </div>
        @endif
    </div>

    <!-- Bulk Actions -->
    <div class="mt-6 super-card">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Bulk Actions</h3>
            <div class="text-sm text-gray-500">Select users to perform bulk actions</div>
        </div>
        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
            <button onclick="bulkAction('activate')" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 font-medium">
                <i class="fas fa-check-circle mr-2"></i> Activate
            </button>
            <button onclick="bulkAction('deactivate')" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 font-medium">
                <i class="fas fa-ban mr-2"></i> Deactivate
            </button>
            <button onclick="bulkAction('approve')" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 font-medium">
                <i class="fas fa-user-check mr-2"></i> Approve
            </button>
            <button onclick="bulkAction('delete')" class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 font-medium">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // AJAX Functions
    function toggleUserStatus(userId, newStatus) {
        if(confirm(newStatus ? 'Activate this user?' : 'Deactivate this user?')) {
            $.ajax({
                url: '/super-admin/users/' + userId + '/toggle-status',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.error || 'Error updating status', 'error');
                }
            });
        }
    }

    function toggleApproval(userId, newApproval) {
        if(confirm(newApproval ? 'Approve this user?' : 'Unapprove this user?')) {
            $.ajax({
                url: '/super-admin/users/' + userId + '/toggle-approval',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            });
        }
    }

    function resetPassword(userId) {
        if(confirm('Reset password for this user?')) {
            $.ajax({
                url: '/super-admin/users/' + userId + '/reset-password',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast('Password reset successfully. New password: ' + response.new_password, 'success');
                }
            });
        }
    }

    function impersonateUser(userId) {
        if(confirm('Impersonate this user? You will be logged in as them.')) {
            $.ajax({
                url: '/super-admin/users/' + userId + '/impersonate',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    window.location.href = '{{ route("home") }}';
                }
            });
        }
    }

    function bulkAction(action) {
        // Get selected user IDs from checkboxes
        const selectedIds = [];
        document.querySelectorAll('.user-checkbox:checked').forEach(checkbox => {
            selectedIds.push(checkbox.value);
        });

        if(selectedIds.length === 0) {
            showToast('Please select users first', 'warning');
            return;
        }

        if(confirm(`Perform ${action} on ${selectedIds.length} user(s)?`)) {
            $.ajax({
                url: '/super-admin/users/bulk-actions',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    action: action,
                    user_ids: selectedIds
                },
                success: function(response) {
                    showToast(response.success, 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            });
        }
    }

    // Add checkboxes to table rows
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.querySelector('tbody');
        if(tableBody) {
            tableBody.querySelectorAll('tr').forEach(row => {
                const firstCell = row.querySelector('td:first-child');
                if(firstCell) {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'user-checkbox mr-2';
                    checkbox.value = row.dataset.userId || '';
                    firstCell.prepend(checkbox);
                }
            });
        }
    });
</script>
@endsection
