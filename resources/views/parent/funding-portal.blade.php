<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#991b1b">
    <title>Top up {{ $student->full_name }}'s card · Kenswed Senior School</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html, body { font-family: Inter, system-ui, sans-serif; -webkit-tap-highlight-color: transparent; }
        body { overflow-x: hidden; }
        .wallet[aria-pressed="true"] { box-shadow: 0 0 0 2px var(--ring), 0 10px 25px -10px rgba(0,0,0,.25); }
        .wallet[aria-pressed="true"] .tick { opacity: 1; }
        .chip[aria-pressed="true"] { background: var(--accent); color: #fff; box-shadow: none; }
        .spinner { width: 22px; height: 22px; border: 3px solid rgba(255,255,255,.35); border-top-color: #fff; border-radius: 50%; animation: spin .9s linear infinite; display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes ring { 0%,100% { transform: rotate(0); } 20% { transform: rotate(-12deg); } 40% { transform: rotate(12deg); } 60% { transform: rotate(-6deg); } }
        .ringing { animation: ring 1.6s ease-in-out infinite; transform-origin: 50% 80%; }
        input[type=number]::-webkit-outer-spin-button, input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
    @include('partials.image-fallback')
</head>
@php
    $tBal = (float) $card->transport_balance;
    $cBal = (float) $card->canteen_balance;
    $fmt = fn ($v) => number_format((float) $v, fmod((float) $v, 1.0) == 0.0 ? 0 : 2);
@endphp
<body class="bg-stone-100 min-h-screen text-stone-900">
<div class="max-w-md mx-auto min-h-screen bg-stone-50 sm:shadow-xl pb-10" style="--accent: #2563eb; --ring: #2563eb;" id="app">

    {{-- Header --}}
    <header class="bg-gradient-to-br from-red-700 to-red-900 text-white px-5 pt-6 pb-16 rounded-b-[28px]">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('Assets/images/Kenswed_logo.png') }}" alt="" class="w-9 h-9 rounded-full bg-white p-0.5 object-contain">
            <span class="text-sm font-medium text-red-100">Kenswed Senior School</span>
        </div>
        <p class="mt-5 text-sm text-red-100">Top up card for</p>
        <h1 class="text-2xl font-extrabold leading-tight">{{ $student->full_name }}</h1>
        <p class="text-sm text-red-100">{{ $student->class }}{{ $student->stream ? ' ' . $student->stream : '' }} · Adm {{ $student->admission_number }}</p>
    </header>

    {{-- Wallets --}}
    <section class="-mt-11 px-4 grid grid-cols-2 gap-3" aria-label="Choose what to top up">
        @foreach(['transport' => ['Transport', 'fa-bus', '#2563eb', 'text-blue-600', $tBal], 'canteen' => ['Canteen', 'fa-utensils', '#16a34a', 'text-green-600', $cBal]] as $key => [$label, $icon, $color, $iconClass, $bal])
            <button type="button" class="wallet text-left rounded-2xl p-4 bg-white ring-1 ring-stone-200 transition active:scale-[.98]"
                    data-wallet="{{ $key }}" data-color="{{ $color }}" aria-pressed="{{ $wallet === $key ? 'true' : 'false' }}" style="--ring: {{ $color }}">
                <span class="flex items-center justify-between">
                    <i class="fas {{ $icon }} {{ $iconClass }}"></i>
                    <i class="tick fas fa-circle-check opacity-0 transition" style="color: {{ $color }}"></i>
                </span>
                <span class="block text-xs text-stone-500 mt-2">{{ $label }}</span>
                <span class="block text-lg font-extrabold {{ $bal < $lowAt ? 'text-amber-600' : 'text-stone-900' }}" data-balance="{{ $key }}">KES {{ $fmt($bal) }}</span>
                @if($bal < $lowAt)<span class="block text-[11px] font-semibold text-amber-600 -mt-0.5">Low balance</span>@endif
            </button>
        @endforeach
    </section>

    {{-- Pending request from earlier --}}
    @if($pendingFunding)
        <div class="mx-4 mt-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200 p-4 flex items-center gap-3">
            <i class="fas fa-hourglass-half text-amber-600"></i>
            <div class="flex-1 text-sm">
                <p class="font-semibold text-amber-900">A payment of KES {{ $fmt($pendingFunding->amount) }} is still waiting</p>
                <p class="text-amber-800 text-xs">Sent {{ $pendingFunding->created_at->diffForHumans() }}</p>
            </div>
            <button type="button" onclick="startWaiting('{{ $pendingFunding->checkout_request_id }}', {{ (float) $pendingFunding->amount }})" class="px-3 py-2 rounded-xl bg-amber-600 text-white text-xs font-semibold">Check</button>
        </div>
    @endif

    {{-- Top-up form --}}
    <form id="topup" class="px-4 mt-5" novalidate>
        <input type="hidden" name="student_id" value="{{ $student->id }}">

        <p class="text-sm font-semibold text-stone-700 mb-2">Amount <span class="font-normal text-stone-500" id="forWhat"></span></p>
        <div class="grid grid-cols-4 gap-2" id="chips">
            @foreach([100, 200, 500, 1000] as $a)
                <button type="button" class="chip py-3 rounded-xl bg-white ring-1 ring-stone-200 font-semibold transition active:scale-95" data-amount="{{ $a }}" aria-pressed="false">{{ number_format($a) }}</button>
            @endforeach
        </div>
        <div class="mt-2 relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-stone-400 font-semibold">KES</span>
            <input type="number" inputmode="numeric" id="amount" min="10" max="50000" placeholder="Other amount"
                   class="w-full rounded-xl ring-1 ring-stone-200 bg-white pl-14 pr-4 py-3.5 text-base outline-none focus:ring-2 focus:ring-[var(--accent)]">
        </div>
        <p id="feeNote" class="hidden mt-3 rounded-xl bg-blue-50 text-blue-900 text-xs leading-relaxed p-3"></p>
        <p id="amountError" class="hidden mt-2 text-xs text-red-600"></p>

        <p class="text-sm font-semibold text-stone-700 mt-5 mb-2">M-Pesa number to pay from</p>
        <div class="flex items-center rounded-xl ring-1 ring-stone-200 bg-white px-4 focus-within:ring-2 focus-within:ring-[var(--accent)]">
            <i class="fas fa-mobile-screen text-stone-400 mr-3"></i>
            <input type="tel" inputmode="tel" id="phone" autocomplete="tel" value="{{ $student->parent_phone }}" placeholder="0712 345 678"
                   class="flex-1 py-3.5 text-base outline-none bg-transparent">
        </div>
        <p id="phoneError" class="hidden mt-2 text-xs text-red-600"></p>

        <button type="submit" id="payBtn"
                class="mt-6 w-full py-4 rounded-2xl bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white font-bold text-base shadow-lg shadow-green-600/30 transition active:scale-[.99]">
            <i class="fas fa-lock mr-2"></i><span id="payLabel">Choose an amount</span>
        </button>
        <p class="text-center text-xs text-stone-500 mt-3">You'll get an M-Pesa prompt on this number — enter your PIN to pay.</p>
    </form>

    {{-- Recent activity --}}
    <details class="mx-4 mt-6 bg-white rounded-2xl ring-1 ring-stone-200 group">
        <summary class="list-none cursor-pointer px-4 py-3.5 flex items-center justify-between text-sm font-semibold text-stone-700">
            <span><i class="fas fa-clock-rotate-left mr-2 text-stone-400"></i>Recent activity</span>
            <i class="fas fa-chevron-down text-xs text-stone-400 group-open:rotate-180 transition"></i>
        </summary>
        <div class="px-4 pb-3 divide-y divide-stone-100">
            @forelse($activity as $a)
                <div class="py-3 flex items-start gap-3 text-sm">
                    <span class="mt-0.5 w-8 h-8 shrink-0 rounded-full flex items-center justify-center {{ $a['delta'] >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                        <i class="fas {{ $a['delta'] >= 0 ? 'fa-arrow-down' : 'fa-arrow-up' }} text-xs"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium">{{ $a['label'] }} <span class="text-xs text-stone-400">· {{ $a['wallet'] }}</span></p>
                        @if($a['detail'])<p class="text-xs text-stone-500 truncate">{{ $a['detail'] }}</p>@endif
                        <p class="text-xs text-stone-400">{{ $a['at']->format('D j M, g:i a') }}</p>
                    </div>
                    <span class="font-semibold {{ $a['delta'] >= 0 ? 'text-green-700' : 'text-stone-800' }}">{{ $a['delta'] >= 0 ? '+' : '−' }}{{ $fmt(abs($a['delta'])) }}</span>
                </div>
            @empty
                <p class="py-4 text-sm text-stone-500 text-center">No activity yet.</p>
            @endforelse
        </div>
    </details>

    {{-- Pay by hand --}}
    <details class="mx-4 mt-3 bg-white rounded-2xl ring-1 ring-stone-200 group">
        <summary class="list-none cursor-pointer px-4 py-3.5 flex items-center justify-between text-sm font-semibold text-stone-700">
            <span><i class="fas fa-circle-question mr-2 text-stone-400"></i>No prompt? Pay by M-Pesa instead</span>
            <i class="fas fa-chevron-down text-xs text-stone-400 group-open:rotate-180 transition"></i>
        </summary>
        <div class="px-4 pb-4 text-sm text-stone-600">
            <p>M-Pesa → Lipa na M-Pesa → <b>Pay Bill</b></p>
            <div class="mt-2 rounded-xl bg-stone-50 ring-1 ring-stone-200 divide-y divide-stone-200">
                <p class="px-3 py-2 flex justify-between"><span>Business number</span><b>{{ $paybill }}</b></p>
                <p class="px-3 py-2 flex justify-between"><span>Account · canteen</span><b>{{ $till }}#{{ $student->admission_number }}</b></p>
                <p class="px-3 py-2 flex justify-between"><span>Account · transport</span><b>{{ $till }}#T{{ $student->admission_number }}</b></p>
            </div>
            <p class="mt-2 text-xs text-stone-500">For transport, add the M-Pesa withdrawal charge to the fare.</p>
            @if($helpPhone)<p class="pt-3">Need help? Call the school on <a href="tel:{{ $helpPhone }}" class="text-red-700 font-semibold">{{ $helpPhone }}</a></p>@endif
        </div>
    </details>

    <p class="text-center text-[11px] text-stone-400 mt-8">© {{ date('Y') }} Kenswed Senior School</p>
