@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', 'Review Results')
@section('subtitle', 'Results submitted by trainers, waiting to be published')

@section('header-actions')
@if(auth()->user()->canManageAcademics())
<a href="{{ route('academics.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-arrow-left mr-1"></i> Academics</a>
@endif
@endsection

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')
<div class="bg-white rounded-xl border p-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2 px-3">Unit</th><th class="py-2 px-3">Course / term</th><th class="py-2 px-3">Trainer</th>
                <th class="py-2 px-3 text-right">To publish</th><th class="py-2 px-3 text-right">Published</th><th class="py-2 px-3 text-right">Trainer drafts</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($units as $unit)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 px-3 font-medium">{{ $unit->label }}</td>
                    <td class="py-2 px-3">{{ $unit->course->name }} · Term {{ $unit->term_number }}</td>
                    <td class="py-2 px-3">{{ $unit->trainer->name ?? '—' }}</td>
                    <td class="py-2 px-3 text-right">
                        @if($unit->submitted_count)<span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded font-semibold">{{ $unit->submitted_count }}</span>@else 0 @endif
                    </td>
                    <td class="py-2 px-3 text-right">{{ $unit->published_count }}</td>
                    <td class="py-2 px-3 text-right text-gray-500">{{ $unit->draft_count }}</td>
                    <td class="py-2 px-3 text-right"><a href="{{ route('academics.review.unit', $unit) }}" class="px-3 py-1 bg-primary text-white rounded-lg text-xs">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-gray-500">No results have been submitted yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@endsection
