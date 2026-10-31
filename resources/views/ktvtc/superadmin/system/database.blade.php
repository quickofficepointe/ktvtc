@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Database')

@section('header-actions')
<form method="POST" action="{{ route('super-admin.system.backup') }}" onsubmit="return confirm('Create a full database backup now?')">
    @csrf
    <button class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm"><i class="fas fa-database mr-1"></i> Back up now</button>
</form>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl shadow-sm border p-4 lg:col-span-2 overflow-x-auto">
        <h3 class="font-semibold text-gray-800 mb-3">Tables ({{ count($tableStats) }})</h3>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Table</th><th class="py-2 text-right">Rows</th><th class="py-2 text-right">Size</th></tr></thead>
            <tbody>
                @foreach(collect($tableStats)->sortByDesc('rows') as $name => $s)
                    <tr class="border-b">
                        <td class="py-1.5 font-mono text-xs">{{ $name }}</td>
                        <td class="py-1.5 text-right">{{ number_format($s['rows']) }}</td>
                        <td class="py-1.5 text-right text-gray-500">{{ $s['size'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="bg-white rounded-xl shadow-sm border p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Backups on the server</h3>
        @forelse(collect($backups)->sortDesc() as $file)
            <p class="text-sm font-mono border-b py-1">{{ basename($file) }}</p>
        @empty
            <p class="text-sm text-gray-500">No backups yet.</p>
        @endforelse
    </div>
</div>
@endsection
