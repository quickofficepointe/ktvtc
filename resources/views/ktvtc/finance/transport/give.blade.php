@extends('ktvtc.finance.layouts.app')

@section('title', 'Give Transport Fare')
@section('subtitle', $card ? $card->student_name . ' · Adm ' . $card->student_admission_number : 'Record cash fare handed to a student')

@section('breadcrumb')
<li><span class="mx-2">/</span><a href="{{ route('finance.transport.index') }}" class="text-gray-600 hover:text-primary">Transport</a></li>
<li><span class="mx-2">/</span><span class="text-gray-400">Give fare</span></li>
@endsection

@section('content')
@if(!$card)
    <div class="finance-card p-5 max-w-2xl">
        <form method="GET" class="flex gap-2 mb-4">
            <input type="text" name="q" value="{{ request('q') }}" autofocus placeholder="Student name, admission number or card number"
                   class="flex-1 border rounded-lg px-3 py-2 text-sm">
            <button class="btn-primary text-white px-4 py-2 rounded-lg text-sm"><i class="fas fa-search"></i></button>
        </form>
        @foreach($matches as $m)
            <a href="{{ route('finance.transport.give', ['card' => $m->id]) }}" class="flex justify-between border-b py-2 hover:bg-gray-50 px-2 rounded">
                <span>{{ $m->student_name }} <span class="text-xs text-gray-400">Adm {{ $m->student_admission_number }} · {{ $m->student_class }}</span></span>
                <span class="text-sm text-blue-700">Transport KES {{ number_format($m->transport_balance, 2) }}</span>
            </a>
        @endforeach
        @if(request('q') && $matches->isEmpty())
            <p class="text-sm text-gray-500">No student matches "{{ request('q') }}".</p>
        @endif
    </div>
@else
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="finance-card p-5 space-y-3">
        <h3 class="font-semibold text-gray-800">{{ $card->student_name }}</h3>
        <p class="text-sm text-gray-500">{{ $card->student_class }} · Card {{ $card->card_number }}</p>
        <div class="rounded-lg bg-blue-50 p-4">
            <p class="text-xs text-blue-700">Transport balance</p>
            <p class="text-3xl font-bold {{ $card->transport_balance < 0 ? 'text-red-600' : 'text-blue-700' }}">KES {{ number_format($card->transport_balance, 2) }}</p>
        </div>
        <p class="text-xs text-gray-500">Canteen: KES {{ number_format($card->canteen_balance, 2) }} (cannot be used for fare)</p>
        <a href="{{ route('finance.transport.give') }}" class="text-sm text-primary hover:underline">Choose another student</a>
    </div>

    <div class="finance-card p-5 lg:col-span-2">
        <form method="POST" action="{{ route('finance.transport.store') }}" class="space-y-4" id="fareForm">
            @csrf
            <input type="hidden" name="card_account_id" value="{{ $card->id }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cash given (KES)</label>
                    <input type="number" name="amount" id="fareAmount" min="1" step="1" required value="{{ old('amount') }}"
                           class="w-full border rounded-lg px-3 py-2 text-lg font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date given</label>
                    <input type="date" name="given_on" required max="{{ today()->toDateString() }}" value="{{ old('given_on', today()->toDateString()) }}"
                           class="w-full border rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-500 mt-1">Use a past date to record fare already given.</p>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Note</label>
                <input type="text" name="notes" maxlength="255" value="{{ old('notes') }}" placeholder="e.g. Midterm break fare, Nairobi"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="charge_fee" value="0">
                <input type="checkbox" name="charge_fee" value="1" id="chargeFee" checked> Charge M-Pesa withdrawal fee
            </label>
            <label class="flex items-start gap-2 text-sm">
                <input type="hidden" name="allow_negative" value="0">
                <input type="checkbox" name="allow_negative" value="1" @checked(old('allow_negative'))>
                <span>Record even if balance goes below zero <span class="text-xs text-gray-500 block">Only for fare already given in the past that was never recorded.</span></span>
            </label>

            <div id="fareQuote" class="rounded-lg bg-gray-50 p-3 text-sm hidden"></div>

            <button class="btn-primary text-white px-5 py-2 rounded-lg font-semibold"><i class="fas fa-check mr-1"></i> Record fare</button>
        </form>
    </div>

    <div class="finance-card p-5 lg:col-span-3">
        <h3 class="font-semibold text-gray-800 mb-3">Fare history</h3>
        @include('ktvtc.finance.transport.partials.history', ['rows' => $history, 'showStudent' => false])
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    const feeTiers = @json($feeTiers);
    const balance = {{ (float) ($card->transport_balance ?? 0) }};
    function fee(a) {
        let t = feeTiers.find(t => a >= +t.min_amount && a <= +t.max_amount);
        if (!t && feeTiers.length && a > +feeTiers[feeTiers.length - 1].max_amount) t = feeTiers[feeTiers.length - 1];
        return t ? +t.fee : 0;
    }
    function updateQuote() {
        const box = document.getElementById('fareQuote');
        const a = parseFloat(document.getElementById('fareAmount')?.value);
        if (!box || !a) { box && box.classList.add('hidden'); return; }
        const f = document.getElementById('chargeFee').checked ? fee(a) : 0;
        const after = balance - a - f;
        box.innerHTML = `Deduct <strong>KES ${(a + f).toLocaleString()}</strong> (KES ${a.toLocaleString()} cash + KES ${f.toLocaleString()} fee).
            Transport balance after: <strong class="${after < 0 ? 'text-red-600' : ''}">KES ${after.toLocaleString()}</strong>`;
        box.classList.remove('hidden');
    }
    document.getElementById('fareAmount')?.addEventListener('input', updateQuote);
    document.getElementById('chargeFee')?.addEventListener('change', updateQuote);
</script>
@endpush
