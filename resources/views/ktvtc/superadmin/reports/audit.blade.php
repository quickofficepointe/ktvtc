@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Audit Trail')
@section('subtitle', 'Logged system actions')

@section('content')
<div class="space-y-4">
    <form method="GET" class="bg-white rounded-xl shadow-sm border p-4 flex flex-wrap items-end gap-3 text-sm">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Action</label>
            <select name="action" class="border rounded-lg px-3 py-2">
                <option value="">All</option>
                @foreach($actions as $a)<option value="{{ $a }}" @selected(request('action') === $a)>{{ str_replace('_', ' ', $a) }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Level</label>
            <select name="level" class="border rounded-lg px-3 py-2">
                <option value="">All</option>
                @foreach(['info', 'warning', 'error', 'critical'] as $l)<option value="{{ $l }}" @selected(request('level') === $l)>{{ ucfirst($l) }}</option>@endforeach
            </select>
        </div>
        <div><label class="block text-xs text-gray-500 mb-1">From</label><input type="date" name="from" value="{{ request('from') }}" class="border rounded-lg px-3 py-2"></div>
        <div><label class="block text-xs text-gray-500 mb-1">To</label><input type="date" name="to" value="{{ request('to') }}" class="border rounded-lg px-3 py-2"></div>
        <button class="px-4 py-2 bg-gray-800 text-white rounded-lg">Filter</button>
    </form>
    @include('ktvtc.superadmin.partials.log-table', ['logs' => $logs])
</div>
@endsection
