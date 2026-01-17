@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'User Management - Admin Panel')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="h3 mb-2 text-gray-800">User Management</h1>
            <p class="text-muted">Manage all registered users and their roles</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="exportToExcel()">
                <i class="fas fa-download"></i> Export Excel
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-6">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Approved Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Approval</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $adminUsers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card shadow">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2"></i>All Registered Users
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <img src="{{ $user->profile_picture ? asset('storage/'.$user->profile_picture) : asset('Assets/images/default-avatar.png') }}"
                                     class="rounded-circle" width="40" height="40" alt="Profile">
                            </td>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                @if($user->bio)
                                <br><small class="text-muted">{{ Str::limit($user->bio, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                @if($user->email_verified_at)
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> Verified</small>
                                @else
                                <br><small class="text-warning"><i class="fas fa-clock"></i> Unverified</small>
                                @endif
                            </td>
                            <td>{{ $user->phone_number ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->role_badge }}">{{ $user->role_name }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->status_badge }}">{{ $user->status_text }}</span>
                            </td>
                            <td>{{ $user->created_at->format('M j, Y') }}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#userModal{{ $user->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               data-bs-toggle="modal"
                                               data-bs-target="#editUserModal{{ $user->id }}">
                                                <i class="fas fa-edit text-primary"></i> Edit
                                            </a>
                                        </li>
                                        @if(!$user->is_approved)
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               onclick="approveUser('{{ $user->id }}')">
                                                <i class="fas fa-check text-success"></i> Approve
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               onclick="updateStatus('{{ $user->id }}', '{{ $user->is_approved ? 0 : 1 }}')">
                                                <i class="fas fa-power-off text-{{ $user->is_approved ? 'warning' : 'success' }}"></i>
                                                {{ $user->is_approved ? 'Disapprove' : 'Approve' }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger"
                                               href="#" onclick="deleteUser('{{ $user->id }}')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- View User Modal -->
                        <div class="modal fade" id="userModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">User Details - {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <img src="{{ $user->profile_picture ? asset('storage/'.$user->profile_picture) : asset('Assets/images/default-avatar.png') }}"
                                                     class="rounded-circle mb-3" width="120" height="120" alt="Profile">
                                                <h6>{{ $user->name }}</h6>
                                                <span class="badge bg-{{ $user->role_badge }}">{{ $user->role_name }}</span>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <strong>Email:</strong><br>
                                                        {{ $user->email }}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Phone:</strong><br>
                                                        {{ $user->phone_number ?? 'N/A' }}
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <strong>Status:</strong><br>
                                                        <span class="badge bg-{{ $user->status_badge }}">{{ $user->status_text }}</span>
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Registered:</strong><br>
                                                        {{ $user->created_at->format('M j, Y \\a\\t g:i A') }}
                                                    </div>
                                                </div>
                                                @if($user->bio)
                                                <div class="mb-3">
                                                    <strong>Bio:</strong><br>
                                                    {{ $user->bio }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserModal{{ $user->id }}"
                                                data-bs-dismiss="modal">
                                            Edit User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit User - {{ $user->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Role</label>
                                                <select name="role" class="form-select" required>
                                                    <option value="1" {{ $user->role == 1 ? 'selected' : '' }}>Main School</option>
                                                    <option value="2" {{ $user->role == 2 ? 'selected' : '' }}>Admin</option>
                                                    <option value="3" {{ $user->role == 3 ? 'selected' : '' }}>Scholarship</option>
                                                    <option value="4" {{ $user->role == 4 ? 'selected' : '' }}>Library</option>
                                                    <option value="5" {{ $user->role == 5 ? 'selected' : '' }}>Student</option>
                                                    <option value="6" {{ $user->role == 6 ? 'selected' : '' }}>Cafeteria</option>
                                                    <option value="7" {{ $user->role == 7 ? 'selected' : '' }}>Finance</option>
                                                    <option value="8" {{ $user->role == 8 ? 'selected' : '' }}>Trainers</option>
                                                    <option value="9" {{ $user->role == 9 ? 'selected' : '' }}>Website</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Approval Status</label>
                                                <select name="is_approved" class="form-select">
                                                    <option value="1" {{ $user->is_approved ? 'selected' : '' }}>Approved</option>
                                                    <option value="0" {{ !$user->is_approved ? 'selected' : '' }}>Pending Approval</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Update User</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select" required>
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
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Forms for Actions -->
<form id="statusForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="is_approved" id="statusInput">
</form>

<form id="approveForm" method="POST" style="display: none;">
    @csrf
    @method('PUT')
</form>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#usersTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'desc']],
            "responsive": true
        });
    });

    function updateStatus(userId, status) {
        if (confirm('Are you sure you want to update this user\'s approval status?')) {
            const form = document.getElementById('statusForm');
            form.action = `/admin/users/${userId}/status`;
            document.getElementById('statusInput').value = status;
            form.submit();
        }
    }

    function approveUser(userId) {
        if (confirm('Are you sure you want to approve this user?')) {
            const form = document.getElementById('approveForm');
            form.action = `/admin/users/${userId}/approve`;
            form.submit();
        }
    }

    function deleteUser(userId) {
        if (confirm('⚠️ ARE YOU SURE?\n\nThis will permanently delete this user and all their data. This action cannot be undone.')) {
            const form = document.getElementById('deleteForm');
            form.action = `/admin/users/${userId}`;
            form.submit();
        }
    }

    function exportToExcel() {
        alert('Excel export functionality to be implemented');
    }
</script>

<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    .table th {
        font-weight: 600;
    }
</style>
@endsection
