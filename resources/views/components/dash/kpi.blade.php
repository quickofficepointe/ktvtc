{{-- One headline figure. tone: brand | green | amber | blue | red | gray --}}
@props(['label', 'value', 'hint' => null, 'tone' => 'brand', 'icon' => null, 'href' => null, 'progress' => null])
@php
    $tones = [
        'brand' => ['text-red-700', 'bg-red-50 text-red-700', 'bg-red-600'],
        'green' => ['text-green-700', 'bg-green-50 text-green-700', 'bg-green-600'],
        'amber' => ['text-amber-700', 'bg-amber-50 text-amber-700', 'bg-amber-500'],
        'blue'  => ['text-blue-700', 'bg-blue-50 text-blue-700', 'bg-blue-600'],
        'red'   => ['text-red-600', 'bg-red-50 text-red-600', 'bg-red-500'],
        'gray'  => ['text-gray-800', 'bg-gray-100 text-gray-600', 'bg-gray-500'],
    ];
    [$valueClass, $iconClass, $barClass] = $tones[$tone] ?? $tones['brand'];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'block bg-white rounded-xl border border-gray-200 shadow-sm p-4' . ($href ? ' hover:border-red-300 hover:shadow transition' : '')]) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
        @if($icon)<span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $iconClass }}"><i class="fas {{ $icon }} text-sm"></i></span>@endif
    </div>
    <p class="mt-1 text-2xl font-bold tabular-nums {{ $valueClass }}">{{ $value }}</p>
    @if(!is_null($progress))
        <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full {{ $barClass }}" style="width: {{ max(0, min(100, $progress)) }}%"></div></div>
    @endif
    @if($hint)<p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>@endif
</{{ $tag }}>
