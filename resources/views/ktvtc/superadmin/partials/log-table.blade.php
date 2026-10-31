<div class="bg-white rounded-xl shadow-sm border p-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 px-3">When</th><th class="py-2 px-3">User</th><th class="py-2 px-3">Action</th><th class="py-2 px-3">Level</th><th class="py-2 px-3">Details</th><th class="py-2 px-3">IP</th></tr></thead>
        <tbody>
            @forelse($logs as $log)
                <tr class="border-b align-top">
                    <td class="py-2 px-3 whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i') }}</td>
                    <td class="py-2 px-3">{{ $log->user->name ?? 'System' }}</td>
                    <td class="py-2 px-3">{{ str_replace('_', ' ', $log->action) }}</td>
                    <td class="py-2 px-3">
                        <span class="px-2 py-0.5 rounded text-xs {{ in_array($log->level, ['error', 'critical']) ? 'bg-red-100 text-red-700' : ($log->level === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700') }}">{{ $log->level }}</span>
                    </td>
                    <td class="py-2 px-3">{{ $log->description }}</td>
                    <td class="py-2 px-3 text-xs text-gray-500">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-6 text-center text-gray-500">Nothing logged yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if(method_exists($logs, 'links'))<div class="mt-3">{{ $logs->links() }}</div>@endif
</div>
