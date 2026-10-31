@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Activity Report')
@section('subtitle', 'Who used the system in the last ' . $days . ' days')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-xl shadow-sm border p-4 flex gap-2 text-sm">
        @foreach([7, 30, 90] as $d)
            <a href="?days={{ $d }}" class="px-3 py-2 rounded-lg border {{ $days == $d ? 'bg-gray-800 text-white' : '' }}">Last {{ $d }} days</a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Users by role</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Role</th><th class="py-2 text-right">Users</th><th class="py-2 text-right">Logged in during period</th></tr></thead>
            <tbody>
                @foreach(\App\Models\User::getRoles() as $id => $name)
                    <tr class="border-b"><td class="py-2">{{ $name }}</td><td class="py-2 text-right">{{ $byRole[$id]->users ?? 0 }}</td><td class="py-2 text-right">{{ $byRole[$id]->active ?? 0 }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Most recent logins</h3>
        <table class="w-full text-sm">
            <tbody>
                @forelse($recent as $u)
                    <tr class="border-b">
                        <td class="py-2">{{ $u->name }} <span class="text-xs text-gray-400">{{ $u->email }}</span></td>
                        <td class="py-2">{{ $u->role_name }}</td>
                        <td class="py-2 text-right whitespace-nowrap">{{ $u->last_login_at?->format('d M Y H:i') }}</td>
                        <td class="py-2 text-right text-xs text-gray-500">{{ $u->last_login_ip }}</td>
                    </tr>
                @empty
                    <tr><td class="py-4 text-center text-gray-500">No logins recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Logged actions per day</h3>
        @php $max = max(1, $actions->max() ?? 1); @endphp
        @forelse($actions as $day => $n)
            <div class="flex items-center gap-2 text-xs mb-1">
                <span class="w-24 text-gray-500">{{ \Carbon\Carbon::parse($day)->format('d M') }}</span>
                <div class="flex-1 bg-gray-100 rounded h-3"><div class="bg-blue-500 h-3 rounded" style="width: {{ $n / $max * 100 }}%"></div></div>
                <span class="w-10 text-right">{{ $n }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No actions logged in this period.</p>
        @endforelse
    </div>
</div>
@endsection
