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
                <span>Increase from previous period</span>
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
                    <input type="text" id="searchInput" placeholder="Search users..."
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
        <table class="min-w-full divide-y divide-gray-200" id="usersTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-10 px-4 py-3 text-left">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profile</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="checkbox" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary" value="{{ $user->id }}">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-sm font-mono text-gray-600">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <img src="{{ $user->profile_picture ? asset('storage/'.$user->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=B91C1C&color=fff' }}"
                                 class="w-8 h-8 rounded-full object-cover border border-gray-200">
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div>
                            <p class="font-medium text-gray-800">{{ $user->name }}</p>
                            @if($user->bio)
                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($user->bio, 50) }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div>
                            <a href="mailto:{{ $user->email }}" class="text-primary hover:text-primary-dark text-sm">
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
                    <td class="px-4 py-3 whitespace-nowrap">
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
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $roleNames[$user->role] ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($user->is_approved)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>
                            Pending
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-600">
                            {{ $user->created_at->format('M j, Y') }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $user->created_at->format('g:i A') }}
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center space-x-1">
                            <button onclick="viewUser({{ $user->id }})"
                                    class="p-1.5 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors"
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editUser({{ $user->id }})"
                                    class="p-1.5 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors"
                                    title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $user->id }}')"
                                        class="p-1.5 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
                                        title="More Actions">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $user->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if(!$user->is_approved)
                                        <button onclick="approveUser({{ $user->id }})"
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

    <!-- Table Footer with Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $users->firstItem() ?? 0 }}</span> to
                <span class="font-medium">{{ $users->lastItem() ?? 0 }}</span> of
                <span class="font-medium">{{ $users->total() ?? 0 }}</span> users
            </div>
            <div class="flex items-center space-x-2">
                @if($users->onFirstPage())
                    <span class="px-3 py-1 border border-gray-300 rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif
                <span class="text-sm text-gray-600">
                    Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                </span>
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="px-3 py-1 border border-gray-300 rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                @endif
            </div>
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

