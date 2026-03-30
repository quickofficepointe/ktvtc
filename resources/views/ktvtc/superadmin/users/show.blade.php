@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'User Details: ' . $user->name)
@section('breadcrumb')
    <li class="inline-flex items-center">
        <a href="{{ route('super-admin.dashboard') }}" class="text-super hover:text-super-dark">Dashboard</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('super-admin.users.index') }}" class="text-super hover:text-super-dark">Users</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-500">Details</span>
    </li>
@endsection

@section('header-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('super-admin.users.edit', $user) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <a href="{{ route('super-admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="super-card mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <img class="h-20 w-20 rounded-full border-4 border-white shadow" src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}">
                <div class="ml-6">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                    <div class="flex items-center mt-2 space-x-2">
                        <span class="px-3 py-1 text-sm rounded-full font-medium {{ $user->role_badge }}">
                            <i class="fas fa-user-tag mr-1"></i> {{ $user->role_name }}
                        </span>
                        <span class="px-3 py-1 text-sm rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas fa-circle mr-1 text-xs"></i> {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="px-3 py-1 text-sm rounded-full {{ $user->is_approved ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                            <i class="fas fa-{{ $user->is_approved ? 'check' : 'clock' }} mr-1"></i> {{ $user->is_approved ? 'Approved' : 'Pending' }}
                        </span>
                        @if($user->trashed())
                            <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-trash mr-1"></i> Deleted
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Member since</p>
                <p class="font-medium text-gray-800">{{ $user->created_at->format('F d, Y') }}</p>
                <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - User Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="super-card">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-id-card text-super mr-2"></i>
                    Basic Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Info -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Personal Details</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Email Address</p>
                                <p class="font-medium text-gray-900">{{ $user->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Phone Number</p>
                                <p class="font-medium text-gray-900">{{ $user->phone_number ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email Verification</p>
                                <p class="font-medium">
                                    @if($user->email_verified_at)
                                        <span class="text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i> Verified {{ $user->email_verified_at->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-yellow-600">
                                            <i class="fas fa-clock mr-1"></i> Not Verified
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Campus & Account Info -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Account Details</h3>
                        <div class="space-y-4">
                            @if($user->campus)
                                <div>
                                    <p class="text-sm text-gray-500">Assigned Campus</p>
                                    <div class="flex items-center mt-1">
                                        <i class="fas fa-school text-gray-400 mr-2"></i>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $user->campus->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $user->campus->code }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div>
                                <p class="text-sm text-gray-500">Last Updated</p>
                                <p class="font-medium text-gray-900">{{ $user->updated_at->format('M d, Y h:i A') }}</p>
                                <p class="text-sm text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Last Login</p>
                                <p class="font-medium text-gray-900">
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->format('M d, Y h:i A') }}
                                        <span class="text-sm text-gray-500 block">{{ $user->last_login_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-gray-400">Never logged in</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bio -->
                @if($user->bio)
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Bio / Description</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ $user->bio }}</p>
                    </div>
                @endif
            </div>

            <!-- Login History Card -->
            <div class="super-card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-history text-super mr-2"></i>
                        Recent Login Activity
                    </h2>
                    <a href="{{ route('super-admin.users.activity', $user) }}" class="text-sm text-super hover:text-super-dark font-medium">
                        View All Activity <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                @if($loginHistory->count() > 0)
                    <div class="space-y-4 max-h-80 overflow-y-auto super-scrollbar">
                        @foreach($loginHistory as $login)
                            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-sign-in-alt text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">Successful Login</p>
                                        <div class="flex items-center mt-1 text-sm text-gray-500">
                                            <i class="fas fa-globe mr-1"></i>
                                            <span class="font-mono text-xs">{{ $login->ip_address ?? 'Unknown IP' }}</span>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-user-agent mr-1"></i>
                                            <span class="text-xs truncate max-w-xs">{{ Str::limit($login->user_agent, 50) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-800">{{ $login->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $login->created_at->format('h:i A') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-sign-in-alt text-3xl mb-3 opacity-50"></i>
                        <p>No login history available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column - Actions & Stats -->
        <div class="space-y-6">
            <!-- Quick Actions Card -->
            <div class="super-card">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-bolt text-super mr-2"></i>
                    Quick Actions
                </h2>
                <div class="space-y-3">
                    <!-- Toggle Active Status -->
                    <button onclick="toggleUserStatus({{ $user->id }}, {{ $user->is_active ? 'false' : 'true' }})"
                            class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg {{ $user->is_active ? 'bg-red-100' : 'bg-green-100' }} flex items-center justify-center mr-3">
                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check-circle' }} {{ $user->is_active ? 'text-red-600' : 'text-green-600' }}"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->is_active ? 'Deactivate' : 'Activate' }} Account</p>
                                <p class="text-sm text-gray-500">{{ $user->is_active ? 'Prevent user from logging in' : 'Allow user to login' }}</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </button>

                    <!-- Toggle Approval -->
                    <button onclick="toggleApproval({{ $user->id }}, {{ $user->is_approved ? 'false' : 'true' }})"
                            class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg {{ $user->is_approved ? 'bg-yellow-100' : 'bg-blue-100' }} flex items-center justify-center mr-3">
                                <i class="fas fa-{{ $user->is_approved ? 'user-times' : 'user-check' }} {{ $user->is_approved ? 'text-yellow-600' : 'text-blue-600' }}"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->is_approved ? 'Unapprove' : 'Approve' }} User</p>
                                <p class="text-sm text-gray-500">{{ $user->is_approved ? 'Require admin approval' : 'Grant full access' }}</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </button>

                    <!-- Reset Password -->
                    <button onclick="resetPassword({{ $user->id }})"
                            class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
                                <i class="fas fa-key text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Reset Password</p>
                                <p class="text-sm text-gray-500">Send new password to user</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </button>

                    <!-- Impersonate -->
                    @if($user->role != 0 && $user->id !== auth()->id())
                        <button onclick="impersonateUser({{ $user->id }})"
                                class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-user-secret text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">Impersonate User</p>
                                    <p class="text-sm text-gray-500">Login as this user</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="super-card">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-chart-bar text-super mr-2"></i>
                    Account Statistics
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
                                <i class="fas fa-sign-in-alt text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Logins</p>
                                <p class="font-medium text-gray-900">{{ $loginHistory->count() }}</p>
                            </div>
                        </div>
                        @if($loginHistory->count() > 0)
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Last Login</p>
                                <p class="font-medium text-gray-900">{{ $loginHistory->first()->created_at->diffForHumans() }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-500 mb-2">Account Age</p>
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-super h-2 rounded-full" style="width: {{ min(100, $user->created_at->diffInDays(now()) * 100 / 365) }}%"></div>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-900">{{ $user->created_at->diffInDays(now()) }} days</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            @if($user->id !== auth()->id() && $user->role != 0)
                <div class="super-card border border-red-200">
                    <h2 class="text-xl font-bold text-red-700 mb-6 flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                        Danger Zone
                    </h2>
                    <div class="space-y-3">
                        @if($user->trashed())
                            <!-- Restore User -->
                            <form action="{{ route('super-admin.users.restore', $user) }}" method="POST" class="w-full">
                                @csrf
                                @method('PATCH')
                                <button type="submit" onclick="return confirm('Restore this user?')"
                                        class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-50 border border-green-200">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-trash-restore text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-green-800">Restore User</p>
                                            <p class="text-sm text-green-600">Restore deleted account</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-green-400"></i>
                                </button>
                            </form>
                        @else
                            <!-- Delete User -->
                            <form action="{{ route('super-admin.users.destroy', $user) }}" method="POST" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('⚠️ Are you sure you want to delete this user?\n\nThis action cannot be undone.')"
                                        class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-red-50 border border-red-200">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center mr-3">
                                            <i class="fas fa-trash text-red-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-red-800">Delete User</p>
                                            <p class="text-sm text-red-600">Permanently delete account</p>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-red-400"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
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
                    showToast('Password reset! New password: ' + response.new_password, 'success');
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

    // Copy email to clipboard
    function copyEmail() {
        const email = '{{ $user->email }}';
        navigator.clipboard.writeText(email).then(() => {
            showToast('Email copied to clipboard', 'success');
        });
    }
</script>
@endsection
