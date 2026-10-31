@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Dashboard')
@section('subtitle', 'System overview')

@section('content')
<x-dash.header title="System dashboard" subtitle="All modules">
    <x-slot name="actions">
        <x-dash.action :href="route('super-admin.reports.audit')" icon="fa-clipboard-list">Audit trail</x-dash.action>
        <x-dash.action :href="route('super-admin.system.database')" icon="fa-database">Backups</x-dash.action>
        <x-dash.action :href="route('super-admin.users.create')" icon="fa-user-plus" primary>Add user</x-dash.action>
    </x-slot>
</x-dash.header>

@foreach($alerts as $alert)
    <div class="mb-3 rounded-xl border px-4 py-3 text-sm {{ ($alert['type'] ?? '') === 'critical' ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
        <i class="fas fa-triangle-exclamation mr-1"></i> {{ $alert['message'] ?? '' }}
    </div>
@endforeach

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="User accounts" :value="number_format($users)" icon="fa-users" :hint="number_format($active) . ' active'" :href="route('super-admin.users.index')" />
    <x-dash.kpi label="Waiting for approval" :value="number_format($awaiting)" icon="fa-user-clock" :tone="$awaiting ? 'amber' : 'green'" hint="Accounts that cannot sign in yet" :href="route('super-admin.users.index')" />
    <x-dash.kpi label="Sign-ins today" :value="number_format($loginsToday)" icon="fa-right-to-bracket" tone="blue" hint="Successful logins" :href="route('super-admin.reports.audit', ['action' => 'login'])" />
    <x-dash.kpi label="Errors today" :value="number_format($errorsToday)" icon="fa-bug" :tone="$errorsToday ? 'red' : 'green'" hint="Logged errors and critical events" :href="route('super-admin.reports.audit', ['level' => 'error'])" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <x-dash.panel title="Sign-ins, last 14 days" class="xl:col-span-2">
        <x-dash.chart type="line" :money="false" :labels="$loginTrend['labels']" :datasets="[['label' => 'Sign-ins', 'data' => $loginTrend['data'], 'color' => '#b91c1c']]" />
    </x-dash.panel>
    <x-dash.panel title="Accounts by role" :href="route('super-admin.roles.index')" link-text="Roles">
        <x-dash.bars :rows="$byRole" :money="false" tone="bg-blue-600" />
    </x-dash.panel>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <x-dash.panel title="Recent activity" class="lg:col-span-2" :href="route('super-admin.reports.audit')">
        <ul class="divide-y text-sm">
            @forelse($recent as $log)
                <li class="py-2 flex justify-between gap-4">
                    <span><span class="font-medium text-gray-900">{{ $log->user_name ?? 'System' }}</span> <span class="text-gray-600">{{ $log->description }}</span></span>
                    <span class="text-xs text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
                </li>
            @empty
                <li class="py-2 text-gray-500">Nothing logged yet.</li>
            @endforelse
        </ul>
    </x-dash.panel>
    <x-dash.panel title="Waiting for approval" :href="route('super-admin.users.index')">
        <ul class="divide-y text-sm">
            @forelse($awaitingUsers as $u)
                <li class="py-2"><a href="{{ route('super-admin.users.show', $u) }}" class="font-medium text-gray-900 hover:text-red-700">{{ $u->name }}</a>
                    <span class="block text-xs text-gray-500">{{ $u->role_name }} · {{ $u->created_at?->format('d M Y') }}</span></li>
            @empty
                <li class="py-2 text-gray-500">No one is waiting.</li>
            @endforelse
        </ul>
    </x-dash.panel>
</div>
@endsection
