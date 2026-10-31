{{-- Quick-action button for the dashboard header. primary: the main red button. --}}
@props(['href', 'icon' => null, 'primary' => false])
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition ' . ($primary ? 'bg-red-700 text-white hover:bg-red-800' : 'border border-gray-300 text-gray-700 hover:bg-gray-50')]) }}>
    @if($icon)<i class="fas {{ $icon }}"></i>@endif{{ $slot }}
</a>
