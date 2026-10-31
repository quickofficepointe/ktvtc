{{--
    Chart.js chart. type: bar | line | doughnut. datasets: [['label' => ..., 'data' => [...], 'color' => '#b91c1c']]
    Money axes are labelled in KES. Loads Chart.js once per page, so it works in every layout.
--}}
@props(['type' => 'bar', 'labels' => [], 'datasets' => [], 'height' => 260, 'stacked' => false, 'money' => true])
@php $id = 'dash-chart-' . \Illuminate\Support\Str::random(8); @endphp
@once
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endonce
<div style="height: {{ $height }}px" class="relative"><canvas id="{{ $id }}"></canvas></div>
<script>
(function () {
    const datasets = @json($datasets);
    const type = @json($type);
    const money = @json($money);
    const fmt = v => (money ? 'KES ' : '') + Number(v).toLocaleString('en-KE', { maximumFractionDigits: 0 });
    const round = type === 'doughnut';
    new Chart(document.getElementById(@json($id)), {
        type,
        data: {
            labels: @json($labels),
            datasets: datasets.map(d => ({
                label: d.label,
                data: d.data,
                backgroundColor: round ? d.colors : d.color,
                borderColor: type === 'line' ? d.color : (round ? '#fff' : d.color),
                borderWidth: type === 'line' ? 2 : (round ? 2 : 0),
                borderRadius: type === 'bar' ? 4 : 0,
                tension: 0.3,
                fill: false,
                pointRadius: type === 'line' ? 3 : 0,
            })),
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: round ? 'nearest' : 'index', intersect: round },
            plugins: {
                legend: { display: round || datasets.length > 1, position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } },
                tooltip: { callbacks: { label: c => ' ' + (c.dataset.label && !round ? c.dataset.label + ': ' : (round ? c.label + ': ' : '')) + fmt(c.parsed.y ?? c.parsed) } },
            },
            cutout: round ? '62%' : undefined,
            scales: round ? {} : {
                x: { stacked: @json($stacked), grid: { display: false } },
                y: { stacked: @json($stacked), beginAtZero: true, ticks: { callback: v => fmt(v), maxTicksLimit: 6 }, grid: { color: '#f1f1ef' } },
            },
        },
    });
})();
</script>
