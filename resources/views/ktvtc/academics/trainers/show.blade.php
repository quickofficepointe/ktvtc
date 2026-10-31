@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', $trainer->name)
@section('subtitle', 'Trainer · ' . $mine->count() . ' unit(s)')

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')
<a href="{{ route('academics.trainers.index') }}" class="text-sm text-red-700 hover:underline">&larr; All trainers</a>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-3">
    <div class="space-y-6">
        <x-dash.panel title="Details">
            <form method="POST" action="{{ route('academics.trainers.update', $trainer) }}" class="space-y-3 text-sm">
                @csrf @method('PUT')
                <input name="name" value="{{ old('name', $trainer->name) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <input name="email" type="email" value="{{ old('email', $trainer->email) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <input name="phone_number" value="{{ old('phone_number', $trainer->phone_number) }}" placeholder="Phone" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <select name="is_active" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="1" @selected($trainer->is_active !== false)>Active</option>
                    <option value="0" @selected($trainer->is_active === false)>Inactive (left the college)</option>
                </select>
                <button class="w-full bg-gray-800 text-white rounded-lg py-2">Save</button>
            </form>
            <p class="text-xs text-gray-500 mt-3">Last sign-in: {{ $trainer->last_login_at ? $trainer->last_login_at->format('d M Y H:i') : 'never' }}</p>
            <form method="POST" action="{{ route('academics.trainers.send-login', $trainer) }}" class="mt-3"
                  onsubmit="return confirm('Set a new password for {{ $trainer->name }} and text it to {{ $trainer->phone_number }}?')">
                @csrf
                <button class="w-full border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50"><i class="fas fa-paper-plane mr-1"></i> Text new login details</button>
            </form>
        </x-dash.panel>

        <x-dash.panel title="Teaches now">
            <ul class="divide-y text-sm">
                @forelse($mine as $u)
                    <li class="py-2"><a href="{{ route('academics.results', ['unit_id' => $u->id, 'course_id' => $u->course_id]) }}" class="text-gray-900 hover:text-red-700">{{ $u->label }}</a>
                        <span class="block text-xs text-gray-500">{{ $u->course->name ?? '' }} · Term {{ $u->term_number }}</span></li>
                @empty
                    <li class="py-2 text-gray-500">No units yet.</li>
                @endforelse
            </ul>
        </x-dash.panel>
    </div>

    <x-dash.panel title="Assign units" subtitle="Tick the units this trainer teaches. Ticking a unit that has another trainer moves it to {{ $trainer->name }}." class="xl:col-span-2">
        <form method="POST" action="{{ route('academics.trainers.units', $trainer) }}">
            @csrf
            <div class="space-y-5 max-h-[34rem] overflow-y-auto pr-1">
                @forelse($courses as $course)
                    <div>
                        <p class="font-semibold text-gray-800 mb-1">{{ $course->name }}</p>
                        @foreach($course->units->groupBy('term_number') as $term => $units)
                            <p class="text-xs uppercase text-gray-500 mt-2 mb-1">Term {{ $term }}</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-1">
                                @foreach($units as $u)
                                    <label class="flex items-start gap-2 text-sm rounded-lg px-2 py-1.5 hover:bg-gray-50">
                                        <input type="checkbox" name="unit_ids[]" value="{{ $u->id }}" class="mt-1" @checked($u->trainer_id === $trainer->id)>
                                        <span>{{ $u->label }}
                                            @if($u->trainer && $u->trainer_id !== $trainer->id)<span class="block text-xs text-amber-700">now: {{ $u->trainer->name }}</span>
                                            @elseif(!$u->trainer)<span class="block text-xs text-red-700">no trainer</span>@endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No course has units yet. Add units under <a href="{{ route('academics.index') }}" class="underline">Courses &amp; units</a>.</p>
                @endforelse
            </div>
            @if($courses->isNotEmpty())
                <button class="mt-4 bg-red-700 hover:bg-red-800 text-white rounded-lg px-5 py-2 text-sm">Save units</button>
            @endif
        </form>
    </x-dash.panel>
</div>
</div>
@endsection
