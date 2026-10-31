@extends($layout)

@section('title', 'Terms · ' . ($enrollment->student->full_name ?? $enrollment->student_name))
@section('subtitle', $enrollment->course_name . ' · ' . ($enrollment->student->student_number ?? $enrollment->student_number))

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500">Billing</p>
            <p class="font-bold">{{ $enrollment->billing_mode === 'term' ? 'Per term' : 'Whole-course fee' }}</p>
            <p class="text-xs text-gray-500">
                @if($enrollment->billing_mode === 'term')
                    Each term is billed when registered.
                @else
                    KES {{ number_format($enrollment->total_fees, 2) }} shared across {{ $enrollment->course?->term_count ?? '?' }} terms.
                @endif
            </p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500">Total billed</p>
            <p class="font-bold">KES {{ number_format($enrollment->total_fees, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500">Paid</p>
            <p class="font-bold text-green-700">KES {{ number_format($enrollment->amount_paid, 2) }}</p>
            @if($credit > 0)<p class="text-xs text-gray-500">KES {{ number_format($credit, 2) }} ahead of registered terms</p>@endif
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500">Balance</p>
            <p class="font-bold {{ $enrollment->balance > 0 ? 'text-red-600' : 'text-green-700' }}">KES {{ number_format($enrollment->balance, 2) }}</p>
        </div>
    </div>

    @if(!$enrollment->course?->hasTermSetup())
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-900">
            {{ $enrollment->course?->name ?? 'This course' }} has not been split into terms yet.
            Finance sets this up under <strong>Term Fees</strong>.
        </div>
    @endif

    <div class="bg-white rounded-xl border p-5 overflow-x-auto">
        <h3 class="font-semibold text-gray-800 mb-3">Registered terms</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 pr-3">Term</th><th class="py-2 pr-3">Dates</th><th class="py-2 pr-3 text-right">Fee</th>
                    <th class="py-2 pr-3 text-right">Paid</th><th class="py-2 pr-3 text-right">Balance</th><th class="py-2 pr-3">How</th><th class="py-2">Exam card</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledger as $row)
                    @php $reg = $row['registration']; @endphp
                    <tr class="border-b align-top">
                        <td class="py-2 pr-3 font-semibold">Term {{ $row['term'] }}</td>
                        <td class="py-2 pr-3 whitespace-nowrap">{{ $reg->starts_on->format('d M Y') }} – {{ $reg->ends_on->format('d M Y') }}</td>
                        <td class="py-2 pr-3 text-right">{{ number_format($row['fee'], 2) }}</td>
                        <td class="py-2 pr-3 text-right">{{ number_format($row['paid'], 2) }} <span class="text-xs text-gray-400">({{ $row['percent'] }}%)</span></td>
                        <td class="py-2 pr-3 text-right {{ $row['balance'] > 0 ? 'text-red-600' : 'text-green-700' }}">{{ number_format($row['balance'], 2) }}</td>
                        <td class="py-2 pr-3 text-xs">
                            {{ ['self' => 'Student', 'staff' => 'Staff', 'legacy' => 'Existing student (auto)', 'exempt' => 'Exempt (course change)'][$reg->registration_type] ?? $reg->registration_type }}
                            @if($reg->registeredBy) · {{ $reg->registeredBy->name }} @endif
                            @if($reg->override_reason)<br><span class="text-orange-700">Override: {{ $reg->override_reason }}</span>@endif
                        </td>
                        <td class="py-2 text-xs">
                            @if($row['exam_card']['allowed'])
                                <span class="text-green-700"><i class="fas fa-unlock mr-1"></i>Unlocked{{ $reg->exam_card_downloaded_at ? ' · downloaded ' . $reg->exam_card_downloaded_at->format('d M') : '' }}</span>
                            @else
                                <span class="text-gray-500"><i class="fas fa-lock mr-1"></i>{{ implode(' ', $row['exam_card']['reasons']) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-4 text-center text-gray-500">No terms registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($ledger->isNotEmpty())
            <form method="POST" action="{{ route('enrollment-terms.cancel', $enrollment) }}" class="mt-3 text-sm"
                  onsubmit="const r = prompt('Why cancel Term {{ $ledger->keys()->max() }}?'); if (!r || r.length < 5) return false; this.reason.value = r; return true;">
                @csrf
                <input type="hidden" name="reason">
                <button class="text-red-600 hover:underline text-xs">Cancel the Term {{ $ledger->keys()->max() }} registration</button>
            </form>
        @endif
    </div>

    @if($isFinance && $ledger->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <form method="POST" action="{{ route('enrollment-terms.adjust', $enrollment) }}" class="bg-white rounded-xl border p-5 space-y-3 text-sm">
                @csrf
                <h3 class="font-semibold text-gray-800">Adjust a term fee</h3>
                <p class="text-xs text-gray-500">A discount, waiver or bursary lowers that term's fee; an extra charge raises it. The 50% rule and the exam card use the new fee.</p>
                <div class="grid grid-cols-2 gap-2">
                    <select name="term_registration_id" class="no-select2 border rounded-lg px-3 py-2" required>
                        @foreach($ledger as $row)
                            @unless($row['registration']->isExempt())
                                <option value="{{ $row['registration']->id }}">Term {{ $row['term'] }} (KES {{ number_format($row['fee'], 2) }})</option>
                            @endunless
                        @endforeach
                    </select>
                    <select name="type" class="no-select2 border rounded-lg px-3 py-2" required>
                        @foreach(\App\Models\FeeAdjustment::TERM_TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="number" name="amount" min="1" step="0.01" required value="{{ old('amount') }}" placeholder="Amount (KES)" class="w-full border rounded-lg px-3 py-2">
                <input type="text" name="reason" minlength="5" maxlength="255" required value="{{ old('reason') }}" placeholder="Reason (shown on the statement)" class="w-full border rounded-lg px-3 py-2">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold">Save adjustment</button>
            </form>

            @php $refundable = max(0, round((float) $enrollment->amount_paid - (float) $enrollment->total_fees, 2)); @endphp
            <div class="bg-white rounded-xl border p-5 text-sm">
                <h3 class="font-semibold text-gray-800 mb-1">Refund</h3>
                @if($refundable > 0)
                    <p class="text-xs text-gray-500 mb-3">KES {{ number_format($refundable, 2) }} has been paid beyond the fees on this course.</p>
                    <form method="POST" action="{{ route('enrollment-terms.refund', $enrollment) }}" class="space-y-2"
                          onsubmit="return confirm('Record a refund paid out to the student?')">
                        @csrf
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="amount" min="1" max="{{ $refundable }}" step="0.01" required placeholder="Amount (KES)" class="border rounded-lg px-3 py-2">
                            <select name="method" class="no-select2 border rounded-lg px-3 py-2" required>
                                <option value="mpesa">M-Pesa</option><option value="bank">Bank</option><option value="cash">Cash</option>
                            </select>
                        </div>
                        <input type="text" name="reference" maxlength="100" placeholder="M-Pesa / bank reference" class="w-full border rounded-lg px-3 py-2">
                        <input type="text" name="reason" minlength="5" maxlength="255" required placeholder="Reason" class="w-full border rounded-lg px-3 py-2">
                        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg font-semibold">Record refund</button>
                    </form>
                @else
                    <p class="text-xs text-gray-500">Nothing to refund: the student has not paid beyond the fees on this course.</p>
                @endif
            </div>
        </div>
    @endif

    @if($adjustments->isNotEmpty())
        <div class="bg-white rounded-xl border p-5 overflow-x-auto">
            <h3 class="font-semibold text-gray-800 mb-3">Adjustments, refunds and transfers</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">What</th><th class="py-2 pr-3 text-right">Fees</th><th class="py-2 pr-3 text-right">Paid</th><th class="py-2 pr-3">Reason</th><th class="py-2">By</th></tr></thead>
                <tbody>
                    @foreach($adjustments as $a)
                        <tr class="border-b align-top">
                            <td class="py-2 pr-3 whitespace-nowrap">{{ $a->created_at->format('d M Y') }}</td>
                            <td class="py-2 pr-3">{{ $a->label }}</td>
                            <td class="py-2 pr-3 text-right">{{ (float) $a->fees_change ? number_format($a->fees_change, 2) : '' }}</td>
                            <td class="py-2 pr-3 text-right">{{ (float) $a->paid_change ? number_format($a->paid_change, 2) : '' }}</td>
                            <td class="py-2 pr-3 text-xs">{{ $a->reason }}@if($a->reference) · Ref {{ $a->reference }}@endif</td>
                            <td class="py-2 text-xs">{{ $a->creator->name ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($next['next_term'])
        <div class="bg-white rounded-xl border p-5">
            <h3 class="font-semibold text-gray-800 mb-2">Register for Term {{ $next['next_term'] }}</h3>
            @if($next['fee'] !== null)
                <p class="text-sm text-gray-600 mb-2">Fee: KES {{ number_format($next['fee'], 2) }}
                    {{ $enrollment->billing_mode === 'term' || ((float) $enrollment->total_fees <= 0 && $ledger->isEmpty()) ? '(will be added to the student\'s bill)' : '(share of the course fee, nothing extra billed)' }}</p>
            @endif
            @foreach($next['reasons'] as $reason)
                <p class="text-sm text-orange-700"><i class="fas fa-exclamation-circle mr-1"></i>{{ $reason }}</p>
            @endforeach
            <form method="POST" action="{{ route('enrollment-terms.register', $enrollment) }}" class="flex flex-wrap gap-2 mt-3 text-sm">
                @csrf
                @unless($next['allowed'])
                    <input type="text" name="override_reason" minlength="5" maxlength="255" value="{{ old('override_reason') }}"
                           placeholder="Reason to register anyway (required)" class="flex-1 min-w-[250px] border rounded-lg px-3 py-2">
                @endunless
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">Register Term {{ $next['next_term'] }}</button>
            </form>
        </div>
    @elseif(!empty($next['reasons']))
        <p class="text-sm text-gray-500">{{ implode(' ', $next['reasons']) }}</p>
    @endif
</div>
@endsection
