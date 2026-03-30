@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'User Management')
@section('subtitle', 'Manage all registered users and their roles')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Management</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">User Management</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="exportToExcel()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export Excel</span>
    </button>
    <button onclick="openModal('addUserModal', 'md')" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Add User</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Users</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $totalUsers ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-users text-primary text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-arrow-up text-success mr-1"></i>
                <span>+12% from last month</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Active Users</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $activeUsers ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-user-check text-success text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-check-circle text-success mr-1"></i>
                <span>Approved and active</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Approval</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $pendingUsers ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center">
                <i class="fas fa-user-clock text-warning text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-clock text-warning mr-1"></i>
                <span>Awaiting approval</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Administrators</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ $adminUsers ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-user-shield text-info text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex items-center text-sm text-gray-500">
                <i class="fas fa-shield-alt text-info mr-1"></i>
                <span>System administrators</span>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Registered Users</h3>
                <p class="text-sm text-gray-600 mt-1">Manage all user accounts and permissions</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="relative">
                    <input type="text" placeholder="Search users..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent w-64">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                <button onclick="refreshTable()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="usersTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="py-3 px-6 text-left">
                        <label class="flex items-center">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary">
                        </label>
                    </th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profile</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                    <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-6">
                        <input type="checkbox" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary" value="{{ $user->id }}">
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-sm font-mono text-gray-600">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center">
                            <img src="{{ $user->profile_picture ? asset('storage/'.$user->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=B91C1C&color=fff' }}"
                                 class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <p class="font-medium text-gray-800">{{ $user->name }}</p>
                            @if($user->bio)
                            <p class="text-xs text-gray-500 mt-1 truncate max-w-xs">{{ Str::limit($user->bio, 40) }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div>
                            <a href="mailto:{{ $user->email }}" class="text-primary hover:text-primary-dark font-medium">
                                {{ $user->email }}
                            </a>
                            <div class="flex items-center mt-1">
                                @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1 text-xs"></i>
                                    Verified
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1 text-xs"></i>
                                    Unverified
                                </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $roleColors = [
                                1 => 'bg-purple-100 text-purple-800',
                                2 => 'bg-red-100 text-red-800',
                                3 => 'bg-green-100 text-green-800',
                                4 => 'bg-blue-100 text-blue-800',
                                5 => 'bg-indigo-100 text-indigo-800',
                                6 => 'bg-pink-100 text-pink-800',
                                7 => 'bg-yellow-100 text-yellow-800',
                                8 => 'bg-gray-100 text-gray-800',
                                9 => 'bg-teal-100 text-teal-800'
                            ];
                            $roleNames = [
                                1 => 'Main School',
                                2 => 'Admin',
                                3 => 'Scholarship',
                                4 => 'Library',
                                5 => 'Student',
                                6 => 'Cafeteria',
                                7 => 'Finance',
                                8 => 'Trainers',
                                9 => 'Website'
                            ];
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $roleNames[$user->role] ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        @if($user->is_approved)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>
                            Pending
                        </span>
                        @endif
                    </td>
                    <td class="py-3 px-6">
                        <div class="text-sm text-gray-600">
                            {{ $user->created_at->format('M j, Y') }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $user->created_at->format('g:i A') }}
                        </div>
                    </td>
                    <td class="py-3 px-6">
                        <div class="flex items-center space-x-2">
                            <button onclick="viewUser('{{ $user->id }}')"
                                    class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editUser('{{ $user->id }}')"
                                    class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    data-tooltip="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $user->id }}')"
                                        class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $user->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if(!$user->is_approved)
                                        <button onclick="approveUser('{{ $user->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check mr-2"></i>
                                            Approve User
                                        </button>
                                        @endif
                                        <button onclick="toggleStatus('{{ $user->id }}', '{{ $user->is_approved ? 0 : 1 }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-{{ $user->is_approved ? 'yellow' : 'green' }}-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-power-off mr-2"></i>
                                            {{ $user->is_approved ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <div class="border-t border-gray-200 my-1"></div>
                                        <button onclick="deleteUser('{{ $user->id }}')"
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i>
                                            Delete User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Table Footer -->
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    Showing <span class="font-medium">{{ $users->firstItem() }}</span> to
                    <span class="font-medium">{{ $users->lastItem() }}</span> of
                    <span class="font-medium">{{ $users->total() }}</span> users
                @else
                    Showing <span class="font-medium">1</span> to
                    <span class="font-medium">{{ count($users) }}</span> of
                    <span class="font-medium">{{ count($users) }}</span> users
                @endif
            </div>
            @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="flex items-center space-x-2">
                <button onclick="prevPage()"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 {{ $users->currentPage() == 1 ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="text-sm text-gray-600">
                    Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                </span>
                <button onclick="nextPage()"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 {{ $users->currentPage() == $users->lastPage() ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Actions -->
<div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-white rounded-lg shadow-lg border border-gray-200 p-4 hidden" id="bulkActions">
    <div class="flex items-center space-x-4">
        <span class="text-sm font-medium text-gray-700" id="selectedCount">0 users selected</span>
        <div class="flex items-center space-x-2">
            <select id="bulkAction" class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                <option value="">Select Action</option>
                <option value="approve">Approve Selected</option>
                <option value="activate">Activate Selected</option>
                <option value="deactivate">Deactivate Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button onclick="applyBulkAction()" class="px-4 py-1 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium">
                Apply
            </button>
            <button onclick="clearSelection()" class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 text-sm">
                Clear
            </button>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('addUserModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Add New User</h3>
                    <button onclick="closeModal('addUserModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="addUserForm" method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="name" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" name="email" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="text" name="phone_number"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                            <select name="role" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="1">Main School</option>
                                <option value="2">Admin</option>
                                <option value="3">Scholarship</option>
                                <option value="4">Library</option>
                                <option value="5">Student</option>
                                <option value="6">Cafeteria</option>
                                <option value="7">Finance</option>
                                <option value="8">Trainers</option>
                                <option value="9">Website</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                            <textarea name="bio" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('addUserModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitForm('addUserForm', 'Creating User...')"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    Create User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <!-- Modal content will be loaded via AJAX -->
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <!-- Modal content will be loaded via AJAX -->
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Confirm Delete</h3>
                        <p class="text-sm text-gray-600 mt-1">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-gray-700" id="deleteMessage"></p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
                <button onclick="confirmDelete()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                    Delete User
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize table functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Row selection
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');

        selectAll?.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });
    });

    function updateBulkActions() {
        const selected = document.querySelectorAll('.row-checkbox:checked').length;
        const bulkActions = document.getElementById('bulkActions');

        if (selected > 0) {
            document.getElementById('selectedCount').textContent = `${selected} user${selected > 1 ? 's' : ''} selected`;
            bulkActions.classList.remove('hidden');
        } else {
            bulkActions.classList.add('hidden');
        }
    }

    function clearSelection() {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    }

    function toggleActionMenu(userId) {
        const menu = document.getElementById(`actionMenu-${userId}`);
        const allMenus = document.querySelectorAll('[id^="actionMenu-"]');

        allMenus.forEach(m => {
            if (m.id !== `actionMenu-${userId}`) {
                m.classList.add('hidden');
            }
        });

        menu.classList.toggle('hidden');
    }

    // Close action menus when clicking elsewhere
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });

    function viewUser(userId) {
        // Load user details via AJAX
        fetch(`/admin/users/${userId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('viewUserModal').innerHTML = html;
                openModal('viewUserModal', 'lg');
            });
    }

    function editUser(userId) {
        // Load edit form via AJAX
        fetch(`/admin/users/${userId}/edit`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('editUserModal').innerHTML = html;
                openModal('editUserModal', 'md');
            });
    }

    function approveUser(userId) {
        if (confirm('Are you sure you want to approve this user?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}/approve`;
            form.innerHTML = `
                @csrf
                @method('PUT')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function toggleStatus(userId, newStatus) {
        const action = newStatus == 1 ? 'activate' : 'deactivate';
        if (confirm(`Are you sure you want to ${action} this user?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}/status`;
            form.innerHTML = `
                @csrf
                @method('PUT')
                <input type="hidden" name="is_approved" value="${newStatus}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteUser(userId) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/users/${userId}`;
        document.getElementById('deleteMessage').textContent =
            'Are you sure you want to delete this user? This action cannot be undone.';
        openModal('deleteModal', 'md');
    }

    function confirmDelete() {
        document.getElementById('deleteForm').submit();
    }

    function applyBulkAction() {
        const action = document.getElementById('bulkAction').value;
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .map(cb => cb.value);

        if (!action) {
            showToast('Please select an action', 'warning');
            return;
        }

        if (selectedIds.length === 0) {
            showToast('Please select at least one user', 'warning');
            return;
        }

        const actions = {
            'approve': 'approve',
            'activate': 'activate',
            'deactivate': 'deactivate',
            'delete': 'delete'
        };

        const actionText = {
            'approve': 'approve',
            'activate': 'activate',
            'deactivate': 'deactivate',
            'delete': 'delete'
        };

        if (confirm(`Are you sure you want to ${actionText[action]} ${selectedIds.length} user(s)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/users/bulk-action';
            form.innerHTML = `
                @csrf
                @method('POST')
                <input type="hidden" name="action" value="${action}">
                ${selectedIds.map(id => `<input type="hidden" name="user_ids[]" value="${id}">`).join('')}
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function exportToExcel() {
        showToast('Export functionality will be implemented soon', 'info');
    }

    function refreshTable() {
        location.reload();
    }

    function prevPage() {
        @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->currentPage() > 1)
            window.location.href = '{{ $users->previousPageUrl() }}';
        @endif
    }

    function nextPage() {
        @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasMorePages())
            window.location.href = '{{ $users->nextPageUrl() }}';
        @endif
    }
</script>

<style>
    #usersTable {
        min-width: 1200px;
    }

    @media (max-width: 768px) {
        #usersTable {
            min-width: 100%;
        }
    }

    .row-checkbox:checked {
        background-color: #B91C1C;
        border-color: #B91C1C;
    }

    #bulkActions {
        z-index: 40;
    }
</style>
@endsection
