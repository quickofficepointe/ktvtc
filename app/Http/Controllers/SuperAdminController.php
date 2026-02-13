<?php

namespace App\Http\Controllers;

use App\Models\User;
// Remove Role import - LINE 5
use App\Models\SystemLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use App\Models\Campus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DeepCopy\f013\C;

class SuperAdminController extends Controller
{
    /**
     * Display super admin dashboard
     */
    public function dashboard()
    {
        // System stats
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'pending_approval' => User::where('is_approved', false)->count(),
            'today_logins' => SystemLog::where('action', 'login')
                ->whereDate('created_at', today())
                ->count(),
            'system_errors' => SystemLog::where('level', 'error')
                ->whereDate('created_at', today())
                ->count(),
            'disk_usage' => $this->getDiskUsage(),
            'memory_usage' => $this->getMemoryUsage(),
        ];

        // Recent activities
        $recentActivities = SystemLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // User distribution by role
        $userDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->mapWithKeys(function ($item) {
                $roleName = $this->getRoleName($item->role);
                return [$roleName => $item->count];
            });

        // System alerts
        $alerts = $this->getSystemAlerts();

        return view('ktvtc.superadmin.dashboard', compact(
            'stats',
            'recentActivities',
            'userDistribution',
            'alerts'
        ));
    }

    /**
     * Display all users
     */
    public function users(Request $request)
    {
        $query = User::withTrashed()->with(['campus', 'shop']);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'pending':
                    $query->where('is_approved', false);
                    break;
                case 'approved':
                    $query->where('is_approved', true);
                    break;
                case 'deleted':
                    $query->onlyTrashed();
                    break;
            }
        }

        // Filter by campus
        if ($request->has('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Role list for filter dropdown
        $roles = collect([
            (object)['id' => 0, 'name' => 'Super Admin'],
            (object)['id' => 1, 'name' => 'Main School'],
            (object)['id' => 2, 'name' => 'Admin'],
            (object)['id' => 3, 'name' => 'Scholarship'],
            (object)['id' => 4, 'name' => 'Library'],
            (object)['id' => 5, 'name' => 'Student'],
            (object)['id' => 6, 'name' => 'Cafeteria'],
            (object)['id' => 7, 'name' => 'Finance'],
            (object)['id' => 8, 'name' => 'Trainers'],
            (object)['id' => 9, 'name' => 'Website'],
        ]);

        $campuses = Campus::active()->get();

        return view('ktvtc.superadmin.users.index', compact('users', 'roles', 'campuses'));
    }

    /**
     * Restore deleted user
     */
    public function restoreUser(Request $request, $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);

        // Prevent restoring super admin accounts
        if ($user->role == 0) {
            return back()->with('error', 'Cannot restore super admin accounts');
        }

        $user->restore();

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_restored',
            'description' => "Restored user: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'User restored successfully');
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        // Fixed: Use same role collection as users() method
        $roles = collect([
            (object)['id' => 0, 'name' => 'Super Admin'],
            (object)['id' => 1, 'name' => 'Main School'],
            (object)['id' => 2, 'name' => 'Admin'],
            (object)['id' => 3, 'name' => 'Scholarship'],
            (object)['id' => 4, 'name' => 'Library'],
            (object)['id' => 5, 'name' => 'Student'],
            (object)['id' => 6, 'name' => 'Cafeteria'],
            (object)['id' => 7, 'name' => 'Finance'],
            (object)['id' => 8, 'name' => 'Trainers'],
            (object)['id' => 9, 'name' => 'Website'],
        ]);

        $campuses = Campus::active()->get();
        return view('ktvtc.superadmin.users.create', compact('roles', 'campuses'));
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|integer',
            'campus_id' => 'nullable|exists:campuses,id',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $password = $request->password ?? Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($password),
            'role' => $request->role,
            'campus_id' => $request->campus_id,
            'is_approved' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_created',
            'description' => "Created user: {$user->email} with role ID: {$user->role}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('super-admin.users.show', $user)
            ->with('success', "User created successfully. Password: {$password}");
    }

    /**
     * Edit user
     */
    public function editUser(User $user)
    {
        // Prevent editing own super admin account
        if ($user->id === auth()->id() && $user->role == 0) {
            return back()->with('error', 'Cannot edit your own super admin account');
        }

        // Fixed: Use same role collection as users() method
        $roles = collect([
            (object)['id' => 0, 'name' => 'Super Admin'],
            (object)['id' => 1, 'name' => 'Main School'],
            (object)['id' => 2, 'name' => 'Admin'],
            (object)['id' => 3, 'name' => 'Scholarship'],
            (object)['id' => 4, 'name' => 'Library'],
            (object)['id' => 5, 'name' => 'Student'],
            (object)['id' => 6, 'name' => 'Cafeteria'],
            (object)['id' => 7, 'name' => 'Finance'],
            (object)['id' => 8, 'name' => 'Trainers'],
            (object)['id' => 9, 'name' => 'Website'],
        ]);

        $campuses = Campus::active()->get();
        return view('ktvtc.superadmin.users.edit', compact('user', 'roles', 'campuses'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        // Prevent editing own super admin account
        if ($user->id === auth()->id() && $user->role == 0) {
            return back()->with('error', 'Cannot edit your own super admin account');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|integer',
            'campus_id' => 'nullable|exists:campuses,id',
            'phone_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
        ]);

        $oldRole = $user->role;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'campus_id' => $request->campus_id,
            'is_active' => $request->has('is_active'),
            'is_approved' => $request->has('is_approved'),
        ]);

        // Log role change
        if ($oldRole != $request->role) {
            SystemLog::create([
                'user_id' => auth()->id(),
                'action' => 'user_role_changed',
                'description' => "Changed role for {$user->email} from {$oldRole} to {$request->role}",
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->route('super-admin.users.show', $user)
            ->with('success', 'User updated successfully');
    }

    /**
     * Delete user
     */
    public function destroyUser(Request $request, User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account');
        }

        // Prevent deleting super admin accounts (except self)
        if ($user->role == 0) {
            return back()->with('error', 'Cannot delete super admin accounts');
        }

        $user->delete();

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_deleted',
            'description' => "Deleted user: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus(Request $request, User $user)
    {
        // Prevent toggling own status
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'Cannot change your own status'], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_status_toggled',
            'description' => "Changed status for {$user->email} to " . ($user->is_active ? 'active' : 'inactive'),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated',
            'is_active' => $user->is_active
        ]);
    }

    /**
     * Toggle user approval
     */
    public function toggleApproval(Request $request, User $user)
    {
        $user->update(['is_approved' => !$user->is_approved]);

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'user_approval_toggled',
            'description' => "Changed approval for {$user->email} to " . ($user->is_approved ? 'approved' : 'pending'),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User approval updated',
            'is_approved' => $user->is_approved
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $newPassword = Str::random(12);
        $user->update(['password' => Hash::make($newPassword)]);

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'password_reset',
            'description' => "Reset password for {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
            'new_password' => $newPassword
        ]);
    }

    /**
     * Impersonate user
     */
    public function impersonate(Request $request, User $user)
    {
        // Store original user ID in session
        session(['original_user_id' => auth()->id()]);

        // Log impersonation start
        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'impersonation_start',
            'description' => "Started impersonating user: {$user->email}",
            'ip_address' => $request->ip(),
        ]);

        // Log in as the target user
        auth()->login($user);

        return redirect()->route('home');
    }

    /**
     * Stop impersonation
     */
    public function stopImpersonate(Request $request)
    {
        $originalUserId = session('original_user_id');

        if ($originalUserId) {
            $originalUser = User::find($originalUserId);

            if ($originalUser) {
                // Log impersonation end
                SystemLog::create([
                    'user_id' => $originalUser->id,
                    'action' => 'impersonation_end',
                    'description' => "Stopped impersonating user",
                    'ip_address' => $request->ip(),
                ]);

                auth()->login($originalUser);
                session()->forget('original_user_id');
            }
        }

        return redirect()->route('super-admin.dashboard');
    }

    /**
     * User activity log
     */
    public function userActivity(User $user)
    {
        $activities = SystemLog::where('user_id', $user->id)
            ->orWhere('action', 'like', "%{$user->email}%")
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('ktvtc.superadmin.users.activity', compact('user', 'activities'));
    }

    /**
     * Bulk user actions
     */
    public function bulkUserActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,approve,delete,export',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $userIds = $request->user_ids;
        $action = $request->action;
        $count = 0;

        switch ($action) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['is_active' => true]);
                $count = count($userIds);
                break;

            case 'deactivate':
                // Exclude self from deactivation
                $userIds = array_diff($userIds, [auth()->id()]);
                User::whereIn('id', $userIds)->update(['is_active' => false]);
                $count = count($userIds);
                break;

            case 'approve':
                User::whereIn('id', $userIds)->update(['is_approved' => true]);
                $count = count($userIds);
                break;

            case 'delete':
                // Exclude self and super admins from deletion
                $users = User::whereIn('id', $userIds)
                    ->where('id', '!=', auth()->id())
                    ->where('role', '!=', 0)
                    ->get();

                $count = $users->count();
                foreach ($users as $user) {
                    $user->delete();
                }
                break;
        }

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'bulk_user_action',
            'description' => "Performed {$action} on {$count} users",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "{$count} users {$action}d successfully");
    }

    /**
     * Export users
     */
    public function exportUsers(Request $request)
    {
        // Fixed: Remove with('roleRelation') since it doesn't exist
        $users = User::all();

        $csv = "ID,Name,Email,Role,Status,Approved,Created At\n";

        foreach ($users as $user) {
            $csv .= "{$user->id},{$user->name},{$user->email},{$this->getRoleName($user->role)},";
            $csv .= ($user->is_active ? 'Active' : 'Inactive') . ",";
            $csv .= ($user->is_approved ? 'Yes' : 'No') . ",";
            $csv .= $user->created_at . "\n";
        }

        $filename = "users_export_" . date('Y-m-d_H-i-s') . ".csv";

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    // ==================== SYSTEM MANAGEMENT METHODS ====================

    /**
     * System settings
     */
    public function systemSettings()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        return view('ktvtc.superadmin.system.settings', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function updateSystemSettings(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'timezone' => 'required|timezone',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'email_verification' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'system_settings_updated',
            'description' => 'Updated system settings',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'System settings updated successfully');
    }

    /**
     * Database management
     */
    public function database()
    {
        $tables = DB::select('SHOW TABLES');
        $tableNames = array_map('current', $tables);

        $tableStats = [];
        foreach ($tableNames as $table) {
            $tableStats[$table] = [
                'rows' => DB::table($table)->count(),
                'size' => $this->getTableSize($table),
            ];
        }

        $backups = Storage::disk('backups')->files();

        return view('ktvtc.superadmin.system.database', compact('tableStats', 'backups'));
    }

    /**
     * Create database backup
     */
    public function createBackup(Request $request)
    {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';

        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_DATABASE'),
            storage_path('app/backups/' . $filename)
        );

        exec($command);

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'database_backup',
            'description' => 'Created database backup: ' . $filename,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Backup created successfully: ' . $filename);
    }

    /**
     * System logs
     */
    public function systemLogs(Request $request)
    {
        $query = SystemLog::with('user');

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $query->where('description', 'like', "%{$request->search}%");
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('ktvtc.superadmin.system.logs', compact('logs'));
    }

    /**
     * Clear system logs
     */
    public function clearLogs(Request $request)
    {
        $date = $request->input('date', '30');
        $daysAgo = Carbon::now()->subDays($date);

        SystemLog::where('created_at', '<', $daysAgo)->delete();

        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => 'logs_cleared',
            'description' => "Cleared logs older than {$date} days",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Logs older than {$date} days cleared successfully");
    }

    /**
     * System information
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'server_os' => php_uname(),
            'database_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'debug_mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'environment' => app()->environment(),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];

        return view('ktvtc.superadmin.system.info', compact('info'));
    }

    /**
     * Server monitoring
     */
    public function serverMonitor()
    {
        return view('ktvtc.superadmin.system.monitor');
    }

    /**
     * Get server stats (AJAX)
     */
    public function serverStats()
    {
        $stats = [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
            'load_average' => $this->getLoadAverage(),
        ];

        return response()->json($stats);
    }

    private function getCpuUsage()
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $load = [0, 0, 0];
                if (function_exists('sys_getloadavg')) {
                    $load = sys_getloadavg();
                }
            } else {
                $load = sys_getloadavg();
                if ($load === false) {
                    $load = [0, 0, 0];
                }
            }

            return [
                '1min' => round($load[0] ?? 0, 2),
                '5min' => round($load[1] ?? 0, 2),
                '15min' => round($load[2] ?? 0, 2)
            ];
        } catch (\Exception $e) {
            return [
                '1min' => 0,
                '5min' => 0,
                '15min' => 0
            ];
        }
    }

    // ==================== UTILITY METHODS ====================

    private function getDiskUsage()
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 2)
        ];
    }

    private function getMemoryUsage()
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $total = round(memory_get_usage(true) / 1024 / 1024, 2);
                $peak = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

                return [
                    'total' => $total . ' MB',
                    'used' => $total . ' MB',
                    'free' => 'N/A',
                    'percentage' => 0
                ];
            } else {
                $memory = shell_exec("free -m");
                if (empty($memory)) {
                    throw new \Exception('Unable to get memory usage');
                }

                $lines = explode("\n", $memory);
                if (count($lines) < 2) {
                    throw new \Exception('Invalid memory output format');
                }

                $values = preg_split('/\s+/', $lines[1]);
                if (count($values) < 4) {
                    throw new \Exception('Invalid memory data');
                }

                $total = $values[1] ?? 0;
                $used = $values[2] ?? 0;
                $free = $values[3] ?? 0;

                return [
                    'total' => $total . ' MB',
                    'used' => $used . ' MB',
                    'free' => $free . ' MB',
                    'percentage' => $total > 0 ? round(($used / $total) * 100, 2) : 0
                ];
            }
        } catch (\Exception $e) {
            return [
                'total' => 'N/A',
                'used' => 'N/A',
                'free' => 'N/A',
                'percentage' => 0
            ];
        }
    }

    private function getUptime()
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $bootTime = shell_exec('systeminfo | find "System Boot Time"');
                if ($bootTime) {
                    $bootTime = trim(str_replace('System Boot Time:', '', $bootTime));
                    $bootTimestamp = strtotime($bootTime);
                    $uptimeSeconds = time() - $bootTimestamp;

                    $days = floor($uptimeSeconds / 86400);
                    $hours = floor(($uptimeSeconds % 86400) / 3600);
                    $minutes = floor(($uptimeSeconds % 3600) / 60);

                    return "{$days}d {$hours}h {$minutes}m";
                }
            } else {
                $uptime = @file_get_contents('/proc/uptime');
                if ($uptime !== false) {
                    $uptime = explode(' ', $uptime)[0];
                    $days = floor($uptime / 86400);
                    $hours = floor(($uptime % 86400) / 3600);
                    $minutes = floor(($uptime % 3600) / 60);

                    return "{$days}d {$hours}h {$minutes}m";
                }
            }

            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function getLoadAverage()
    {
        try {
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                if ($load !== false) {
                    return implode(', ', array_map(fn($val) => round($val, 2), $load));
                }
            }
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function getTableSize($table)
    {
        $size = DB::select("SELECT
            round(((data_length + index_length) / 1024 / 1024), 2) as size_mb
            FROM information_schema.TABLES
            WHERE table_schema = ? AND table_name = ?",
            [env('DB_DATABASE'), $table]
        );

        return $size[0]->size_mb ?? 0 . ' MB';
    }

    private function getSystemAlerts()
    {
        $alerts = [];

        // Check disk space
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage['percentage'] > 90) {
            $alerts[] = [
                'type' => 'critical',
                'message' => "Disk space critically low ({$diskUsage['percentage']}% used)",
                'icon' => 'fa-hdd'
            ];
        } elseif ($diskUsage['percentage'] > 80) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Disk space running low ({$diskUsage['percentage']}% used)",
                'icon' => 'fa-hdd'
            ];
        }

        // Check error logs
        $errorCount = SystemLog::where('level', 'error')
            ->where('created_at', '>', now()->subDay())
            ->count();

        if ($errorCount > 100) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "High error rate: {$errorCount} errors in last 24 hours",
                'icon' => 'fa-exclamation-triangle'
            ];
        }

        // Check pending approvals
        $pendingCount = User::where('is_approved', false)->count();
        if ($pendingCount > 50) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$pendingCount} users pending approval",
                'icon' => 'fa-user-clock'
            ];
        }

        return $alerts;
    }

    private function getRoleName($roleId)
    {
        // Fixed: Changed 'Mschool' to 'Main School' for consistency
        $roles = [
            0 => 'Super Admin',
            1 => 'Main School',
            2 => 'Admin',
            3 => 'Scholarship',
            4 => 'Library',
            5 => 'Student',
            6 => 'Cafeteria',
            7 => 'Finance',
            8 => 'Trainers',
            9 => 'Website',
        ];

        return $roles[$roleId] ?? "Role {$roleId}";
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    // ==================== API METHODS ====================

    /**
     * Get notifications (AJAX)
     */
    public function getNotifications()
    {
        $notifications = SystemLog::where('level', '!=', 'info')
            ->orWhere(function ($query) {
                $query->whereIn('action', ['user_created', 'user_deleted', 'role_changed']);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('ktvtc.superadmin.partials.notifications', compact('notifications'));
    }

    /**
     * Mark notifications as read (AJAX)
     */
    public function markNotificationsRead(Request $request)
    {
        SystemLog::whereIn('id', $request->ids ?? [])->update(['read' => true]);
        return response()->json(['success' => true]);
    }
/**
 * Show user details
 */
public function showUser(User $user)
{
    // Load related data
    $user->load(['campus', 'shop', 'logs' => function($query) {
        $query->orderBy('created_at', 'desc')->limit(20);
    }]);

    // Get recent activities
    $recentActivities = SystemLog::where('user_id', $user->id)
        ->orWhere('description', 'like', "%{$user->email}%")
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

    return view('ktvtc.superadmin.users.show', compact('user', 'recentActivities'));
}
    /**
     * Get system alerts (AJAX)
     */
    public function getAlerts()
    {
        return response()->json($this->getSystemAlerts());
    }

    /**
     * Search users (AJAX)
     */
    public function searchUsers(Request $request)
    {
        $query = User::query();

        if ($request->has('q')) {
            $query->where('name', 'like', "%{$request->q}%")
                  ->orWhere('email', 'like', "%{$request->q}%");
        }

        $users = $query->limit(10)->get(['id', 'name', 'email']);

        // Fixed: Add role_name to each user
        $users->each(function ($user) {
            $user->role_name = $this->getRoleName($user->role);
        });

        return response()->json($users);
    }

    /**
     * System health check (AJAX)
     */
    public function systemHealth()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['healthy']);

        return response()->json([
            'healthy' => $allHealthy,
            'checks' => $checks,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['healthy' => true, 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkStorage()
    {
        try {
            Storage::disk('local')->put('healthcheck.txt', 'test');
            Storage::disk('local')->delete('healthcheck.txt');
            return ['healthy' => true, 'message' => 'Storage writable'];
        } catch (\Exception $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkCache()
    {
        try {
            cache()->put('healthcheck', 'test', 1);
            $value = cache()->get('healthcheck');
            cache()->forget('healthcheck');

            if ($value === 'test') {
                return ['healthy' => true, 'message' => 'Cache working'];
            }

            return ['healthy' => false, 'message' => 'Cache retrieval failed'];
        } catch (\Exception $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkQueue()
    {
        return ['healthy' => true, 'message' => 'Queue status unknown'];
    }
}
