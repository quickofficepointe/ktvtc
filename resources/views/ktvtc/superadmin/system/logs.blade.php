@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'System Logs')

@section('header-actions')
<form method="POST" action="{{ route('super-admin.system.clear-logs') }}" onsubmit="return confirm('Delete logs older than 30 days?')">
    @csrf
    @method('DELETE')
    <input type="hidden" name="date" value="30">
    <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm">Clear logs older than 30 days</button>
</form>
@endsection

@section('content')
<div class="space-y-4">
    <form method="GET" class="bg-white rounded-xl shadow-sm border p-4 flex flex-wrap gap-3 text-sm">
        <select name="level" class="border rounded-lg px-3 py-2">
            <option value="">All levels</option>
            @foreach(['info', 'warning', 'error', 'critical'] as $l)<option value="{{ $l }}" @selected(request('level') === $l)>{{ ucfirst($l) }}</option>@endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search details" class="flex-1 border rounded-lg px-3 py-2">
        <button class="px-4 py-2 bg-gray-800 text-white rounded-lg">Filter</button>
    </form>
    @include('ktvtc.superadmin.partials.log-table', ['logs' => $logs])
</div>
@endsection
