@extends('ktvtc.finance.layouts.app')

@section('title', $course->name)
@section('subtitle', 'Terms and term fees')

@section('breadcrumb')
<li><span class="mx-2">/</span><a href="{{ route('finance.fee-structure.index') }}" class="text-gray-600 hover:text-primary">Term fees</a></li>
<li><span class="mx-2">/</span><span class="text-gray-400">{{ $course->name }}</span></li>
@endsection

@section('content')
<div class="space-y-6">

    {{-- 1. Term structure --}}
    <div class="finance-card p-5">
        <h3 class="font-semibold text-gray-800 mb-1">1. How the course is split into terms</h3>
        <p class="text-sm text-gray-500 mb-4">
            Advertised duration: {{ $course->duration ?? 'not given' }}.
            Each student's Term 1 starts on their own intake date.
        </p>
        <form method="POST" action="{{ route('finance.fee-structure.structure', $course) }}" class="flex flex-wrap items-end gap-3 text-sm">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs text-gray-500 mb-1">Number of terms</label>
                <input type="number" name="term_count" min="{{ max(1, $maxRegistered) }}" max="24" required value="{{ old('term_count', $course->term_count) }}" class="border rounded-lg px-3 py-2 w-28">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Each term lasts</label>
                <div class="flex gap-1">
                    <input type="number" name="term_length" min="1" max="52" required value="{{ old('term_length', $course->term_length) }}" class="border rounded-lg px-3 py-2 w-20" @disabled($maxRegistered > 0)>
                    <select name="term_length_unit" class="border rounded-lg px-3 py-2" @disabled($maxRegistered > 0)>
                        <option value="months" @selected(old('term_length_unit', $course->term_length_unit) === 'months')>months</option>
                        <option value="weeks" @selected(old('term_length_unit', $course->term_length_unit) === 'weeks')>weeks</option>
                    </select>
                    @if($maxRegistered > 0)
                        <input type="hidden" name="term_length" value="{{ $course->term_length }}">
                        <input type="hidden" name="term_length_unit" value="{{ $course->term_length_unit }}">
                    @endif
                </div>
            </div>
            <button class="btn-primary text-white px-4 py-2 rounded-lg">Save</button>
            @if($maxRegistered > 0)
                <p class="text-xs text-gray-500 w-full">Term length is locked because students are registered (up to Term {{ $maxRegistered }}).</p>
            @endif
        </form>
    </div>

    @if($course->hasTermSetup())
    {{-- 2. Fees --}}
    <div class="finance-card p-5">
        <h3 class="font-semibold text-gray-800 mb-1">2. Fees per term</h3>
        <p class="text-sm text-gray-500 mb-4">
            A fee version applies to students whose intake is on or after its date. To change fees for new intakes,
            add a new version with a later date: students already admitted keep what they joined on.
            Existing students on a whole-course fee are not affected.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
            @for($n = 1; $n <= $course->term_count; $n++)
                <div class="border rounded-lg p-3">
                    <p class="font-semibold mb-2">Term {{ $n }}</p>
                    @forelse($versions[$n] ?? [] as $v)
                        <div class="border-t py-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">From intakes {{ $v->effective_from->format('d M Y') }}</span>
                                <strong>KES {{ number_format($v->total, 2) }}</strong>
                            </div>
                            <ul class="text-xs text-gray-600 mt-1">
                                @foreach($v->items as $item)
                                    <li class="flex justify-between"><span>{{ $item['name'] }}</span><span>{{ number_format($item['amount'], 2) }}</span></li>
                                @endforeach
                            </ul>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-gray-400">{{ $v->registrations_count }} student(s) charged</span>
                                <div class="flex gap-2">
                                    <button type="button" class="text-xs text-blue-600 hover:underline"
                                            onclick='copyVersion({{ $n }}, @json($v->items))'>Copy</button>
                                    @if($v->registrations_count == 0)
                                        <form method="POST" action="{{ route('finance.fee-structure.fee.destroy', $v) }}" onsubmit="return confirm('Delete this fee version?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs text-red-600 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-yellow-700 bg-yellow-50 rounded px-2 py-1">No fees set: students can't register for this term.</p>
                    @endforelse
                </div>
            @endfor
        </div>

        <form method="POST" action="{{ route('finance.fee-structure.fee.store', $course) }}" class="border rounded-lg p-4 space-y-3 text-sm" id="feeForm">
            @csrf
            <p class="font-semibold">Add fees</p>
            <div class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Term</label>
                    <select name="term_number" id="feeTerm" class="border rounded-lg px-3 py-2">
                        @for($n = 1; $n <= $course->term_count; $n++)
                            <option value="{{ $n }}" @selected(old('term_number') == $n)>Term {{ $n }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">For intakes from</label>
                    <input type="date" name="effective_from" required value="{{ old('effective_from', '2020-01-01') }}" class="border rounded-lg px-3 py-2">
                </div>
                <label class="flex items-center gap-2 pb-2"><input type="checkbox" name="apply_to_all" value="1"> Same fees for every term</label>
            </div>

            <table class="w-full max-w-xl" id="itemTable">
                <thead><tr class="text-left text-xs text-gray-500"><th class="py-1">Item</th><th class="py-1 w-40">Amount (KES)</th><th></th></tr></thead>
                <tbody>
                    @foreach(old('items', [['name' => 'Tuition', 'amount' => ''], ['name' => 'Examination', 'amount' => '']]) as $i => $item)
                        <tr>
                            <td class="py-1 pr-2"><input type="text" name="items[{{ $i }}][name]" value="{{ $item['name'] }}" class="w-full border rounded px-2 py-1"></td>
                            <td class="py-1 pr-2"><input type="number" step="0.01" min="0" name="items[{{ $i }}][amount]" value="{{ $item['amount'] }}" class="item-amount w-full border rounded px-2 py-1"></td>
                            <td><button type="button" class="text-red-600 text-xs" onclick="this.closest('tr').remove(); total()">Remove</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="flex items-center gap-4">
                <button type="button" onclick="addItem()" class="border rounded-lg px-3 py-1.5">Add item</button>
                <span>Term total: <strong id="feeTotal">KES 0</strong></span>
            </div>
            <input type="text" name="notes" maxlength="255" value="{{ old('notes') }}" placeholder="Note (optional)" class="w-full max-w-xl border rounded-lg px-3 py-2">
            <div><button class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">Save fees</button></div>
        </form>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    let nextItem = 100;
    function addItem(name = '', amount = '') {
        const i = nextItem++;
        document.querySelector('#itemTable tbody').insertAdjacentHTML('beforeend', `
            <tr>
                <td class="py-1 pr-2"><input type="text" name="items[${i}][name]" value="${name}" class="w-full border rounded px-2 py-1"></td>
                <td class="py-1 pr-2"><input type="number" step="0.01" min="0" name="items[${i}][amount]" value="${amount}" class="item-amount w-full border rounded px-2 py-1"></td>
                <td><button type="button" class="text-red-600 text-xs" onclick="this.closest('tr').remove(); total()">Remove</button></td>
            </tr>`);
        total();
    }
    function total() {
        let t = 0;
        document.querySelectorAll('.item-amount').forEach(i => t += parseFloat(i.value) || 0);
        const el = document.getElementById('feeTotal');
        if (el) el.textContent = 'KES ' + t.toLocaleString();
    }
    function copyVersion(term, items) {
        document.getElementById('feeTerm').value = term;
        document.querySelector('#itemTable tbody').innerHTML = '';
        items.forEach(it => addItem(String(it.name).replace(/"/g, '&quot;'), it.amount));
        document.getElementById('feeForm').scrollIntoView({ behavior: 'smooth' });
    }
    document.getElementById('itemTable')?.addEventListener('input', total);
    total();
</script>
@endpush