</div>

{{-- Waiting / result sheet --}}
<div id="sheet" class="hidden fixed inset-0 z-50 bg-black/40 flex items-end sm:items-center justify-center">
    <div class="w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl p-6 pb-8 text-center">
        <div id="sheetWaiting">
            <span class="inline-flex w-16 h-16 rounded-full bg-red-50 text-red-700 items-center justify-center"><i class="fas fa-mobile-screen-button text-3xl ringing"></i></span>
            <h2 class="mt-4 text-xl font-extrabold">Check your phone</h2>
            <p class="mt-1 text-stone-600 text-sm">Enter your M-Pesa PIN to pay <b id="sheetAmount"></b>.</p>
            <p class="mt-4 text-xs text-stone-400 flex items-center justify-center gap-2"><span class="spinner" style="border-color: #e7e5e4; border-top-color: #b91c1c; width: 16px; height: 16px; border-width: 2px;"></span> Waiting for M-Pesa…</p>
        </div>
        <div id="sheetDone" class="hidden">
            <span class="inline-flex w-16 h-16 rounded-full bg-green-600 text-white items-center justify-center"><i class="fas fa-check text-3xl"></i></span>
            <h2 class="mt-4 text-xl font-extrabold">Payment received</h2>
            <p class="mt-1 text-stone-600 text-sm" id="doneText">The card will show the new balance in a moment. You'll also get an SMS.</p>
            <button type="button" onclick="location.reload()" class="mt-6 w-full py-3.5 rounded-2xl bg-stone-900 text-white font-semibold">Done</button>
        </div>
        <div id="sheetFailed" class="hidden">
            <span class="inline-flex w-16 h-16 rounded-full bg-red-600 text-white items-center justify-center"><i class="fas fa-xmark text-3xl"></i></span>
            <h2 class="mt-4 text-xl font-extrabold">Not paid</h2>
            <p class="mt-1 text-stone-600 text-sm" id="failText">The payment was cancelled or timed out. Nothing was taken.</p>
            <button type="button" onclick="closeSheet()" class="mt-6 w-full py-3.5 rounded-2xl bg-red-700 text-white font-semibold">Try again</button>
        </div>
        <div id="sheetSlow" class="hidden">
            <span class="inline-flex w-16 h-16 rounded-full bg-amber-500 text-white items-center justify-center"><i class="fas fa-hourglass-half text-2xl"></i></span>
            <h2 class="mt-4 text-xl font-extrabold">Still waiting</h2>
            <p class="mt-1 text-stone-600 text-sm">If you paid, the card will update shortly and you'll get an SMS. If not, try again.</p>
            <div class="mt-6 grid grid-cols-2 gap-3">
                <button type="button" onclick="closeSheet()" class="py-3.5 rounded-2xl bg-stone-100 font-semibold">Close</button>
                <button type="button" onclick="location.reload()" class="py-3.5 rounded-2xl bg-stone-900 text-white font-semibold">Refresh</button>
            </div>
        </div>
    </div>
