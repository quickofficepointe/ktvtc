@extends('ktvtc.finance.layouts.app')

@section('title', 'Fare for a Class')
@section('subtitle', 'Record cash fare handed to several students on the same day')

@section('breadcrumb')
<li><span class="mx-2">/</span><a href="{{ route('finance.transport.index') }}" class="text-gray-600 hover:text-primary">Transport</a></li>
<li><span class="mx-2">/</span><span class="text-gray-400">Class fare</span></li>
@endsection

@section('content')
<div class="space-y-6">
    <form method="GET" class="finance-card p-4 flex flex-wrap items-end gap-3 text-sm">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Class</label>
            <select name="class" class="border rounded-lg px-3 py-2" onchange="this.form.submit()">
                <option value="">Choose a class</option>
                @foreach($classes as $c)
                    <option value="{{ $c }}" @selected(request('class') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @if($cards->isNotEmpty())
    <form method="POST" action="{{ route('finance.transport.batch.store') }}" class="finance-card p-4 space-y-4">
        @csrf
        <div class="flex flex-wrap items-end gap-4 text-sm">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Date given</label>
                <input type="date" name="given_on" required max="{{ today()->toDateString() }}" value="{{ old('given_on', today()->toDateString()) }}" class="border rounded-lg px-3 py-2">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Note for all</label>
                <input type="text" name="notes" maxlength="255" value="{{ old('notes') }}" placeholder="e.g. Closing day fare" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Same amount for everyone</label>
                <div class="flex gap-1">
                    <input type="number" id="fillAmount" min="1" class="border rounded-lg px-3 py-2 w-28">
                    <button type="button" onclick="fillAll()" class="border rounded-lg px-3 py-2">Fill</button>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-6 text-sm">
            <label class="flex items-center gap-2"><input type="hidden" name="charge_fee" value="0"><input type="checkbox" name="charge_fee" value="1" checked id="chargeFee"> Charge M-Pesa withdrawal fee</label>
            <label class="flex items-center gap-2"><input type="hidden" name="allow_negative" value="0"><input type="checkbox" name="allow_negative" value="1"> Allow below zero (past fare only)</label>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 px-3">Student</th>
                        <th class="py-2 px-3 text-right">Transport balance</th>
                        <th class="py-2 px-3 w-36">Cash given</th>
                        <th class="py-2 px-3 text-right">Fee</th>
                        <th class="py-2 px-3 text-right">Balance after</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cards as $card)
                        <tr class="border-b" data-balance="{{ (float) $card->transport_balance }}">
                            <td class="py-2 px-3">{{ $card->student_name }} <span class="text-xs text-gray-400">Adm {{ $card->student_admission_number }}</span></td>
                            <td class="py-2 px-3 text-right">{{ number_format($card->transport_balance, 2) }}</td>
                            <td class="py-2 px-3">
                                <input type="number" name="amounts[{{ $card->id }}]" min="0" step="1" value="{{ old('amounts.' . $card->id) }}"
                                       class="amount-input w-full border rounded-lg px-2 py-1">
                            </td>
                            <td class="py-2 px-3 text-right fee-cell text-gray-500">—</td>
                            <td class="py-2 px-3 text-right after-cell">—</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td class="py-2 px-3" colspan="2">Total</td>
                        <td class="py-2 px-3" id="totalCash">0</td>
                        <td class="py-2 px-3 text-right" id="totalFee">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <p class="text-xs text-gray-500">All-or-nothing: if any student does not have enough transport money, nothing is saved.</p>
        <button class="btn-primary text-white px-5 py-2 rounded-lg font-semibold"><i class="fas fa-check mr-1"></i> Record fare for class</button>
    </form>
    @elseif(request('class'))
        <div class="finance-card p-5 text-sm text-gray-500">No active cards in {{ request('class') }}.</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const feeTiers = @json($feeTiers);
    function fee(a) {
        let t = feeTiers.find(t => a >= +t.min_amount && a <= +t.max_amount);
        if (!t && feeTiers.length && a > +feeTiers[feeTiers.length - 1].max_amount) t = feeTiers[feeTiers.length - 1];
        return t ? +t.fee : 0;
    }
    function recalc() {
        let cash = 0, fees = 0;
        const charge = document.getElementById('chargeFee').checked;
        document.querySelectorAll('tr[data-balance]').forEach(row => {
            const a = parseFloat(row.querySelector('.amount-input').value) || 0;
            const f = a > 0 && charge ? fee(a) : 0;
            const after = parseFloat(row.dataset.balance) - a - f;
            row.querySelector('.fee-cell').textContent = a > 0 ? f.toLocaleString() : '—';
            const cell = row.querySelector('.after-cell');
            cell.textContent = a > 0 ? after.toLocaleString() : '—';
            cell.className = 'py-2 px-3 text-right after-cell ' + (a > 0 && after < 0 ? 'text-red-600 font-semibold' : '');
            cash += a; fees += f;
        });
        document.getElementById('totalCash').textContent = 'KES ' + cash.toLocaleString();
        document.getElementById('totalFee').textContent = 'KES ' + fees.toLocaleString();
    }
    function fillAll() {
        const v = document.getElementById('fillAmount').value;
        document.querySelectorAll('.amount-input').forEach(i => i.value = v);
        recalc();
    }
    document.querySelectorAll('.amount-input').forEach(i => i.addEventListener('input', recalc));
    document.getElementById('chargeFee')?.addEventListener('change', recalc);
</script>
@endpush
