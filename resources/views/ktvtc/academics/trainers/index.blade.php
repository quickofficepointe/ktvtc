@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', 'Trainers')
@section('subtitle', 'Every instructor, the units they teach and how their marks are going')

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-dash.kpi label="Trainers" :value="number_format($trainers->where('is_active', '!==', false)->count())" icon="fa-chalkboard-teacher" :hint="number_format($trainers->count()) . ' accounts in total'" />
    <x-dash.kpi label="Units without a trainer" :value="number_format($unassigned)" icon="fa-triangle-exclamation" :tone="$unassigned ? 'red' : 'green'" hint="Nobody can enter marks for these" :href="route('academics.index')" />
    <x-dash.kpi label="Marks in draft" :value="number_format($marks->sum('drafts'))" icon="fa-pen" tone="amber" hint="Entered but not submitted" />
    <x-dash.kpi label="Waiting for publishing" :value="number_format($marks->sum('submitted'))" icon="fa-check-double" tone="blue" :href="route('academics.review')" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <x-dash.panel title="Trainers" class="xl:col-span-2">
        <form method="GET" class="mb-3"><input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email or phone" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full md:w-72"></form>
        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs uppercase text-gray-500 border-b">
                    <th class="py-2 px-1">Trainer</th><th class="py-2 px-1 text-right">Units</th><th class="py-2 px-1 text-right">Draft</th><th class="py-2 px-1 text-right">Submitted</th><th class="py-2 px-1 text-right">Published</th><th class="py-2 px-1">Rating</th><th class="py-2 px-1">Last sign-in</th>
                </tr></thead>
                <tbody>
                    @forelse($trainers as $t)
                        @php $m = $marks[$t->id] ?? null; @endphp
                        <tr class="border-b last:border-0 {{ $t->is_active === false ? 'opacity-50' : '' }}">
                            <td class="py-2 px-1"><a href="{{ route('academics.trainers.show', $t) }}" class="font-medium text-gray-900 hover:text-red-700">{{ $t->name }}</a>
                                <span class="block text-xs text-gray-500">{{ $t->email }} {{ $t->phone_number ? '· ' . $t->phone_number : '' }}{{ $t->is_active === false ? ' · inactive' : '' }}</span></td>
                            <td class="py-2 px-1 text-right tabular-nums {{ ($units[$t->id] ?? 0) ? '' : 'text-amber-700' }}">{{ $units[$t->id] ?? 0 }}</td>
                            <td class="py-2 px-1 text-right tabular-nums">{{ (int) ($m->drafts ?? 0) }}</td>
                            <td class="py-2 px-1 text-right tabular-nums">{{ (int) ($m->submitted ?? 0) }}</td>
                            <td class="py-2 px-1 text-right tabular-nums text-green-700">{{ (int) ($m->published ?? 0) }}</td>
                            <td class="py-2 px-1">{{ $ratings[$t->id] !== null ? $ratings[$t->id] . ' / 5' : '—' }}</td>
                            <td class="py-2 px-1 text-xs text-gray-500 whitespace-nowrap">{{ $t->last_login_at ? $t->last_login_at->diffForHumans() : 'Never' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-gray-500">No trainer accounts yet. Add the first one on the right.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-dash.panel>

    <x-dash.panel title="Add a trainer" subtitle="Their login details are texted to them">
        <form method="POST" action="{{ route('academics.trainers.store') }}" class="space-y-3 text-sm">
            @csrf
            <input name="name" value="{{ old('name') }}" required placeholder="Full name" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            <input name="email" type="email" value="{{ old('email') }}" required placeholder="Email (used to sign in)" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            <input name="phone_number" value="{{ old('phone_number') }}" required placeholder="Phone, e.g. 0712345678" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            <select name="campus_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Campus (optional)</option>
                @foreach(\App\Models\Campus::orderBy('name')->get() as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
            <button class="w-full bg-red-700 hover:bg-red-800 text-white rounded-lg py-2"><i class="fas fa-user-plus mr-1"></i> Add trainer</button>
        </form>
        <p class="text-xs text-gray-500 mt-3">After adding, open the trainer to give them their units.</p>
    </x-dash.panel>
</div>
</div>
@endsection
