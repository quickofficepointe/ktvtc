@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Edit User')
@section('breadcrumb')
    <li class="inline-flex items-center">
        <a href="{{ route('super-admin.dashboard') }}" class="text-super hover:text-super-dark">Dashboard</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('super-admin.users.index') }}" class="text-super hover:text-super-dark">Users</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-500">Edit User</span>
    </li>
@endsection

@section('header-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('super-admin.users.show', $user) }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
            <i class="fas fa-eye mr-2"></i> View User
        </a>
        <a href="{{ route('super-admin.users.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
    </div>
@endsection

@section('content')
    <div class="super-card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit User: {{ $user->name }}</h2>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 text-xs rounded-full font-medium {{ $user->role_badge }}">
                    {{ $user->role_name }}
                </span>
                @if($user->is_active)
                    <span class="px-3 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                @else
                    <span class="px-3 py-1 text-xs bg-red-100 text-red-800 rounded-full">Inactive</span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('super-admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Basic Information</h3>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number"
                               value="{{ old('phone_number', $user->phone_number) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Role & Permissions -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Role & Permissions</h3>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">User Role *</label>
                        <select id="role" name="role"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                                required>
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ old('role', $user->role) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="campus_id" class="block text-sm font-medium text-gray-700 mb-1">Campus</label>
                        <select id="campus_id" name="campus_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
                            <option value="">No Campus</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}"
                                    {{ old('campus_id', $user->campus_id) == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->name }} ({{ $campus->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('campus_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Toggles -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active"
                                   value="1" class="h-4 w-4 text-super focus:ring-super border-gray-300 rounded"
                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active User
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_approved" name="is_approved"
                                   value="1" class="h-4 w-4 text-super focus:ring-super border-gray-300 rounded"
                                   {{ old('is_approved', $user->is_approved) ? 'checked' : '' }}>
                            <label for="is_approved" class="ml-2 block text-sm text-gray-900">
                                Approved User
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information (Full Width) -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profile Picture -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <img class="h-16 w-16 rounded-full" src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}">
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-2">Current profile picture</p>
                                <input type="file" id="profile_picture" name="profile_picture"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-super file:text-white hover:file:bg-super-dark">
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF (Max: 2MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bio/Notes -->
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio / Notes</label>
                        <textarea id="bio" name="bio" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Last updated: {{ $user->updated_at->format('M d, Y h:i A') }}
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('super-admin.users.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-super text-white rounded-lg hover:bg-super-dark font-medium">
                        <i class="fas fa-save mr-2"></i> Update User
                    </button>
                </div>
            </div>
        </form>

        <!-- Danger Zone -->
        @if($user->id !== auth()->id() && $user->role != 0)
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-red-700 mb-4">Danger Zone</h3>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-red-800">Delete User Account</h4>
                            <p class="mt-1 text-sm text-red-600">
                                Once you delete a user account, it cannot be recovered. All data associated with this user will be permanently removed.
                            </p>
                        </div>
                        <form action="{{ route('super-admin.users.destroy', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    onclick="if(confirm('Are you sure you want to permanently delete this user?')) { this.form.submit(); }"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium text-sm">
                                <i class="fas fa-trash mr-2"></i> Delete User
                            </button>
                        </form>
                    </div>

                    <div class="mt-4 pt-4 border-t border-red-200">
                        <h4 class="text-sm font-medium text-red-800">Reset Password</h4>
                        <p class="mt-1 text-sm text-red-600 mb-3">
                            Generate a new random password for this user. They will need to reset it on next login.
                        </p>
                        <button type="button" onclick="resetPassword({{ $user->id }})"
                                class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-medium text-sm">
                            <i class="fas fa-key mr-2"></i> Reset Password
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    // Password reset function
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
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.error || 'Error resetting password', 'error');
                }
            });
        }
    }

    // Show toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    // Handle form submission
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const roleSelect = document.getElementById('role');
            if (!roleSelect.value) {
                e.preventDefault();
                showToast('Please select a role for the user', 'error');
                roleSelect.focus();
            }
        });
    });
</script>
@endsection
