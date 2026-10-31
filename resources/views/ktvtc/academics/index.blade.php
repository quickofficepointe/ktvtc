@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', 'Academics')
@section('subtitle', 'Course tracks, units per term, trainers and results')

@section('header-actions')
@if(auth()->user()->canManageAcademics())
<div class="flex gap-2">
    <a href="{{ route('academics.review') }}" class="px-4 py-2 bg-primary text-white rounded-lg font-medium">
        <i class="fas fa-clipboard-check mr-1"></i> Review results
        @if($pending)<span class="ml-1 bg-white text-primary text-xs px-2 rounded-full">{{ $pending }}</span>@endif
    </a>
    <a href="{{ route('academics.evaluations') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-star mr-1"></i> Trainer evaluations</a>
    <a href="{{ route('academics.scales') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-sliders-h mr-1"></i> Grading scales</a>
</div>
@endif
@endsection

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')
<div class="space-y-6">
    @if($trainerCount === 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-900">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            There are no trainer accounts yet. Create users with the <strong>Trainers</strong> role in user management,
            then assign them to units here so they can enter marks.
        </div>
    @endif

    <form method="GET" class="bg-white rounded-xl border p-4 flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Course name" class="flex-1 border rounded-lg px-3 py-2 text-sm">
        <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Search</button>
    </form>

    <div class="bg-white rounded-xl border p-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 px-3">Course</th><th class="py-2 px-3">Track</th><th class="py-2 px-3">Terms</th>
                    <th class="py-2 px-3 text-right">Units</th><th class="py-2 px-3 text-right">Active students</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-3 font-medium">{{ $course->name }} <span class="text-xs text-gray-400">{{ $course->duration }}</span></td>
                        <td class="py-2 px-3">
                            @if($course->academic_track)
                                {{ \App\Models\GradingScale::TRACKS[$course->academic_track] }}
                            @else
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">Not set</span>
                            @endif
                        </td>
                        <td class="py-2 px-3">{{ $course->hasTermSetup() ? "{$course->term_count} × {$course->term_length} {$course->term_length_unit}" : '—' }}</td>
                        <td class="py-2 px-3 text-right">{{ $course->units_count ?: '—' }}</td>
                        <td class="py-2 px-3 text-right">{{ $course->active_enrollments_count }}</td>
                        <td class="py-2 px-3 text-right"><a href="{{ route('academics.course', $course) }}" class="px-3 py-1 bg-primary text-white rounded-lg text-xs">Set up</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
