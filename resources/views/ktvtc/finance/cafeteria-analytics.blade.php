@extends('ktvtc.finance.layouts.app')

@section('title', 'Cafeteria Analysis')
@section('subtitle', $from->format('d M Y') . ' – ' . $to->format('d M Y'))

@section('breadcrumb')
<li><span class="mx-2">/</span><span class="text-gray-400">Cafeteria analysis</span></li>
@endsection

@php
    $kes = fn ($v) => 'KES ' . number_format((float) $v, 2);
    $methodLabel = fn ($m) => match ($m) { 'mpesa' => 'M-Pesa', 'mpesa_manual' => 'M-Pesa (manual)', 'cash' => 'Cash', 'credit' => 'Credit', default => ucfirst((string) $m) };
@endphp

@section('content')
<div class="space-y-6">

    <form method="GET" class="finance-card p-4 flex flex-wrap items-end gap-3 text-sm">
        <div><label class="block text-xs text-gray-500 mb-1">From</label><input type="date" name="from" value="{{ $from->toDateString() }}" class="border rounded-lg px-3 py-2"></div>
        <div><label class="block text-xs text-gray-500 mb-1">To</label><input type="date" name="to" value="{{ $to->toDateString() }}" class="border rounded-lg px-3 py-2"></div>
        <button class="btn-primary text-white px-4 py-2 rounded-lg"><i class="fas fa-filter mr-1"></i>Apply</button>
        <div class="flex gap-2 ml-auto">
            <a class="border rounded-lg px-3 py-2" href="?from={{ today()->toDateString() }}&to={{ today()->toDateString() }}">Today</a>
            <a class="border rounded-lg px-3 py-2" href="?from={{ now()->startOfWeek()->toDateString() }}&to={{ today()->toDateString() }}">This week</a>
            <a class="border rounded-lg px-3 py-2" href="?from={{ now()->startOfMonth()->toDateString() }}&to={{ today()->toDateString() }}">This month</a>
        </div>
    </form>

    {{-- Headline numbers --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="finance-card p-4 bg-green-50 border-green-200">
            <p class="text-xs text-gray-600">Canteen top-ups</p>
            <p class="text-2xl font-bold text-green-700">{{ $kes($topups['canteen']) }}</p>
            <p class="text-xs text-gray-500">Transport top-ups: {{ $kes($topups['transport']) }}</p>
        </div>
        <div class="finance-card p-4 bg-blue-50 border-blue-200">
            <p class="text-xs text-gray-600">Spent with cards</p>
            <p class="text-2xl font-bold text-blue-700">{{ $kes($cardSpend['total']) }}</p>
            <p class="text-xs text-gray-500">{{ number_format($cardSpend['count']) }} purchases · {{ number_format($cardSpend['buyers']) }} students · avg {{ $kes($cardSpend['average']) }}</p>
        </div>
        <div class="finance-card p-4 bg-purple-50 border-purple-200">
            <p class="text-xs text-gray-600">POS / online sales paid</p>
            <p class="text-2xl font-bold text-purple-700">{{ $kes($salesByMethod->sum('total')) }}</p>
            <p class="text-xs text-gray-500">{{ number_format($salesByMethod->sum('n')) }} sales</p>
        </div>
        <div class="finance-card p-4 bg-yellow-50 border-yellow-200">
            <p class="text-xs text-gray-600">Held on cards now</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $kes($held['canteen'] + $held['transport'] + $held['unsplit']) }}</p>
            <p class="text-xs text-gray-500">Canteen {{ $kes($held['canteen']) }} · Transport {{ $kes($held['transport']) }}</p>
            @if($held['unsplit'] > 0)<p class="text-xs text-gray-500">Not yet split: {{ $kes($held['unsplit']) }}</p>@endif
        </div>
    </div>

    {{-- Daily trend --}}
    <div class="finance-card p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Daily sales</h3>
        <div style="height: 260px"><canvas id="dailyChart"></canvas></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- By class --}}
        <div class="finance-card p-4">
            <h3 class="font-semibold text-gray-800 mb-3">Card spending by class</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Class</th><th class="py-2 text-right">Students</th><th class="py-2 text-right">Purchases</th><th class="py-2 text-right">Spent</th><th class="py-2 text-right">Per student</th></tr></thead>
                <tbody>
                    @forelse($byClass as $c)
                        <tr class="border-b">
                            <td class="py-2">{{ $c->class }}</td>
                            <td class="py-2 text-right">{{ $c->students }}</td>
                            <td class="py-2 text-right">{{ number_format($c->n) }}</td>
                            <td class="py-2 text-right font-semibold">{{ number_format($c->total, 2) }}</td>
                            <td class="py-2 text-right">{{ number_format($c->students ? $c->total / $c->students : 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-center text-gray-500">No card purchases in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top students --}}
        <div class="finance-card p-4">
            <h3 class="font-semibold text-gray-800 mb-3">Top spenders</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Student</th><th class="py-2 text-right">Purchases</th><th class="py-2 text-right">Spent</th><th class="py-2 text-right">Canteen left</th></tr></thead>
                <tbody>
                    @forelse($topStudents as $s)
                        <tr class="border-b">
                            <td class="py-2"><a href="{{ route('cards.show', $s->id) }}" class="hover:text-primary">{{ $s->student_name }}</a> <span class="text-xs text-gray-400">{{ $s->student_class }}</span></td>
                            <td class="py-2 text-right">{{ number_format($s->n) }}</td>
                            <td class="py-2 text-right font-semibold">{{ number_format($s->total, 2) }}</td>
                            <td class="py-2 text-right">{{ number_format($s->canteen_balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-gray-500">No card purchases in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Products via card --}}
        <div class="finance-card p-4">
            <h3 class="font-semibold text-gray-800 mb-3">What students buy with cards</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Product</th><th class="py-2 text-right">Qty</th><th class="py-2 text-right">Amount</th></tr></thead>
                <tbody>
                    @forelse($cardProducts as $name => $p)
                        <tr class="border-b"><td class="py-2">{{ $name }}</td><td class="py-2 text-right">{{ number_format($p['qty']) }}</td><td class="py-2 text-right">{{ number_format($p['total'], 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-500">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- POS --}}
        <div class="finance-card p-4 space-y-4">
            <div>
                <h3 class="font-semibold text-gray-800 mb-3">POS / online sales by payment method</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Method</th><th class="py-2">Channel</th><th class="py-2 text-right">Sales</th><th class="py-2 text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($salesByMethod as $m)
                            <tr class="border-b"><td class="py-2">{{ $methodLabel($m->payment_method) }}</td><td class="py-2">{{ str_replace('_', ' ', $m->channel) }}</td><td class="py-2 text-right">{{ number_format($m->n) }}</td><td class="py-2 text-right">{{ number_format($m->total, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-500">No paid sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800 mb-3">Top POS products</h3>
                <table class="w-full text-sm">
                    <tbody>
                        @forelse($salesProducts as $p)
                            <tr class="border-b"><td class="py-2">{{ $p->product_name }}</td><td class="py-2 text-right">{{ number_format($p->qty) }}</td><td class="py-2 text-right">{{ number_format($p->total, 2) }}</td></tr>
                        @empty
                            <tr><td class="py-4 text-center text-gray-500">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="finance-card p-4">
            <h3 class="font-semibold text-gray-800 mb-3">Busy hours (card purchases)</h3>
            @php $maxHour = max(1, $byHour->max('total') ?? 1); @endphp
            @forelse($byHour as $h => $row)
                <div class="flex items-center gap-2 text-xs mb-1">
                    <span class="w-12 text-gray-500">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</span>
                    <div class="flex-1 bg-gray-100 rounded h-4"><div class="bg-blue-500 h-4 rounded" style="width: {{ $row->total / $maxHour * 100 }}%"></div></div>
                    <span class="w-28 text-right">{{ number_format($row->total, 0) }} ({{ $row->n }})</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No data.</p>
            @endforelse
        </div>
        <div class="finance-card p-4 text-sm space-y-2">
            <h3 class="font-semibold text-gray-800 mb-1">Transport in this period</h3>
            <p>Topped up: <strong>{{ $kes($topups['transport']) }}</strong></p>
            <p>Cash fare given: <strong>{{ $kes($fareGiven['cash']) }}</strong> + withdrawal charges {{ $kes($fareGiven['fees']) }}</p>
            <p>Transport money still held: <strong>{{ $kes($held['transport']) }}</strong></p>
            <p class="text-gray-500">Cards below their low-balance alert (canteen): {{ $held['low'] }}</p>
            <a href="{{ route('finance.transport.index') }}" class="text-primary hover:underline">Open transport →</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: @json($daily['labels']),
            datasets: [
                { label: 'Card purchases', data: @json($daily['card']), backgroundColor: '#3B82F6' },
                { label: 'POS / online paid', data: @json($daily['sales']), backgroundColor: '#A855F7' },
            ],
        },
        options: { maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } },
    });
</script>
@endpush