</div>

<script>
    const feeTiers = @json($feeTiers);
    const state = { wallet: @json($wallet), amount: 0 };
    const $ = id => document.getElementById(id);

    // Safaricom withdrawal charge added to transport top-ups (the fare is withdrawn in cash later)
    function withdrawalFee(amount) {
        if (!amount || amount <= 0) return 0;
        let tier = feeTiers.find(t => amount >= parseFloat(t.min_amount) && amount <= parseFloat(t.max_amount));
        if (!tier && feeTiers.length && amount > parseFloat(feeTiers[feeTiers.length - 1].max_amount)) tier = feeTiers[feeTiers.length - 1];
        return tier ? parseFloat(tier.fee) : 0;
    }
    const kes = v => Number(v).toLocaleString('en-KE', { maximumFractionDigits: 2 });
    const total = () => state.amount + (state.wallet === 'transport' ? withdrawalFee(state.amount) : 0);

    function render() {
        document.querySelectorAll('.wallet').forEach(b => b.setAttribute('aria-pressed', b.dataset.wallet === state.wallet ? 'true' : 'false'));
        const color = document.querySelector(`.wallet[data-wallet="${state.wallet}"]`).dataset.color;
        $('app').style.setProperty('--accent', color);
        $('forWhat').textContent = state.wallet === 'transport' ? '· transport (bus fare)' : '· canteen (meals & snacks)';
        document.querySelectorAll('.chip').forEach(c => c.setAttribute('aria-pressed', Number(c.dataset.amount) === state.amount && !$('amount').value ? 'true' : 'false'));

        const fee = state.wallet === 'transport' ? withdrawalFee(state.amount) : 0;
        if (state.amount >= 10 && fee > 0) {
            $('feeNote').innerHTML = `You'll be asked to pay <b>KES ${kes(state.amount + fee)}</b>: KES ${kes(state.amount)} fare + KES ${kes(fee)} M-Pesa withdrawal charge (the fare is given to your child in cash).`;
            $('feeNote').classList.remove('hidden');
        } else {
            $('feeNote').classList.add('hidden');
        }
        $('payLabel').textContent = state.amount >= 10 ? `Pay KES ${kes(total())} with M-Pesa` : 'Choose an amount';
    }

    document.querySelectorAll('.wallet').forEach(b => b.addEventListener('click', () => { state.wallet = b.dataset.wallet; render(); }));
    document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', () => { $('amount').value = ''; state.amount = Number(c.dataset.amount); $('amountError').classList.add('hidden'); render(); }));
    $('amount').addEventListener('input', e => { state.amount = Math.round(Number(e.target.value) || 0); render(); });

    function cleanPhone(v) {
        let d = String(v).replace(/\D/g, '');
        if (d.startsWith('254')) d = '0' + d.slice(3);
        if (d.length === 9 && /^[71]/.test(d)) d = '0' + d;
        return d;
    }

    $('topup').addEventListener('submit', async e => {
        e.preventDefault();
        const phone = cleanPhone($('phone').value);
        let ok = true;
        if (!(state.amount >= 10 && state.amount <= 50000)) { $('amountError').textContent = 'Choose an amount between KES 10 and 50,000.'; $('amountError').classList.remove('hidden'); ok = false; }
        if (!/^0[17]\d{8}$/.test(phone)) { $('phoneError').textContent = 'Enter a Safaricom number like 0712 345 678.'; $('phoneError').classList.remove('hidden'); ok = false; } else { $('phoneError').classList.add('hidden'); }
        if (!ok) return;

        const btn = $('payBtn');
        btn.disabled = true; $('payLabel').innerHTML = '<span class="spinner align-middle"></span>';
        try {
            const res = await fetch(state.wallet === 'transport' ? '{{ route('parent.fund.transport') }}' : '{{ route('parent.fund.canteen') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ student_id: {{ $student->id }}, amount: state.amount, phone_number: phone }),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.success && data.checkout_request_id) {
                startWaiting(data.checkout_request_id, data.amount || total());
            } else {
                showFailed(data.message || 'The payment could not be started. Please try again.');
            }
        } catch (err) {
            showFailed('No connection. Check your internet and try again.');
        } finally {
            btn.disabled = false; render();
        }
    });

    function show(which) {
        $('sheet').classList.remove('hidden');
        ['sheetWaiting', 'sheetDone', 'sheetFailed', 'sheetSlow'].forEach(id => $(id).classList.toggle('hidden', id !== which));
    }
    function closeSheet() { $('sheet').classList.add('hidden'); clearInterval(window.poller); }
    function showFailed(msg) { $('failText').textContent = msg; show('sheetFailed'); }

    function startWaiting(checkoutId, amount) {
        $('sheetAmount').textContent = 'KES ' + kes(amount);
        show('sheetWaiting');
        clearInterval(window.poller);
        let tries = 0;
        window.poller = setInterval(async () => {
            tries++;
            try {
                const r = await fetch('/parent/fund/status/' + encodeURIComponent(checkoutId), { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                if (d.status === 'completed') { clearInterval(window.poller); show('sheetDone'); return; }
                if (d.status === 'failed') { clearInterval(window.poller); showFailed(d.result_description ? 'M-Pesa says: ' + d.result_description : 'The payment was cancelled or timed out. Nothing was taken.'); return; }
            } catch (e) { /* keep trying */ }
            if (tries >= 36) { clearInterval(window.poller); show('sheetSlow'); } // about 3 minutes
        }, 5000);
    }

    render();
</script>
</body>
</html>
