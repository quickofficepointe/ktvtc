@extends('ktvtc.trainers.layout.trainerlayout')

@section('title', 'My Units')

@section('content')
<div class="bg-white rounded-lg shadow-sm border p-5">
    <h2 class="text-lg font-bold text-gray-800 mb-1">My units</h2>
    <p class="text-sm text-gray-500 mb-4">Enter marks, save drafts as you go, then submit. Students see results once the academic office publishes them.</p>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 px-3">Unit</th><th class="py-2 px-3">Course</th><th class="py-2 px-3">Term</th>
                    <th class="py-2 px-3 text-right">Drafts</th><th class="py-2 px-3 text-right">Submitted</th><th class="py-2 px-3 text-right">Published</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-3 font-medium">{{ $unit->label }}</td>
                        <td class="py-2 px-3">{{ $unit->course->name }}</td>
                        <td class="py-2 px-3">Term {{ $unit->term_number }}</td>
                        <td class="py-2 px-3 text-right">{{ $unit->draft_count }}</td>
                        <td class="py-2 px-3 text-right">{{ $unit->submitted_count }}</td>
                        <td class="py-2 px-3 text-right">{{ $unit->published_count }}</td>
                        <td class="py-2 px-3 text-right"><a href="{{ route('trainers.results.unit', $unit) }}" class="px-3 py-1 bg-primary text-white rounded text-xs">Enter marks</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-gray-500">No units are assigned to you yet. The academic office assigns trainers to units.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
