@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Create New User')
@section('breadcrumb')
    <li class="inline-flex items-center">
        <a href="{{ route('super-admin.dashboard') }}" class="text-super hover:text-super-dark">Dashboard</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('super-admin.users.index') }}" class="text-super hover:text-super-dark">Users</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-500">Create User</span>
    </li>
@endsection

@section('header-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('super-admin.users.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
    </div>
@endsection

@section('content')
    <div class="super-card max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Create New User</h2>

        <form method="POST" action="{{ route('super-admin.users.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Basic Information</h3>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                               placeholder="John Doe"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                               placeholder="john@example.com"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number"
                               value="{{ old('phone_number') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                               placeholder="+1234567890">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Account Settings</h3>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">User Role *</label>
                        <select id="role" name="role"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                                required>
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
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
                                <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>
                                    {{ $campus->name }} ({{ $campus->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('campus_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Settings -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="radio" id="auto_password" name="password_option" value="auto"
                                       checked class="h-4 w-4 text-super focus:ring-super border-gray-300">
                                <label for="auto_password" class="ml-2 block text-sm text-gray-900">
                                    Generate random password
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="manual_password" name="password_option" value="manual"
                                       class="h-4 w-4 text-super focus:ring-super border-gray-300">
                                <label for="manual_password" class="ml-2 block text-sm text-gray-900">
                                    Set custom password
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Password Fields (hidden by default) -->
                    <div id="custom_password_fields" class="hidden space-y-3">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                            <input type="password" id="password" name="password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                                   placeholder="Minimum 8 characters">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                                   placeholder="Confirm your password">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bio -->
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio / Notes</label>
                        <textarea id="bio" name="bio" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super"
                                  placeholder="Add any notes or information about this user...">{{ old('bio') }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Initial Settings -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-700">Initial Account Status</h4>

                        <div class="flex items-center">
                            <input type="checkbox" id="send_welcome_email" name="send_welcome_email"
                                   value="1" class="h-4 w-4 text-super focus:ring-super border-gray-300 rounded"
                                   checked>
                            <label for="send_welcome_email" class="ml-2 block text-sm text-gray-900">
                                Send welcome email with login instructions
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="require_password_change" name="require_password_change"
                                   value="1" class="h-4 w-4 text-super focus:ring-super border-gray-300 rounded"
                                   checked>
                            <label for="require_password_change" class="ml-2 block text-sm text-gray-900">
                                Require password change on first login
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i> All fields marked with * are required
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('super-admin.users.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-super text-white rounded-lg hover:bg-super-dark font-medium">
                        <i class="fas fa-user-plus mr-2"></i> Create User
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle password option toggle
        const autoPassword = document.getElementById('auto_password');
        const manualPassword = document.getElementById('manual_password');
        const customPasswordFields = document.getElementById('custom_password_fields');

        function togglePasswordFields() {
            if (manualPassword.checked) {
                customPasswordFields.classList.remove('hidden');
                document.getElementById('password').required = true;
                document.getElementById('password_confirmation').required = true;
            } else {
                customPasswordFields.classList.add('hidden');
                document.getElementById('password').required = false;
                document.getElementById('password_confirmation').required = false;
            }
        }

        autoPassword.addEventListener('change', togglePasswordFields);
        manualPassword.addEventListener('change', togglePasswordFields);

        // Initialize on page load
        togglePasswordFields();

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const roleSelect = document.getElementById('role');
            if (!roleSelect.value) {
                e.preventDefault();
                showToast('Please select a role for the user', 'error');
                roleSelect.focus();
                return;
            }

            if (manualPassword.checked) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('password_confirmation').value;

                if (password.length < 8) {
                    e.preventDefault();
                    showToast('Password must be at least 8 characters long', 'error');
                    document.getElementById('password').focus();
                    return;
                }

                if (password !== confirmPassword) {
                    e.preventDefault();
                    showToast('Passwords do not match', 'error');
                    document.getElementById('password_confirmation').focus();
                    return;
                }
            }
        });
    });

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
</script>
@endsection
