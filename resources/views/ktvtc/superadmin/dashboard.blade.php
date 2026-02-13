@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Super Admin Dashboard')
@section('breadcrumb')
    <li class="inline-flex items-center">
        <span class="text-gray-500">Dashboard</span>
    </li>
@endsection

@section('header-actions')
    <div class="flex items-center space-x-2">
        <button onclick="showToast('Refreshing data...', 'info')" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
            <i class="fas fa-sync-alt mr-2"></i> Refresh
        </button>
        <a href="{{ route('super-admin.system.settings') }}" class="px-4 py-2 bg-super text-white rounded-lg hover:bg-super-dark font-medium">
            <i class="fas fa-cog mr-2"></i> System Settings
        </a>
    </div>
@endsection

@section('content')
    <!-- System Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="stats-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Total Users</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total_users'] ?? 0 }}</p>
                    <div class="flex items-center mt-2">
                        <i class="fas fa-users text-white/60 mr-2"></i>
                        <span class="text-sm opacity-80">All System Users</span>
                    </div>
                </div>
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-users text-2xl text-white"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center justify-between text-sm">
                    <span>Active: {{ $stats['active_users'] ?? 0 }}</span>
                    <span>Pending: {{ $stats['pending_approval'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="stats-card p-6" style="background: linear-gradient(135deg, #2A9D8F 0%, #1D7873 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">System Health</p>
                    <p class="text-3xl font-bold mt-2">
                        @if($stats['system_errors'] == 0)
                            100%
                        @else
                            {{ max(100 - ($stats['system_errors'] / 10), 80) }}%
                        @endif
                    </p>
                    <div class="flex items-center mt-2">
                        <div class="w-2 h-2 rounded-full {{ $stats['system_errors'] == 0 ? 'bg-green-400' : 'bg-yellow-400' }} mr-2"></div>
                        <span class="text-sm opacity-80">
                            @if($stats['system_errors'] == 0)
                                All Systems Normal
                            @else
                                {{ $stats['system_errors'] }} Errors
                            @endif
                        </span>
                    </div>
                </div>
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-heartbeat text-2xl text-white"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-white/20 rounded-full h-2">
                    <div class="bg-white h-2 rounded-full" style="width: {{ $stats['system_errors'] == 0 ? '100%' : '90%' }}"></div>
                </div>
            </div>
        </div>

        <!-- Disk Usage -->
        <div class="stats-card p-6" style="background: linear-gradient(135deg, #E63946 0%, #C1121F 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Disk Usage</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['disk_usage']['percentage'] ?? 0 }}%</p>
                    <div class="flex items-center mt-2">
                        <i class="fas fa-hdd text-white/60 mr-2"></i>
                        <span class="text-sm opacity-80">{{ $stats['disk_usage']['used'] ?? '0B' }} used</span>
                    </div>
                </div>
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-database text-2xl text-white"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex items-center justify-between text-sm">
                    <span>Free: {{ $stats['disk_usage']['free'] ?? '0B' }}</span>
                    <span>Total: {{ $stats['disk_usage']['total'] ?? '0B' }}</span>
                </div>
            </div>
        </div>

        <!-- Today's Activity -->
        <div class="stats-card p-6" style="background: linear-gradient(135deg, #457B9D 0%, #1D3557 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Today's Activity</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['today_logins'] ?? 0 }}</p>
                    <div class="flex items-center mt-2">
                        <i class="fas fa-sign-in-alt text-white/60 mr-2"></i>
                        <span class="text-sm opacity-80">User Logins</span>
                    </div>
                </div>
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-chart-line text-2xl text-white"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-sm">
                    <span class="opacity-80">Last 24 hours</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if(count($alerts) > 0)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                System Alerts
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($alerts as $alert)
                    <div class="bg-white rounded-xl border-l-4 {{ $alert['type'] == 'critical' ? 'border-danger' : ($alert['type'] == 'warning' ? 'border-warning' : 'border-info') }} p-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg {{ $alert['type'] == 'critical' ? 'bg-danger/10' : ($alert['type'] == 'warning' ? 'bg-warning/10' : 'bg-info/10') }} flex items-center justify-center mr-3">
                                <i class="fas {{ $alert['icon'] }} {{ $alert['type'] == 'critical' ? 'text-danger' : ($alert['type'] == 'warning' ? 'text-warning' : 'text-info') }}"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">{{ $alert['message'] }}</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $alert['type'] == 'critical' ? 'Requires immediate attention' : ($alert['type'] == 'warning' ? 'Monitor closely' : 'Informational') }}
                                </p>
                            </div>
                            @if($alert['type'] == 'critical')
                                <span class="ml-2 px-2 py-1 bg-danger text-white text-xs rounded-full font-bold animate-pulse">
                                    CRITICAL
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Activity & User Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activity -->
        <div class="super-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">Recent System Activity</h2>
                <a href="{{ route('super-admin.system.logs') }}" class="text-sm text-super hover:text-super-dark font-medium">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-4 max-h-96 overflow-y-auto super-scrollbar">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 rounded-full {{ getActivityColor($activity->action) }} flex items-center justify-center mr-3 mt-1">
    <i class="fas {{ getActivityIcon($activity->action) }} text-white text-sm"></i>
</div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $activity->description }}</p>
                            <div class="flex items-center mt-1 text-sm text-gray-500">
                                @if($activity->user)
                                    <span class="font-medium">{{ $activity->user->name }}</span>
                                    <span class="mx-2">•</span>
                                @endif
                                <span>{{ $activity->created_at->diffForHumans() }}</span>
                                @if($activity->ip_address)
                                    <span class="mx-2">•</span>
                                    <span class="font-mono text-xs">{{ $activity->ip_address }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-3 opacity-50"></i>
                        <p>No recent activity</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- User Distribution -->
        <div class="super-card p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">User Distribution by Role</h2>
            <div class="space-y-4">
                @foreach($userDistribution as $role => $count)
                    <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg {{ getRoleColor($role) }} flex items-center justify-center mr-3">
    <i class="fas {{ getRoleIcon($role) }} text-white"></i>
</div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $role }}</p>
                                <p class="text-sm text-gray-500">{{ $count }} users</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-super">{{ $count }}</div>
                            <div class="text-xs text-gray-500">
                                {{ number_format(($count / $stats['total_users']) * 100, 1) }}%
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('super-admin.users.index') }}" class="block text-center py-3 bg-super/5 text-super rounded-lg hover:bg-super/10 font-medium">
                    <i class="fas fa-users mr-2"></i> Manage All Users
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Quick Super Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('super-admin.users.create') }}" class="super-card p-6 hover:shadow-lg text-center group">
                <div class="w-16 h-16 rounded-2xl bg-super/10 group-hover:bg-super/20 flex items-center justify-center mx-auto mb-4 transition-colors">
                    <i class="fas fa-user-plus text-2xl text-super"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Create User</h3>
                <p class="text-gray-600 text-sm">Add new system user with any role</p>
            </a>

            <a href="{{ route('super-admin.system.database') }}" class="super-card p-6 hover:shadow-lg text-center group">
                <div class="w-16 h-16 rounded-2xl bg-success/10 group-hover:bg-success/20 flex items-center justify-center mx-auto mb-4 transition-colors">
                    <i class="fas fa-database text-2xl text-success"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Backup Database</h3>
                <p class="text-gray-600 text-sm">Create system backup for safety</p>
            </a>

            <a href="{{ route('super-admin.roles.index') }}" class="super-card p-6 hover:shadow-lg text-center group">
                <div class="w-16 h-16 rounded-2xl bg-info/10 group-hover:bg-info/20 flex items-center justify-center mx-auto mb-4 transition-colors">
                    <i class="fas fa-shield-alt text-2xl text-info"></i>
                </div>
                <h3 class="font-bold text-gray-800 mb-2">Manage Roles</h3>
                <p class="text-gray-600 text-sm">Configure permissions and access</p>
            </a>
        </div>
    </div>
