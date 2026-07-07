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
    </div>
</div>

<!-- Search and Filters -->
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone, ID..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Role</label>
            <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Roles</option>
                @foreach($roleNames ?? [] as $key => $name)
                    <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Per Page</label>
            <select name="per_page" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" onchange="this.form.submit()">
                <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                <option value="200" {{ request('per_page', 20) == 200 ? 'selected' : '' }}>200</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                <i class="fas fa-search mr-2"></i> Filter
            </button>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Registered Users</h3>
                <p class="text-sm text-gray-600 mt-1">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() ?? 0 }} users
                </p>
            </div>
            <button onclick="refreshTable()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
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
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <input type="checkbox" class="row-checkbox rounded border-gray-300 text-primary focus:ring-primary" value="{{ $user->id }}">
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-sm font-mono text-gray-600">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <img src="{{ $user->profile_picture ? asset('storage/'.$user->profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=B91C1C&color=fff' }}" class="w-8 h-8 rounded-full object-cover border border-gray-200">
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
                            <a href="mailto:{{ $user->email }}" class="text-primary hover:text-primary-dark text-sm">{{ $user->email }}</a>
                            <div class="flex items-center mt-1">
                                @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1 text-xs"></i> Verified
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1 text-xs"></i> Unverified
                                </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @php
                            $roleColors = [1 => 'bg-purple-100 text-purple-800', 2 => 'bg-red-100 text-red-800', 3 => 'bg-green-100 text-green-800', 4 => 'bg-blue-100 text-blue-800', 5 => 'bg-indigo-100 text-indigo-800', 6 => 'bg-pink-100 text-pink-800', 7 => 'bg-yellow-100 text-yellow-800', 8 => 'bg-gray-100 text-gray-800', 9 => 'bg-teal-100 text-teal-800'];
                            $roleNames = [1 => 'Main School', 2 => 'Admin', 3 => 'Scholarship', 4 => 'Library', 5 => 'Student', 6 => 'Cafeteria', 7 => 'Finance', 8 => 'Trainers', 9 => 'Website'];
                        @endphp
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $roleNames[$user->role] ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($user->is_approved)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> Active
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i> Pending
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-600">{{ $user->created_at->format('M j, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $user->created_at->format('g:i A') }}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center space-x-1">
                            <button onclick="viewUser({{ $user->id }})" class="p-1.5 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition-colors" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editUser({{ $user->id }})" class="p-1.5 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <div class="relative">
                                <button onclick="toggleActionMenu('{{ $user->id }}')" class="p-1.5 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors" title="More Actions">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="actionMenu-{{ $user->id }}" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-10">
                                    <div class="py-1">
                                        @if(!$user->is_approved)
                                        <button onclick="approveUser({{ $user->id }})" class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-check mr-2"></i> Approve User
                                        </button>
                                        @endif
                                        <button onclick="toggleStatus('{{ $user->id }}', '{{ $user->is_approved ? 0 : 1 }}')" class="w-full text-left px-4 py-2 text-sm text-{{ $user->is_approved ? 'yellow' : 'green' }}-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-power-off mr-2"></i> {{ $user->is_approved ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <div class="border-t border-gray-200 my-1"></div>
                                        <button onclick="openResetPasswordModal('{{ $user->id }}', '{{ $user->name }}', '{{ $user->phone_number }}')" class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-key mr-2"></i> Reset Password
                                        </button>
                                        @if($user->phone_number)
                                        <button onclick="resetPasswordWithSms('{{ $user->id }}', '{{ $user->name }}', '{{ $user->phone_number }}')" class="w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-sms mr-2"></i> Reset & Send SMS
                                        </button>
                                        @endif
                                        <div class="border-t border-gray-200 my-1"></div>
                                        <button onclick="deleteUser('{{ $user->id }}')" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                            <i class="fas fa-trash mr-2"></i> Delete User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $users->firstItem() ?? 0 }}</span> to
                <span class="font-medium">{{ $users->lastItem() ?? 0 }}</span> of
                <span class="font-medium">{{ $users->total() ?? 0 }}</span> users
            </div>
            <div>{{ $users->appends(request()->query())->links() }}</div>
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
                <option value="reset_password">Reset Passwords</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button onclick="applyBulkAction()" class="px-4 py-1 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium">Apply</button>
            <button onclick="clearSelection()" class="px-3 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 text-sm">Clear</button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ALL MODALS -->
<!-- ============================================================ -->

<!-- Add User Modal -->
<div id="addUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 modal-overlay" onclick="closeModal('addUserModal')"></div>
        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Add New User</h3>
                <button onclick="closeModal('addUserModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div><label class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="text" name="phone_number" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary"></div>
                    <div><label class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="">Select Role</option>
                            @foreach($roleNames ?? [] as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">Bio</label>
                        <textarea name="bio" rows="3" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 modal-overlay" onclick="closeModal('viewUserModal')"></div>
        <div class="relative bg-white rounded-xl max-w-2xl w-full p-6 shadow-2xl">
            <div id="viewUserContent">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">User Details</h3>
                    <button onclick="closeModal('viewUserModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="flex justify-center py-8">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 modal-overlay" onclick="closeModal('editUserModal')"></div>
        <div class="relative bg-white rounded-xl max-w-lg w-full p-6 shadow-2xl">
            <div id="editUserContent">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Edit User</h3>
                    <button onclick="closeModal('editUserModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="flex justify-center py-8">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Role Modal -->
<div id="approveRoleModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 modal-overlay" onclick="closeApproveRoleModal()"></div>
        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Approve User</h3>
                <button onclick="closeApproveRoleModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <p class="text-sm text-gray-600">Select a role for this user:</p>
                <select id="approveRoleSelect" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                    <option value="">Select Role</option>
                    @foreach($roleNames ?? [] as $key => $name)
                        <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
                <div id="roleDescription" class="hidden bg-blue-50 p-3 rounded-lg text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span id="roleDescriptionText"></span>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeApproveRoleModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button onclick="submitApprovalWithRole()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 modal-overlay" onclick="closeModal('resetPasswordModal')"></div>
        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Reset Password</h3>
                <button onclick="closeModal('resetPasswordModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <p class="text-sm text-gray-600">Resetting password for: <span id="resetUserDisplay" class="font-bold text-gray-800"></span></p>
                <p class="text-sm text-gray-500" id="resetUserPhone"></p>
                <div>
                    <label class="block text-sm font-medium text-gray-700">New Password</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="resetNewPassword" class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary font-mono">
                        <button onclick="generateRandomPasswordField()" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm" title="Generate random password">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters</p>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="sendSmsCheckbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="sendSmsCheckbox" class="text-sm text-gray-700">Send password via SMS</label>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeModal('resetPasswordModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button onclick="submitResetPassword()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Reset Password</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 modal-overlay" onclick="closeModal('deleteModal')"></div>
        <div class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Delete User</h3>
                <button onclick="closeModal('deleteModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <p class="text-sm text-gray-600" id="deleteMessage">Are you sure you want to delete this user? This action cannot be undone.</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
            </form>
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeModal('deleteModal')" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- ============================================================ -->
<!-- SCRIPTS -->
<!-- ============================================================ -->

<script>
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let currentApprovingUserId = null;
    let currentResetUserId = null;

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

    // ============================================
    // MODAL FUNCTIONS
    // ============================================

    function openModal(modalId, size = 'md') {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const content = modal.querySelector('.relative');
        if (content) {
            content.className = `relative bg-white rounded-xl shadow-2xl p-6 w-full`;
            if (size === 'lg') content.classList.add('max-w-2xl');
            else if (size === 'xl') content.classList.add('max-w-4xl');
            else content.classList.add('max-w-md');
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
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

    // ============================================
    // PASSWORD RESET FUNCTIONS
    // ============================================

    function generateRandomPassword() {
        const length = 10;
        const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        let password = '';
        for (let i = 0; i < length; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        return password;
    }

    function generateRandomPasswordField() {
        const passwordField = document.getElementById('resetNewPassword');
        if (passwordField) {
            passwordField.value = generateRandomPassword();
        }
    }

    function openResetPasswordModal(userId, userName, userPhone) {
        currentResetUserId = userId;

        const userDisplay = document.getElementById('resetUserDisplay');
        const userPhoneDisplay = document.getElementById('resetUserPhone');
        const passwordField = document.getElementById('resetNewPassword');
        const smsCheckbox = document.getElementById('sendSmsCheckbox');

        if (userDisplay) userDisplay.textContent = userName;
        if (userPhoneDisplay) userPhoneDisplay.textContent = userPhone ? `Phone: ${userPhone}` : 'No phone number registered';
        if (passwordField) passwordField.value = generateRandomPassword();

        if (smsCheckbox) {
            if (userPhone) {
                smsCheckbox.disabled = false;
                smsCheckbox.checked = true;
            } else {
                smsCheckbox.disabled = true;
                smsCheckbox.checked = false;
            }
        }

        openModal('resetPasswordModal', 'md');
    }

    function submitResetPassword() {
        const passwordField = document.getElementById('resetNewPassword');
        const password = passwordField ? passwordField.value : '';

        if (password.length < 8) {
            showToast('Password must be at least 8 characters long.', 'warning');
            return;
        }

        const smsCheckbox = document.getElementById('sendSmsCheckbox');
        const sendSms = smsCheckbox ? smsCheckbox.checked : false;

        if (confirm(`Reset password for this user?${sendSms ? ' SMS will be sent.' : ''}`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${currentResetUserId}/reset-password`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="password" value="${password}">
                <input type="hidden" name="password_confirmation" value="${password}">
                <input type="hidden" name="send_sms" value="${sendSms ? '1' : '0'}">
            `;
            document.body.appendChild(form);
            closeModal('resetPasswordModal');
            form.submit();
        }
    }

    function resetPasswordWithSms(userId, userName, phoneNumber) {
        if (confirm(`Reset password for ${userName} and send SMS to ${phoneNumber}?`)) {
            const newPassword = generateRandomPassword();

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}/reset-password`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="password" value="${newPassword}">
                <input type="hidden" name="password_confirmation" value="${newPassword}">
                <input type="hidden" name="send_sms" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Bulk Reset Passwords
    function applyBulkResetPassword() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) {
            showToast('Please select at least one user', 'warning');
            return;
        }

        const sendSms = confirm(`Send SMS notifications to ${selectedIds.length} selected users?`);
        const newPassword = generateRandomPassword();

        if (confirm(`Reset passwords for ${selectedIds.length} selected users?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/users/bulk-reset-passwords';
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="password" value="${newPassword}">
                <input type="hidden" name="send_sms" value="${sendSms ? '1' : '0'}">
                ${selectedIds.map(id => `<input type="hidden" name="user_ids[]" value="${id}">`).join('')}
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // ============================================
    // APPROVE USER FUNCTIONS
    // ============================================

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

    function submitApprovalWithRole() {
        const roleSelect = document.getElementById('approveRoleSelect');
        const selectedRole = roleSelect ? roleSelect.value : null;

        if (!selectedRole) {
            showToast('Please select a role for this user', 'warning');
            if (roleSelect) roleSelect.focus();
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

    // ============================================
    // VIEW / EDIT USER FUNCTIONS
    // ============================================

    function viewUser(userId) {
        openModal('viewUserModal', 'lg');
        const content = document.getElementById('viewUserContent');
        if (!content) return;

        content.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">User Details</h3>
                <button onclick="closeModal('viewUserModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="flex justify-center py-8">
                <div class="loading-spinner"></div>
            </div>
        `;

        fetch(`/admin/users/${userId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load user data');
            return response.json();
        })
        .then(user => {
            displayUserInModal(user);
        })
        .catch(error => {
            if (content) {
                content.innerHTML = `<div class="text-center py-8 text-red-500">Error loading user data</div>`;
            }
        });
    }

    function displayUserInModal(user) {
        const roleNames = { 1: 'Main School', 2: 'Admin', 3: 'Scholarship', 4: 'Library', 5: 'Student', 6: 'Cafeteria', 7: 'Finance', 8: 'Trainers', 9: 'Website' };
        const content = document.getElementById('viewUserContent');
        if (!content) return;

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
        content.innerHTML = html;
    }

    function editUser(userId) {
        openModal('editUserModal', 'md');
        const content = document.getElementById('editUserContent');
        if (!content) return;

        content.innerHTML = `
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Edit User</h3>
                <button onclick="closeModal('editUserModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="flex justify-center py-8">
                <div class="loading-spinner"></div>
            </div>
        `;

        fetch(`/admin/users/${userId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load user data');
            return response.json();
        })
        .then(user => {
            displayEditForm(user);
        })
        .catch(error => {
            if (content) {
                content.innerHTML = `<div class="text-center py-8 text-red-500">Error loading edit form</div>`;
            }
        });
    }

    function displayEditForm(user) {
        const roleNames = { 1: 'Main School', 2: 'Admin', 3: 'Scholarship', 4: 'Library', 5: 'Student', 6: 'Cafeteria', 7: 'Finance', 8: 'Trainers', 9: 'Website' };
        const content = document.getElementById('editUserContent');
        if (!content) return;

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
        content.innerHTML = html;

        document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
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

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;

        const colors = {
            success: 'bg-green-50 border-green-500 text-green-600',
            error: 'bg-red-50 border-red-500 text-red-600',
            warning: 'bg-yellow-50 border-yellow-500 text-yellow-600',
            info: 'bg-blue-50 border-blue-500 text-blue-600'
        };

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.className = `p-3 mb-2 rounded-lg border-l-4 shadow-lg transition-all duration-300 ${colors[type] || colors.info}`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${icons[type] || icons.info} mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        toastContainer.appendChild(toast);
        setTimeout(() => {
            if (toast.parentElement) toast.remove();
        }, 5000);
    }

    function exportToExcel() {
        showToast('Export functionality coming soon', 'info');
    }

    function refreshTable() {
        location.reload();
    }

    // ============================================
    // TABLE FUNCTIONS
    // ============================================

    function updateBulkActions() {
        const selected = document.querySelectorAll('.row-checkbox:checked').length;
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        if (selected > 0) {
            if (selectedCount) selectedCount.textContent = `${selected} user${selected > 1 ? 's' : ''} selected`;
            if (bulkActions) bulkActions.classList.remove('hidden');
        } else {
            if (bulkActions) bulkActions.classList.add('hidden');
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
        document.querySelectorAll('[id^="actionMenu-"]').forEach(m => {
            if (m.id !== `actionMenu-${userId}`) m.classList.add('hidden');
        });
        if (menu) menu.classList.toggle('hidden');
    }

    function toggleStatus(userId, newStatus) {
        const action = newStatus == 1 ? 'activate' : 'deactivate';
        if (confirm(`Are you sure you want to ${action} this user?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}/status`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="is_approved" value="${newStatus}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    let currentDeleteUserId = null;

    function deleteUser(userId) {
        currentDeleteUserId = userId;
        const deleteForm = document.getElementById('deleteForm');
        const deleteMessage = document.getElementById('deleteMessage');

        if (deleteForm) deleteForm.action = `/admin/users/${userId}`;
        if (deleteMessage) deleteMessage.textContent = 'Are you sure you want to delete this user? This action cannot be undone.';

        openModal('deleteModal', 'md');
    }

    function confirmDelete() {
        const deleteForm = document.getElementById('deleteForm');
        if (deleteForm) deleteForm.submit();
    }

    function applyBulkAction() {
        const actionSelect = document.getElementById('bulkAction');
        const action = actionSelect ? actionSelect.value : '';
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);

        if (!action) { showToast('Please select an action', 'warning'); return; }
        if (selectedIds.length === 0) { showToast('Please select at least one user', 'warning'); return; }

        if (action === 'reset_password') {
            applyBulkResetPassword();
            return;
        }

        if (action === 'delete') {
            if (!confirm(`Are you sure you want to delete ${selectedIds.length} user(s)? This action cannot be undone.`)) {
                return;
            }
        } else {
            if (!confirm(`Are you sure you want to ${action} ${selectedIds.length} user(s)?`)) {
                return;
            }
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/users/bulk-actions';
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="action" value="${action}">
            ${selectedIds.map(id => `<input type="hidden" name="user_ids[]" value="${id}">`).join('')}
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================

    document.addEventListener('DOMContentLoaded', function() {
        // Role description change handler
        const roleSelect = document.getElementById('approveRoleSelect');
        if (roleSelect) {
            roleSelect.addEventListener('change', function() {
                const roleDescription = document.getElementById('roleDescription');
                const roleDescriptionText = document.getElementById('roleDescriptionText');
                const selectedValue = this.value;

                if (selectedValue && roleDescriptions[selectedValue]) {
                    if (roleDescriptionText) roleDescriptionText.textContent = roleDescriptions[selectedValue];
                    if (roleDescription) roleDescription.classList.remove('hidden');
                } else {
                    if (roleDescription) roleDescription.classList.add('hidden');
                }
            });
        }

        // Select all checkbox
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
                updateBulkActions();
            });
        }

        // Row checkboxes
        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        // Close action menus on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[id^="actionMenu-"]') && !e.target.closest('[onclick*="toggleActionMenu"]')) {
                document.querySelectorAll('[id^="actionMenu-"]').forEach(menu => menu.classList.add('hidden'));
            }
        });

        // Close modals on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.fixed.inset-0.z-50:not(.hidden)').forEach(modal => {
                    closeModal(modal.id);
                });
            }
        });
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
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection
