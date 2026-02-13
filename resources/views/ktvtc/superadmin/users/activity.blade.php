@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'User Activity: ' . $user->name)
@section('breadcrumb')
    <li class="inline-flex items-center">
        <a href="{{ route('super-admin.dashboard') }}" class="text-super hover:text-super-dark">Dashboard</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('super-admin.users.index') }}" class="text-super hover:text-super-dark">Users</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('super-admin.users.show', $user) }}" class="text-super hover:text-super-dark">{{ Str::limit($user->name, 20) }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-500">Activity</span>
    </li>
@endsection

@section('header-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('super-admin.users.show', $user) }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to User
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
            <i class="fas fa-print mr-2"></i> Print
        </button>
    </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="super-card mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <img class="h-16 w-16 rounded-full" src="{{ $user->profile_picture_url }}" alt="{{ $user->name }}">
                <div class="ml-6">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                    <div class="flex items-center mt-2 space-x-2">
                        <span class="px-2 py-1 text-xs rounded-full font-medium {{ $user->role_badge }}">
                            {{ $user->role_name }}
                        </span>
                        <span class="text-sm text-gray-600">{{ $user->email }}</span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Activity Records</p>
                <p class="text-2xl font-bold text-super">{{ $activities->total() }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="super-card mb-6">
        <form method="GET" action="{{ route('super-admin.users.activity', $user) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <div class="flex space-x-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
                    <span class="flex items-center text-gray-400">to</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
                </div>
            </div>

            <!-- Action Type -->
            <div>
                <label for="action" class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                <select name="action" id="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
                    <option value="">All Actions</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Logins</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logouts</option>
                    <option value="user_created" {{ request('action') == 'user_created' ? 'selected' : '' }}>User Created</option>
                    <option value="user_updated" {{ request('action') == 'user_updated' ? 'selected' : '' }}>User Updated</option>
                    <option value="user_deleted" {{ request('action') == 'user_deleted' ? 'selected' : '' }}>User Deleted</option>
                    <option value="role_changed" {{ request('action') == 'role_changed' ? 'selected' : '' }}>Role Changed</option>
                    <option value="password_reset" {{ request('action') == 'password_reset' ? 'selected' : '' }}>Password Reset</option>
                </select>
            </div>

            <!-- IP Address -->
            <div>
                <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                <input type="text" name="ip_address" id="ip_address" value="{{ request('ip_address') }}"
                       placeholder="Filter by IP"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-super focus:border-super">
            </div>

            <!-- Action Buttons -->
            <div class="md:col-span-4 flex items-center justify-between pt-4 border-t border-gray-200">
                <button type="submit" class="px-4 py-2 bg-super text-white rounded-lg hover:bg-super-dark font-medium">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
                <a href="{{ route('super-admin.users.activity', $user) }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                    <i class="fas fa-redo mr-2"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Activity Timeline -->
    <div class="super-card">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">Activity Timeline</h2>
            <div class="text-sm text-gray-500">
                Showing {{ $activities->firstItem() ?? 0 }}-{{ $activities->lastItem() ?? 0 }} of {{ $activities->total() }}
            </div>
        </div>

        @if($activities->count() > 0)
            <div class="space-y-4">
                @foreach($activities as $activity)
                    <div class="flex items-start border-l-4 {{ getActivityColor($activity->action) }} pl-4 py-4 hover:bg-gray-50 rounded-lg">
                        <!-- Icon -->
                        <div class="w-10 h-10 rounded-lg {{ getActivityColor($activity->action) }} flex items-center justify-center mr-4 mt-1">
                            <i class="fas {{ getActivityIcon($activity->action) }} text-white"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $activity->description }}</p>
                                    @if($activity->details)
                                        <p class="text-sm text-gray-600 mt-1">{{ $activity->details }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">{{ $activity->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->created_at->format('h:i:s A') }}</p>
                                </div>
                            </div>

                            <!-- Meta Information -->
                            <div class="flex items-center mt-3 text-sm text-gray-500">
                                @if($activity->ip_address)
                                    <div class="flex items-center mr-4">
                                        <i class="fas fa-globe mr-1"></i>
                                        <span class="font-mono">{{ $activity->ip_address }}</span>
                                    </div>
                                @endif

                                @if($activity->user_agent)
                                    <div class="flex items-center">
                                        <i class="fas fa-desktop mr-1"></i>
                                        <span class="truncate max-w-xs">{{ Str::limit($activity->user_agent, 80) }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Performed By -->
                            @if($activity->performed_by)
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs text-gray-500">Performed by:</p>
                                    <div class="flex items-center mt-1">
                                        <img class="h-6 w-6 rounded-full" src="{{ $activity->performed_by->profile_picture_url }}" alt="{{ $activity->performed_by->name }}">
                                        <span class="ml-2 text-sm font-medium">{{ $activity->performed_by->name }}</span>
                                        <span class="ml-2 text-xs text-gray-500">{{ $activity->performed_by->email }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                {{ $activities->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No activity records found</h3>
                <p class="text-gray-500">Try adjusting your filters or check back later.</p>
            </div>
        @endif
    </div>

    <!-- Activity Statistics -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="super-card text-center">
            <div class="text-2xl font-bold text-super mb-2">{{ $activities->total() }}</div>
            <div class="text-sm text-gray-600">Total Activities</div>
        </div>

        <div class="super-card text-center">
            <div class="text-2xl font-bold text-green-600 mb-2">
                {{ $activities->where('action', 'login')->count() }}
            </div>
            <div class="text-sm text-gray-600">Login Attempts</div>
        </div>

        <div class="super-card text-center">
            <div class="text-2xl font-bold text-blue-600 mb-2">
                {{ $activities->where('action', 'like', 'user_%')->count() }}
            </div>
            <div class="text-sm text-gray-600">User Actions</div>
        </div>

        <div class="super-card text-center">
            <div class="text-2xl font-bold text-purple-600 mb-2">
                {{ $activities->where('action', 'password_reset')->count() }}
            </div>
            <div class="text-sm text-gray-600">Password Resets</div>
        </div>
    </div>
</div>
@endsection

@php
    // Helper functions for activity colors and icons
    function getActivityColor($action) {
        $colors = [
            'login' => 'border-green-500',
            'logout' => 'border-gray-500',
            'user_created' => 'border-blue-500',
            'user_deleted' => 'border-red-500',
            'user_updated' => 'border-yellow-500',
            'role_changed' => 'border-purple-500',
            'password_reset' => 'border-pink-500',
            'settings_updated' => 'border-indigo-500',
        ];
        return $colors[$action] ?? 'border-gray-400';
    }

    function getActivityIcon($action) {
        $icons = [
            'login' => 'fa-sign-in-alt',
            'logout' => 'fa-sign-out-alt',
            'user_created' => 'fa-user-plus',
            'user_deleted' => 'fa-user-times',
            'user_updated' => 'fa-user-edit',
            'role_changed' => 'fa-user-tag',
            'password_reset' => 'fa-key',
            'settings_updated' => 'fa-cog',
        ];
        return $icons[$action] ?? 'fa-info-circle';
    }
@endphp

@section('scripts')
<script>
    // Set default date range to last 30 days if not set
    document.addEventListener('DOMContentLoaded', function() {
        const startDate = document.querySelector('input[name="start_date"]');
        const endDate = document.querySelector('input[name="end_date"]');

        if (!startDate.value && !endDate.value) {
            const end = new Date();
            const start = new Date();
            start.setDate(start.getDate() - 30);

            startDate.valueAsDate = start;
            endDate.valueAsDate = end;
        }
    });

    // Export activity to CSV
    function exportActivity() {
        const params = new URLSearchParams(window.location.search);
        window.location.href = '/super-admin/users/{{ $user->id }}/activity/export?' + params.toString();
    }
</script>
@endsection
