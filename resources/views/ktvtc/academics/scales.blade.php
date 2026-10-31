@extends(auth()->user()->canManageAcademics() ? 'ktvtc.academics.layout' : 'ktvtc.admin.layout.adminlayout')

@section('title', 'Grading Scales')
@section('subtitle', 'How scores become grades on each track')

@section('header-actions')
@if(auth()->user()->canManageAcademics())
<a href="{{ route('academics.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium"><i class="fas fa-arrow-left mr-1"></i> Academics</a>
@endif
@endsection

@section('content')
<div class="{{ auth()->user()->canManageAcademics() ? '' : 'academics-ro' }}">
@include('ktvtc.academics.partials.readonly')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    @foreach($scales as $scale)
        <form method="POST" action="{{ route('academics.scales.update', $scale) }}" class="bg-white rounded-xl border p-5 space-y-3 text-sm">
            @csrf @method('PUT')
            <h3 class="font-semibold text-gray-800">{{ $scale->name }}</h3>
            <div class="grid grid-cols-2 gap-2">
                <div><label class="block text-xs text-gray-500">First score</label><input name="component_a" value="{{ $scale->component_a }}" class="w-full border rounded px-2 py-1"></div>
                <div><label class="block text-xs text-gray-500">Second score</label><input name="component_b" value="{{ $scale->component_b }}" class="w-full border rounded px-2 py-1"></div>
            </div>
            <div>
                <label class="block text-xs text-gray-500">Weight of {{ $scale->component_a }} (%) — {{ $scale->component_b }} gets the rest</label>
                <input type="number" name="weight_a" min="0" max="100" value="{{ $scale->weight_a }}" class="w-24 border rounded px-2 py-1">
                <span class="text-xs text-gray-500">now {{ $scale->weight_a }} / {{ $scale->weight_b }}</span>
            </div>
            <table class="w-full">
                <thead><tr class="text-left text-xs text-gray-500"><th>From score</th><th>Grade</th><th>Pass?</th></tr></thead>
                <tbody>
                    @foreach($scale->bands as $i => $band)
                        <tr>
                            <td class="py-1 pr-2"><input type="number" step="0.01" name="bands[{{ $i }}][min]" value="{{ $band['min'] }}" class="w-20 border rounded px-2 py-1"></td>
                            <td class="py-1 pr-2"><input name="bands[{{ $i }}][label]" value="{{ $band['label'] }}" class="w-full border rounded px-2 py-1"></td>
                            <td class="py-1"><input type="hidden" name="bands[{{ $i }}][passed]" value="0"><input type="checkbox" name="bands[{{ $i }}][passed]" value="1" @checked($band['passed'])></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button class="px-4 py-2 bg-primary text-white rounded-lg">Save {{ $scale->name }}</button>
        </form>
    @endforeach
</div>
</div>
@endsection