@endsection

@php
    // Helper methods for the view (these would typically be in a View Composer)
    function getActivityColor($action) {
        $colors = [
            'login' => 'bg-success',
            'logout' => 'bg-gray-500',
            'user_created' => 'bg-info',
            'user_deleted' => 'bg-danger',
            'user_updated' => 'bg-warning',
            'role_changed' => 'bg-purple-500',
            'settings_updated' => 'bg-blue-500',
            'password_reset' => 'bg-pink-500',
        ];
        return $colors[$action] ?? 'bg-gray-400';
    }

    function getActivityIcon($action) {
        $icons = [
            'login' => 'fa-sign-in-alt',
            'logout' => 'fa-sign-out-alt',
            'user_created' => 'fa-user-plus',
            'user_deleted' => 'fa-user-times',
            'user_updated' => 'fa-user-edit',
            'role_changed' => 'fa-user-tag',
            'settings_updated' => 'fa-cog',
            'password_reset' => 'fa-key',
        ];
        return $icons[$action] ?? 'fa-info-circle';
    }

    function getRoleColor($role) {
        $colors = [
            'Super Admin' => 'bg-gradient-to-br from-super to-super-dark',
            'Admin' => 'bg-gradient-to-br from-blue-500 to-blue-700',
            'Mschool' => 'bg-gradient-to-br from-green-500 to-green-700',
            'Library' => 'bg-gradient-to-br from-purple-500 to-purple-700',
            'Cafeteria' => 'bg-gradient-to-br from-yellow-500 to-yellow-700',
            'Website' => 'bg-gradient-to-br from-indigo-500 to-indigo-700',
            'Student' => 'bg-gradient-to-br from-gray-500 to-gray-700',
            'Finance' => 'bg-gradient-to-br from-teal-500 to-teal-700',
        ];
        return $colors[$role] ?? 'bg-gradient-to-br from-gray-400 to-gray-600';
    }

    function getRoleIcon($role) {
        $icons = [
            'Super Admin' => 'fa-crown',
            'Admin' => 'fa-user-tie',
            'Mschool' => 'fa-school',
            'Library' => 'fa-book',
            'Cafeteria' => 'fa-utensils',
            'Website' => 'fa-globe',
            'Student' => 'fa-user-graduate',
            'Finance' => 'fa-chart-line',
        ];
        return $icons[$role] ?? 'fa-user';
    }
@endphp

@section('scripts')
<script>
    // Initialize real-time updates
    let updateInterval;

    function startRealTimeUpdates() {
        updateInterval = setInterval(() => {
            updateServerStats();
            updateNotifications();
        }, 30000); // Update every 30 seconds
    }

    function updateServerStats() {
        $.ajax({
            url: '',
            method: 'GET',
            success: function(data) {
                // Update disk usage
                if (data.disk_usage) {
                    const diskElement = document.querySelector('.disk-usage-percentage');
                    if (diskElement) {
                        diskElement.textContent = data.disk_usage.percentage + '%';
                    }
                }
            }
        });
    }

    function updateNotifications() {
        loadNotifications();
    }

    // Start updates when page loads
    document.addEventListener('DOMContentLoaded', function() {
        startRealTimeUpdates();

        // Check for critical alerts
        $.ajax({
            url: '',
            method: 'GET',
            success: function(alerts) {
                alerts.forEach(alert => {
                    if (alert.type === 'critical') {
                        showToast(alert.message, 'error', 10000);
                    }
                });
            }
        });
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
</script>
@endsection
