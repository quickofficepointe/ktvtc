{{-- A titled dashboard section. --}}
@props(['title', 'subtitle' => null, 'href' => null, 'linkText' => 'View all'])
<section {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 shadow-sm p-5']) }}>
    <div class="flex items-start justify-between gap-3 mb-4">
        <div>
            <h2 class="font-semibold text-gray-900">{{ $title }}</h2>
            @if($subtitle)<p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>@endif
        </div>
        @if($href)<a href="{{ $href }}" class="text-sm text-red-700 hover:underline whitespace-nowrap">{{ $linkText }} &rarr;</a>@endif
    </div>
    {{ $slot }}
</section>
