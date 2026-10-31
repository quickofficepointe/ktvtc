@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', $course->name)
@section('subtitle', 'Track, terms and units')

@section('header-actions')
@if(auth()->user()->canManageAcademics())
<a href="{{ route('academics.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-arrow-left mr-1"></i> Courses</a>
@endif
@endsection

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')
<div class="space-y-6">

    <div class="bg-white rounded-xl border p-5">
        <h3 class="font-semibold text-gray-800 mb-3">Track and terms</h3>
        <form method="POST" action="{{ route('academics.course.update', $course) }}" class="flex flex-wrap items-end gap-3 text-sm">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs text-gray-500 mb-1">Academic track</label>
                <select name="academic_track" required class="border rounded-lg px-3 py-2">
                    <option value="">Choose…</option>
                    @foreach($tracks as $key => $label)
                        <option value="{{ $key }}" @selected(old('academic_track', $course->academic_track) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Number of terms</label>
                <input type="number" name="term_count" min="1" max="24" required value="{{ old('term_count', $course->term_count) }}" class="border rounded-lg px-3 py-2 w-24">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Each term lasts</label>
                <div class="flex gap-1">
                    <input type="number" name="term_length" min="1" max="52" required value="{{ old('term_length', $course->term_length) }}" class="border rounded-lg px-3 py-2 w-20">
                    <select name="term_length_unit" class="border rounded-lg px-3 py-2">
                        <option value="months" @selected(old('term_length_unit', $course->term_length_unit) === 'months')>months</option>
                        <option value="weeks" @selected(old('term_length_unit', $course->term_length_unit) === 'weeks')>weeks</option>
                    </select>
                </div>
            </div>
            <button class="px-4 py-2 bg-primary text-white rounded-lg">Save</button>
            <p class="text-xs text-gray-500 w-full">Advertised duration: {{ $course->duration ?? 'not given' }}. The same term split is used by Finance for term fees.</p>
        </form>
    </div>

    @if($course->hasTermSetup())
        @for($n = 1; $n <= $course->term_count; $n++)
            <div class="bg-white rounded-xl border p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Term {{ $n }} units</h3>
                @forelse($units[$n] ?? [] as $unit)
                    <form method="POST" action="{{ route('academics.units.update', $unit) }}" class="flex flex-wrap items-center gap-2 border-b py-2 text-sm {{ $unit->is_active ? '' : 'opacity-50' }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="term_number" value="{{ $unit->term_number }}">
                        <input type="text" name="code" value="{{ $unit->code }}" placeholder="Code" class="border rounded px-2 py-1 w-28">
                        <input type="text" name="title" value="{{ $unit->title }}" required class="border rounded px-2 py-1 flex-1 min-w-[200px]">
                        <select name="unit_type" class="border rounded px-2 py-1">
                            @foreach(\App\Models\CourseUnit::TYPES as $k => $label)
                                <option value="{{ $k }}" @selected($unit->unit_type === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="trainer_id" class="border rounded px-2 py-1">
                            <option value="">— No trainer —</option>
                            @foreach($trainers as $t)
                                <option value="{{ $t->id }}" @selected($unit->trainer_id == $t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <label class="flex items-center gap-1 text-xs"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($unit->is_active)> Active</label>
                        <button class="px-3 py-1 bg-gray-100 rounded text-xs">Save</button>
                        <span class="text-xs text-gray-400">{{ $unit->results_count }} result(s)</span>
                    </form>
                    @if($unit->results_count == 0)
                        <form method="POST" action="{{ route('academics.units.destroy', $unit) }}" class="text-right -mt-1" onsubmit="return confirm('Delete {{ addslashes($unit->title) }}?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">Delete</button>
                        </form>
                    @endif
                @empty
                    <p class="text-sm text-gray-500 mb-2">No units yet.</p>
                @endforelse

                <form method="POST" action="{{ route('academics.units.store', $course) }}" class="flex flex-wrap items-center gap-2 pt-3 text-sm">
                    @csrf
                    <input type="hidden" name="term_number" value="{{ $n }}">
                    <input type="text" name="code" placeholder="Code (optional)" class="border rounded px-2 py-1 w-32">
                    <input type="text" name="title" required placeholder="New unit title" class="border rounded px-2 py-1 flex-1 min-w-[200px]">
                    <select name="unit_type" class="border rounded px-2 py-1">
                        @foreach(\App\Models\CourseUnit::TYPES as $k => $label)
                            <option value="{{ $k }}" @selected($k === 'core')>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="trainer_id" class="border rounded px-2 py-1">
                        <option value="">— Trainer —</option>
                        @foreach($trainers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-1 bg-primary text-white rounded text-xs">Add unit</button>
                </form>
            </div>
        @endfor
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-900">Set the track and number of terms above to add units.</div>
    @endif
</div>
</div>
@endsection
