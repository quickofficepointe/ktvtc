@extends('ktvtc.finance.layouts.app')

@section('title', 'KCB Payment ' . $ipn->transaction_id)
@section('subtitle', 'KES ' . number_format($ipn->transaction_amount, 2) . ' from ' . ($ipn->payer_name ?: $ipn->debit_msisdn))

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.kcb-payments.index') }}" class="text-gray-600 hover:text-primary">KCB Payments</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">{{ $ipn->transaction_id }}</span>
</li>
@endsection

@php
    $isFees = $ipn->till_number === config('kcb.tills.school_fees');
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Payment details --}}
    <div class="finance-card p-5 space-y-3 lg:col-span-1">
        <h3 class="font-semibold text-gray-800">Payment</h3>
        <dl class="text-sm space-y-2">
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Amount</dt><dd class="font-bold">KES {{ number_format($ipn->transaction_amount, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Paid at</dt><dd>{{ optional($ipn->transaction_date)->format('d M Y H:i') ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Till</dt><dd>{{ $ipn->till_number }} ({{ $isFees ? 'school fees' : 'cafeteria / cards' }})</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Account typed</dt><dd class="font-mono text-xs">{{ $ipn->business_key ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Payer</dt><dd>{{ $ipn->payer_name ?: '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Phone</dt><dd>{{ $ipn->debit_msisdn ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">KCB transaction</dt><dd class="font-mono text-xs">{{ $ipn->transaction_id }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Source IP</dt><dd>{{ $ipn->source_ip ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500">Signature</dt><dd>{{ str_replace('_', ' ', $ipn->signature_status ?? '—') }}</dd></div>
        </dl>

        <div class="rounded-lg p-3 text-sm {{ $ipn->needsAttention() ? 'bg-yellow-50 text-yellow-900' : 'bg-gray-50 text-gray-700' }}">
            <p class="font-semibold">{{ ucfirst($ipn->status) }}</p>
            <p>{{ $ipn->status_reason }}</p>
            @if($ipn->resolved_at)
                <p class="text-xs mt-1">By {{ $ipn->resolver->name ?? 'unknown' }} on {{ $ipn->resolved_at->format('d M Y H:i') }}</p>
            @endif
        </div>

        @if($ipn->status === \App\Models\KcbIpn::STATUS_CREDITED)
            <form method="POST" action="{{ route('finance.kcb-payments.undo', $ipn) }}" class="rounded-lg border border-red-200 p-3 text-sm space-y-2"
                  onsubmit="return confirm('Take KES {{ number_format($ipn->transaction_amount, 2) }} back and put this payment in the queue again?')">
                @csrf
                <p class="font-semibold text-red-700">Applied to the wrong {{ $isFees ? 'student' : 'card' }}?</p>
                <input type="text" name="reason" required minlength="5" maxlength="255" placeholder="Why (e.g. parent typed the wrong number)"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-sm">Undo and apply again</button>
            </form>
        @endif

        @if($ipn->is_historical)
            <div class="rounded-lg p-3 text-sm bg-purple-50 text-purple-900">
                Recovered from logs after the IPN outage. Before assigning, check that this payment was not already typed in by hand.
            </div>
        @endif
    </div>

    <div class="lg:col-span-2 space-y-6">
    @if($ipn->needsAttention())

        {{-- Suggestions --}}
        <div class="finance-card p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Suggested matches</h3>
            @forelse($suggestions as $s)
                <form method="POST"
                      action="{{ $s['type'] === 'student' ? route('finance.kcb-payments.assign-student', $ipn) : route('finance.kcb-payments.assign-card', $ipn) }}"
                      class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b last:border-0 py-3"
                      onsubmit="return confirm('Apply KES {{ number_format($ipn->transaction_amount, 2) }} to {{ addslashes($s['label']) }}?')">
                    @csrf
                    <input type="hidden" name="{{ $s['type'] === 'student' ? 'student_id' : 'card_account_id' }}" value="{{ $s['id'] }}">
                    <div>
                        <p class="font-medium">
                            <i class="fas {{ $s['type'] === 'student' ? 'fa-user-graduate' : 'fa-id-card' }} mr-1 text-gray-400"></i>
                            {{ $s['label'] }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $s['why'] }}</p>
                    </div>
                    @if($s['type'] === 'card')
                        @include('ktvtc.finance.payments.partials.wallet-select')
                    @endif
                    <button class="btn-primary text-white px-3 py-1.5 rounded-lg text-sm whitespace-nowrap">
                        {{ $s['type'] === 'student' ? 'Apply to fees' : 'Credit card' }}
                    </button>
                </form>
            @empty
                <p class="text-sm text-gray-500">No matches found by account number or phone. Search below.</p>
            @endforelse
        </div>

        {{-- Search --}}
        <div class="finance-card p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Find the student or card</h3>
            <form method="GET" class="flex gap-2 mb-4">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Student number, admission number, card number or name"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <button class="border border-gray-300 px-4 py-2 rounded-lg text-sm"><i class="fas fa-search"></i></button>
            </form>

            @if(request('q'))
                @foreach($searchStudents as $student)
                    <form method="POST" action="{{ route('finance.kcb-payments.assign-student', $ipn) }}"
                          class="flex items-center justify-between gap-2 border-b py-2"
                          onsubmit="return confirm('Apply KES {{ number_format($ipn->transaction_amount, 2) }} to {{ addslashes($student->full_name) }} ({{ $student->student_number }}) fees?')">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        <span class="text-sm"><i class="fas fa-user-graduate mr-1 text-gray-400"></i>{{ $student->full_name }} ({{ $student->student_number }})</span>
                        <button class="btn-primary text-white px-3 py-1 rounded-lg text-xs">Apply to fees</button>
                    </form>
                @endforeach
                @foreach($searchCards as $card)
                    <form method="POST" action="{{ route('finance.kcb-payments.assign-card', $ipn) }}"
                          class="flex items-center justify-between gap-2 border-b py-2"
                          onsubmit="return confirm('Credit KES {{ number_format($ipn->transaction_amount, 2) }} to card {{ $card->card_number }}?')">
                        @csrf
                        <input type="hidden" name="card_account_id" value="{{ $card->id }}">
                        <span class="text-sm"><i class="fas fa-id-card mr-1 text-gray-400"></i>{{ $card->student->full_name ?? 'Unknown' }} (adm {{ $card->student->admission_number ?? '—' }}, card {{ $card->card_number }})</span>
                        @include('ktvtc.finance.payments.partials.wallet-select')
                        <button class="btn-primary text-white px-3 py-1 rounded-lg text-xs">Credit card</button>
                    </form>
                @endforeach
                @if($searchStudents->isEmpty() && $searchCards->isEmpty())
                    <p class="text-sm text-gray-500">No student or card matches "{{ request('q') }}".</p>
                @endif
            @endif
        </div>

        {{-- Set aside --}}
        <div class="finance-card p-5">
            <h3 class="font-semibold text-gray-800 mb-1">Set aside</h3>
            <p class="text-sm text-gray-500 mb-3">Use this when the money should not go to any student, e.g. already recorded by hand, refunded, or not a fee payment.</p>
            <form method="POST" action="{{ route('finance.kcb-payments.ignore', $ipn) }}" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="text" name="reason" required minlength="5" maxlength="255" placeholder="Reason (required)"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">Set aside</button>
            </form>
            @error('reason')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

    @else
        <div class="finance-card p-5 text-sm text-gray-600">
            This payment is resolved. No further action is needed.
        </div>
    @endif
    </div>
</div>
@endsection