<!-- Modals (Add User, View User, Edit User, Delete, Approve Role) -->
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
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('viewUserModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4" id="viewUserContent">
                <div class="flex justify-center items-center py-12">
                    <div class="loading-spinner"></div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button onclick="closeModal('viewUserModal')"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('editUserModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4" id="editUserContent">
                <div class="flex justify-center items-center py-12">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>
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
                        <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
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

<!-- Approve User Role Selection Modal -->
<div id="approveRoleModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeApproveRoleModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full modal-content">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Assign User Role</h3>
                    <button onclick="closeApproveRoleModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg mb-4">
                        <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                        <p class="text-sm text-blue-700">Role assignment determines dashboard access and system permissions</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Role <span class="text-red-500">*</span></label>
                        <select id="approveRoleSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select a role</option>
                            <option value="1">Main School</option>
                            <option value="2">Administrator</option>
                            <option value="3">Scholarship</option>
                            <option value="4">Library</option>
                            <option value="5">Student</option>
                            <option value="6">Cafeteria</option>
                            <option value="7">Finance</option>
                            <option value="8">Trainers</option>
                            <option value="9">Website</option>
                        </select>
                    </div>
                    <div id="roleDescription" class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg hidden">
                        <i class="fas fa-arrow-right text-primary mr-2"></i>
                        <span id="roleDescriptionText"></span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeApproveRoleModal()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="submitApprovalWithRole()"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    <i class="fas fa-check mr-2"></i>
                    Approve User
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let currentApprovingUserId = null;

    // Role descriptions
    const roleDescriptions = {
        1: 'Main School dashboard - Manage courses, students, certificates, and academic operations',
        2: 'Administrator dashboard - Full system access, user management, and settings',
        3: 'Scholarship dashboard - Manage scholarship applications and awards',
        4: 'Library dashboard - Manage books, ebooks, members, and library operations',
        5: 'Student dashboard - View courses, enrollments, fees, and academic progress',
        6: 'Cafeteria dashboard - Manage products, sales, inventory, and orders',
        7: 'Finance dashboard - Manage payments, transactions, and financial reports',
        8: 'Trainers dashboard - Manage training programs and participant registrations',
        9: 'Website dashboard - Manage content, pages, blogs, and website settings'
    };

    // Modal Functions
    function openModal(modalId, size = 'md') {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Approve User Functions
    function approveUser(userId) {
        currentApprovingUserId = userId;
        const modal = document.getElementById('approveRoleModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            const roleSelect = document.getElementById('approveRoleSelect');
            const roleDescription = document.getElementById('roleDescription');
            if (roleSelect) roleSelect.value = '';
            if (roleDescription) roleDescription.classList.add('hidden');
        }
    }

    function closeApproveRoleModal() {
        const modal = document.getElementById('approveRoleModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentApprovingUserId = null;
        }
    }

    // Role description change handler
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('approveRoleSelect');
        if (roleSelect) {
            roleSelect.addEventListener('change', function() {
                const roleDescription = document.getElementById('roleDescription');
                const roleDescriptionText = document.getElementById('roleDescriptionText');
                const selectedValue = this.value;

                if (selectedValue && roleDescriptions[selectedValue]) {
                    roleDescriptionText.textContent = roleDescriptions[selectedValue];
                    roleDescription.classList.remove('hidden');
                } else {
                    roleDescription.classList.add('hidden');
                }
            });
        }
    });

    function submitApprovalWithRole() {
        const roleSelect = document.getElementById('approveRoleSelect');
        const selectedRole = roleSelect.value;

        if (!selectedRole) {
            showToast('Please select a role for this user', 'warning');
            roleSelect.focus();
            return;
        }

        if (currentApprovingUserId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${currentApprovingUserId}/approve`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="role" value="${selectedRole}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // View User
    async function viewUser(userId) {
        openModal('viewUserModal', 'lg');
        try {
            const response = await fetch(`/admin/users/${userId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (response.ok) {
                const user = await response.json();
                displayUserInModal(user);
            } else {
                throw new Error('Failed to load user data');
            }
        } catch (error) {
            document.getElementById('viewUserContent').innerHTML = `<div class="text-center py-8 text-red-500">Error loading user data</div>`;
        }
    }

    function displayUserInModal(user) {
        const roleNames = { 1: 'Main School', 2: 'Admin', 3: 'Scholarship', 4: 'Library', 5: 'Student', 6: 'Cafeteria', 7: 'Finance', 8: 'Trainers', 9: 'Website' };
        const html = `
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">User Details</h3>
                <button onclick="closeModal('viewUserModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=B91C1C&color=fff&size=100" class="w-20 h-20 rounded-full">
                    <div>
                        <h4 class="text-xl font-bold">${escapeHtml(user.name)}</h4>
                        <p class="text-gray-600">${escapeHtml(user.email)}</p>
                        <p class="text-gray-600">${user.phone_number || 'No phone number'}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="font-semibold">Role:</label> ${roleNames[user.role] || 'Unknown'}</div>
                    <div><label class="font-semibold">Status:</label> ${user.is_approved ? 'Active' : 'Pending'}</div>
                    <div><label class="font-semibold">Email Verified:</label> ${user.email_verified_at ? 'Yes' : 'No'}</div>
                    <div><label class="font-semibold">Member Since:</label> ${new Date(user.created_at).toLocaleDateString()}</div>
                </div>
                ${user.bio ? `<div><label class="font-semibold">Bio:</label><p>${escapeHtml(user.bio)}</p></div>` : ''}
            </div>
        `;
        document.getElementById('viewUserContent').innerHTML = html;
    }

    // Edit User
    async function editUser(userId) {
        openModal('editUserModal', 'md');
        try {
            const response = await fetch(`/admin/users/${userId}/edit`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (response.ok) {
                const user = await response.json();
                displayEditForm(user);
            } else {
                throw new Error('Failed to load user data');
            }
        } catch (error) {
            document.getElementById('editUserContent').innerHTML = `<div class="text-center py-8 text-red-500">Error loading edit form</div>`;
        }
    }

    function displayEditForm(user) {
        const roleNames = { 1: 'Main School', 2: 'Admin', 3: 'Scholarship', 4: 'Library', 5: 'Student', 6: 'Cafeteria', 7: 'Finance', 8: 'Trainers', 9: 'Website' };
        const html = `
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Edit User</h3>
                <button onclick="closeModal('editUserModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form id="editUserForm" method="POST" action="/admin/users/${user.id}">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="PUT">
                <div class="space-y-4">
                    <div><label class="block font-medium mb-1">Full Name</label><input type="text" name="name" value="${escapeHtml(user.name)}" class="w-full border rounded-lg px-3 py-2" required></div>
                    <div><label class="block font-medium mb-1">Email</label><input type="email" name="email" value="${escapeHtml(user.email)}" class="w-full border rounded-lg px-3 py-2" required></div>
                    <div><label class="block font-medium mb-1">Phone Number</label><input type="text" name="phone_number" value="${user.phone_number || ''}" class="w-full border rounded-lg px-3 py-2"></div>
                    <div><label class="block font-medium mb-1">Role</label>
                        <select name="role" class="w-full border rounded-lg px-3 py-2">
                            ${Object.entries(roleNames).map(([val, label]) => `<option value="${val}" ${user.role == val ? 'selected' : ''}>${label}</option>`).join('')}
                        </select>
                    </div>
                    <div><label class="block font-medium mb-1">Bio</label><textarea name="bio" rows="3" class="w-full border rounded-lg px-3 py-2">${user.bio || ''}</textarea></div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editUserModal')" class="px-4 py-2 border rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg">Update User</button>
                </div>
            </form>
        `;
        document.getElementById('editUserContent').innerHTML = html;

        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(this.action, { method: 'POST', body: formData })
                .then(response => {
                    if (response.ok) {
                        showToast('User updated successfully', 'success');
                        closeModal('editUserModal');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error updating user', 'error');
                    }
                });
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Table Functions
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
        const selectAll = document.getElementById('selectAll');
        if (selectAll) selectAll.checked = false;
        updateBulkActions();
    }

    function toggleActionMenu(userId) {
        const menu = document.getElementById(`actionMenu-${userId}`);
        document.querySelectorAll('[id^="actionMenu-"]').forEach(m => { if (m.id !== `actionMenu-${userId}`) m.classList.add('hidden'); });
        if (menu) menu.classList.toggle('hidden');
    }

    function toggleStatus(userId, newStatus) {
        const action = newStatus == 1 ? 'activate' : 'deactivate';
        if (confirm(`Are you sure you want to ${action} this user?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}/status`;
            form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="PUT"><input type="hidden" name="is_approved" value="${newStatus}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    let currentDeleteUserId = null;
    function deleteUser(userId) {
        currentDeleteUserId = userId;
        document.getElementById('deleteForm').action = `/admin/users/${userId}`;
        document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete this user? This action cannot be undone.';
        openModal('deleteModal', 'md');
    }

    function confirmDelete() {
        if (currentDeleteUserId) document.getElementById('deleteForm').submit();
    }

    function applyBulkAction() {
        const action = document.getElementById('bulkAction').value;
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (!action) { showToast('Please select an action', 'warning'); return; }
        if (selectedIds.length === 0) { showToast('Please select at least one user', 'warning'); return; }
        if (confirm(`Are you sure you want to ${action} ${selectedIds.length} user(s)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/users/bulk-actions';
            form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="action" value="${action}">${selectedIds.map(id => `<input type="hidden" name="user_ids[]" value="${id}">`).join('')}`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function submitForm(formId, loadingText) {
        const form = document.getElementById(formId);
        const submitBtn = event.target;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`;
        form.submit();
    }

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;
        const toast = document.createElement('div');
        const colors = { success: 'bg-green-50 border-green-500 text-green-600', error: 'bg-red-50 border-red-500 text-red-600', warning: 'bg-yellow-50 border-yellow-500 text-yellow-600', info: 'bg-blue-50 border-blue-500 text-blue-600' };
        toast.className = `p-3 mb-2 rounded-lg border-l-4 ${colors[type] || colors.info} shadow-lg`;
        toast.innerHTML = `<div class="flex items-center"><i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i><span>${message}</span></div>`;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    function exportToExcel() { showToast('Export functionality coming soon', 'info'); }
    function refreshTable() { location.reload(); }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
                updateBulkActions();
            });
        }
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.addEventListener('change', updateBulkActions));

        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const term = this.value.toLowerCase();
                document.querySelectorAll('#usersTable tbody tr').forEach(row => {
                    const name = row.querySelector('td:nth-child(4) .font-medium')?.textContent.toLowerCase() || '';
                    const email = row.querySelector('td:nth-child(5) a')?.textContent.toLowerCase() || '';
                    row.style.display = (name.includes(term) || email.includes(term)) ? '' : 'none';
                });
            });
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
            document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
        }
    });
</script>

<style>
    .loading-spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #B91C1C;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .modal-overlay {
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
</style>
@endsection
