{{-- Collect school fees by M-Pesa: any amount; before enrollment it is kept as a deposit --}}
@php
    $promptSvc = app(\App\Services\Payments\FeePromptService::class);
    $account = $promptSvc->account($student);
    $open = $student->enrollments->whereNotIn('status', ['dropped', 'transferred']);
    $enrolled = $open->isNotEmpty();
    $held = app(\App\Services\Payments\KcbIpnRouter::class)->heldFor($student);
    $balance = $open->sum(fn ($e) => (float) $e->total_fees - (float) $e->amount_paid);
    $prompts = \App\Models\KcbBuniTransaction::where('invoice_number', $account)->where('transaction_type', 'school_fees')->latest('id')->limit(5)->get();
    $phones = collect([
        $student->phone ? ['label' => 'Student', 'phone' => $student->phone] : null,
        $student->next_of_kin_phone ? ['label' => $student->next_of_kin_name ?: 'Parent / guardian', 'phone' => $student->next_of_kin_phone] : null,
    ])->filter()->unique('phone')->values();
@endphp
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6" id="fee-prompt">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center"><i class="fas fa-mobile-alt text-primary mr-2"></i> Collect fees by M-Pesa</h3>
        <span class="text-sm text-gray-600">Paybill <b>{{ config('kcb.paybill') }}</b> · Account <b class="font-mono">{{ $account }}</b></span>
    </div>
    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-4 min-w-0">
            @if(!$enrolled)
                <div class="rounded-lg bg-blue-50 border border-blue-200 text-blue-900 text-sm p-3">
                    <i class="fas fa-info-circle mr-1"></i> Not enrolled yet. Anything paid now is kept as a <b>deposit</b> for {{ $student->first_name }} and goes to their fees as soon as you enroll them.
                    <a href="{{ route('admin.enrollments.create', ['student_id' => $student->id]) }}" class="block mt-2 font-semibold text-blue-800 hover:underline"><i class="fas fa-user-plus mr-1"></i>Enroll in a course</a>
                </div>
            @else
                <p class="text-sm text-gray-700">Balance on open courses: <b class="{{ $balance > 0 ? 'text-red-700' : 'text-green-700' }}">KES {{ number_format($balance) }}</b>{{ $balance < 0 ? ' (credit)' : '' }}</p>
            @endif

            <form id="promptForm" class="space-y-3" onsubmit="return false">
                <label class="block text-sm font-medium text-gray-700">Amount the client is paying (KES)
                    <input type="number" id="promptAmount" min="1" step="1" required placeholder="Any amount, e.g. 5000"
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2">
                </label>
                <label class="block text-sm font-medium text-gray-700">Phone that will pay
                    <select id="promptPhoneChoice" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2">
                        @foreach($phones as $p)
                            <option value="{{ $p['phone'] }}">{{ $p['label'] }}: {{ $p['phone'] }}</option>
                        @endforeach
                        <option value="">Another number...</option>
                    </select>
                </label>
                <input type="tel" id="promptPhoneOther" placeholder="0712 345 678" class="{{ $phones->isEmpty() ? '' : 'hidden' }} w-full border border-gray-300 rounded-lg px-3 py-2">
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="promptSend" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                        <i class="fas fa-paper-plane mr-1"></i> Send M-Pesa prompt
                    </button>
                    <button type="button" id="promptText" class="border border-gray-300 hover:bg-gray-50 px-4 py-2 rounded-lg text-gray-700">
                        <i class="fas fa-sms mr-1"></i> Text payment details
                    </button>
                </div>
                <div id="promptStatus" class="hidden rounded-lg p-3 text-sm"></div>
            </form>
            <form id="textForm" method="POST" action="{{ route('admin.students.fee-details', $student) }}" class="hidden">
                @csrf <input type="hidden" name="phone" id="textPhone">
            </form>
        </div>

        <div class="space-y-4 min-w-0 text-sm">
            @if($held->isNotEmpty())
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Deposits waiting for enrollment: KES {{ number_format($held->sum('transaction_amount')) }}</p>
                    <ul class="divide-y border rounded-lg">
                        @foreach($held as $h)
                            <li class="px-3 py-2 flex justify-between gap-2"><span>{{ $h->transaction_date?->format('d M Y H:i') ?? $h->created_at->format('d M Y H:i') }} · {{ $h->transaction_id }}</span><b>KES {{ number_format($h->transaction_amount) }}</b></li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div>
                <p class="font-semibold text-gray-800 mb-2">Recent prompts</p>
                @forelse($prompts as $t)
                    @php $st = $promptSvc->status($t); @endphp
                    <div class="flex justify-between gap-2 py-1.5 border-b last:border-0">
                        <span class="text-gray-600">{{ $t->created_at->format('d M H:i') }} · {{ $t->phone_number }}</span>
                        <span class="{{ $st['state'] === 'paid' ? 'text-green-700' : ($st['state'] === 'failed' ? 'text-red-600' : 'text-amber-600') }} font-medium whitespace-nowrap">
                            KES {{ number_format($t->amount) }} · {{ ['paid' => 'Paid', 'failed' => 'Not paid', 'confirming' => 'Confirming', 'waiting' => 'Waiting'][$st['state']] }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">No prompts sent yet.</p>
                @endforelse
            </div>
            <p class="text-xs text-gray-500">Payments come in through KCB and are receipted automatically. Use Finance's Record Payment only for real cash.</p>
        </div>
    </div>
</div>

<script>
(function () {
    const csrf = '{{ csrf_token() }}', sendUrl = '{{ route('admin.students.fee-prompt', $student) }}',
          statusUrl = '{{ route('admin.students.fee-prompt.status', [$student, '__ID__']) }}';
    const choice = document.getElementById('promptPhoneChoice'), other = document.getElementById('promptPhoneOther'),
          box = document.getElementById('promptStatus'), sendBtn = document.getElementById('promptSend');
    const phone = () => (choice && choice.value) ? choice.value : other.value.trim();
    if (choice) choice.addEventListener('change', () => other.classList.toggle('hidden', choice.value !== ''));
    const show = (state, msg) => {
        box.className = 'rounded-lg p-3 text-sm ' + ({paid: 'bg-green-50 text-green-800', failed: 'bg-red-50 text-red-800'}[state] || 'bg-amber-50 text-amber-800');
        box.innerHTML = (state === 'paid' ? '<i class="fas fa-check-circle mr-1"></i>' : state === 'failed' ? '<i class="fas fa-times-circle mr-1"></i>' : '<i class="fas fa-spinner fa-spin mr-1"></i>') + msg;
    };
    let timer;
    sendBtn.addEventListener('click', async () => {
        const amount = document.getElementById('promptAmount').value;
        if (!amount || amount < 1) { show('failed', 'Type the amount the client is paying.'); return; }
        if (!phone()) { show('failed', 'Choose or type the phone that will pay.'); return; }
        sendBtn.disabled = true; show('waiting', 'Sending...');
        try {
            const r = await fetch(sendUrl, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({amount, phone: phone()})});
            const d = await r.json();
            if (!r.ok) { show('failed', d.error || (d.message ?? 'Could not send the prompt.')); sendBtn.disabled = false; return; }
            show(d.state, d.message);
            clearInterval(timer);
            timer = setInterval(async () => {
                const s = await (await fetch(statusUrl.replace('__ID__', d.id), {headers: {'Accept': 'application/json'}})).json();
                show(s.state, s.message);
                if (s.state === 'paid' || s.state === 'failed') { clearInterval(timer); sendBtn.disabled = false; }
            }, 4000);
        } catch (e) { show('failed', 'Network problem. Try again.'); sendBtn.disabled = false; }
    });
    document.getElementById('promptText').addEventListener('click', () => {
        if (!phone()) { show('failed', 'Choose or type the phone to text.'); return; }
        document.getElementById('textPhone').value = phone();
        document.getElementById('textForm').submit();
    });
})();
</script>
