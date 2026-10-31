@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Server Monitor')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-3xl">
    <p class="text-sm text-gray-500 mb-2">Refreshes every 15 seconds.</p>
    <pre id="stats" class="text-xs bg-gray-50 rounded p-3">Loading…</pre>
</div>
<script>
    async function loadStats() {
        try {
            const r = await fetch('{{ route('super-admin.system.monitor.stats') }}', { headers: { 'Accept': 'application/json' } });
            document.getElementById('stats').textContent = JSON.stringify(await r.json(), null, 2);
        } catch (e) {
            document.getElementById('stats').textContent = 'Could not load server stats.';
        }
    }
    loadStats();
    setInterval(loadStats, 15000);
</script>
@endsection
