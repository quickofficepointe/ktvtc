{{-- Ranked horizontal bars. rows: [['label' => ..., 'value' => number, 'note' => optional text]] --}}
@props(['rows', 'money' => true, 'tone' => 'bg-red-600', 'empty' => 'Nothing to show yet.'])
@php $max = max(1, collect($rows)->max('value') ?? 1); @endphp
@forelse($rows as $row)
    <div class="mb-3 last:mb-0">
        <div class="flex justify-between gap-3 text-sm">
            <span class="text-gray-700 truncate">{{ $row['label'] }}</span>
            <span class="font-semibold tabular-nums text-gray-900 whitespace-nowrap">{{ $money ? 'KES ' . number_format($row['value']) : number_format($row['value']) }}</span>
        </div>
        <div class="mt-1 h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full {{ $tone }}" style="width: {{ $row['value'] / $max * 100 }}%"></div></div>
        @if(!empty($row['note']))<p class="text-xs text-gray-500 mt-0.5">{{ $row['note'] }}</p>@endif
    </div>
@empty
    <p class="text-sm text-gray-500">{{ $empty }}</p>
@endforelse
