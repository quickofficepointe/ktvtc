@extends('ktvtc.finance.layouts.app')

@section('title', 'Fee reminders')
@section('subtitle', 'Text students and their parents about balances')

@section('content')
@php $kes = fn ($v) => 'KES ' . number_format((float) $v); @endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-dash.kpi label="Due a reminder" :value="number_format($counts['due'])" icon="fa-bell" tone="amber"
        :hint="$kes($amounts['due']) . ' owed · not reminded in the last 7 days'" :href="route('finance.fee-reminders.index', ['tab' => 'due'])" />
    <x-dash.kpi label="Reminders sent this week" :value="number_format($sentThisWeek)" icon="fa-paper-plane" tone="blue" hint="Each goes to the student and the parent" />
    <x-dash.kpi label="Old balances to confirm" :value="number_format($counts['unconfirmed'])" icon="fa-clipboard-check" tone="gray"
        :hint="$kes($amounts['unconfirmed']) . ' · whole-course figures typed in before the new system'" :href="route('finance.fee-reminders.index', ['tab' => 'unconfirmed'])" />
</div>

@if($sample)
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 text-sm">
        <p class="text-xs font-semibold uppercase text-gray-500 mb-1">What the SMS says</p>
        <p class="whitespace-pre-line text-gray-800 font-mono text-xs bg-gray-50 rounded-lg p-3">{{ $sample }}</p>
    </div>
@endif

<x-dash.panel title="Students with balances">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex flex-wrap gap-1 text-sm">
            @foreach(['due' => 'Due this week', 'remindable' => 'All that can be reminded', 'unconfirmed' => 'Old balances to confirm'] as $key => $label)
                <a href="{{ route('finance.fee-reminders.index', ['tab' => $key]) }}"
                   class="px-3 py-1.5 rounded-lg {{ $tab === $key ? 'bg-red-700 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' }}">{{ $label }} ({{ number_format($counts[$key]) }})</a>
            @endforeach
        </div>
        <form method="GET" class="flex gap-2 text-sm">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, number or course" class="border border-gray-300 rounded-lg px-3 py-1.5">
            <input type="number" name="min" value="{{ request('min') }}" placeholder="Owing at least" class="border border-gray-300 rounded-lg px-3 py-1.5 w-36">
            <button class="px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50">Filter</button>
        </form>
    </div>

    @if($tab === 'unconfirmed')
        <p class="text-sm text-gray-600 mb-3">These balances come from the old whole-course records, and some were typed in wrongly. Check each against the receipts, then tick it and press <b>Confirm balance</b>. Confirmed balances move to "All that can be reminded".</p>
    @elseif($tab === 'due' && $counts['due'] > 0)
        <form method="POST" action="{{ route('finance.fee-reminders.bulk') }}" class="mb-3"
              onsubmit="return confirm('Text reminders to the next {{ min($counts['due'], $batch) }} students and their parents (largest balances first)?')">
            @csrf
            <input type="hidden" name="scope" value="due">
            <button class="px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-white text-sm"><i class="fas fa-paper-plane mr-1"></i> Remind the next {{ min($counts['due'], $batch) }}</button>
            <span class="text-xs text-gray-500 ml-2">Sends {{ $batch }} at a time; anyone reminded in the last 7 days is skipped.</span>
        </form>
    @endif

    <form method="POST" id="selectedForm" action="{{ $tab === 'unconfirmed' ? route('finance.fee-reminders.confirm') : route('finance.fee-reminders.bulk') }}">
        @csrf
        <div class="overflow-x-auto -mx-1">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-gray-500 border-b">
                        <th class="py-2 px-1"><input type="checkbox" onclick="document.querySelectorAll('.pick').forEach(c => c.checked = this.checked)"></th>
                        <th class="py-2 px-1">Student</th>
                        <th class="py-2 px-1">Course</th>
                        <th class="py-2 px-1">Texts go to</th>
                        <th class="py-2 px-1 text-right">Billed</th>
                        <th class="py-2 px-1 text-right">Paid</th>
                        <th class="py-2 px-1 text-right">Balance</th>
                        <th class="py-2 px-1">Last reminded</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $e)
                        @php
                            $phones = app(\App\Services\Payments\FeeReminderService::class)->phonesFor($e);
                            $l = $last[$e->id] ?? null;
                        @endphp
                        <tr class="border-b last:border-0 align-top">
                            <td class="py-2 px-1"><input type="checkbox" class="pick" name="enrollment_ids[]" value="{{ $e->id }}"></td>
                            <td class="py-2 px-1"><a href="{{ route('enrollment-terms.show', $e) }}" class="font-medium text-gray-900 hover:text-red-700">{{ $e->student ? trim($e->student->first_name . ' ' . $e->student->last_name) : $e->student_name }}</a>
                                <span class="block text-xs text-gray-500 font-mono">{{ $e->student_number }}</span></td>
                            <td class="py-2 px-1 text-gray-600">{{ $e->course_name }}
                                <span class="block text-xs {{ $e->billing_mode === 'term' ? 'text-green-700' : ($e->balance_confirmed_at ? 'text-blue-700' : 'text-gray-500') }}">
                                    {{ $e->billing_mode === 'term' ? 'Billed per term' : ($e->balance_confirmed_at ? 'Confirmed ' . $e->balance_confirmed_at->format('d M') : 'Old balance, not confirmed') }}</span></td>
                            <td class="py-2 px-1 text-xs text-gray-600">
                                @forelse($phones as $p)<span class="block whitespace-nowrap">{{ $p }}{{ $e->student && $p === trim((string) $e->student->next_of_kin_phone) ? ' (parent)' : '' }}</span>@empty<span class="text-red-700">No phone</span>@endforelse
                            </td>
                            <td class="py-2 px-1 text-right tabular-nums">{{ number_format((float) $e->total_fees) }}</td>
                            <td class="py-2 px-1 text-right tabular-nums">{{ number_format((float) $e->amount_paid) }}</td>
                            <td class="py-2 px-1 text-right tabular-nums font-semibold text-red-700">{{ number_format((float) $e->total_fees - (float) $e->amount_paid) }}</td>
                            <td class="py-2 px-1 text-xs whitespace-nowrap {{ $l ? 'text-gray-600' : 'text-gray-400' }}">{{ $l ? \Carbon\Carbon::parse($l->at)->format('d M Y') . ' (' . $l->n . 'x)' : 'Never' }}</td>
                            <td class="py-2 px-1 text-right">
                                @if($tab !== 'unconfirmed')
                                    <button form="send-{{ $e->id }}" class="text-xs text-red-700 hover:underline whitespace-nowrap" @disabled(!$phones)><i class="fas fa-paper-plane mr-0.5"></i>Send</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-6 text-center text-gray-500">
                            {{ $tab === 'unconfirmed' ? 'No old balances waiting for confirmation.' : ($tab === 'due' ? 'Everyone who can be reminded was reminded in the last 7 days.' : 'No balances that can be reminded about yet. Balances appear here once students are billed per term, or once you confirm old balances.') }}
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($enrollments->count())
            <div class="mt-3">
                @if($tab === 'unconfirmed')
                    <button class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-sm" onclick="return document.querySelector('.pick:checked') ? confirm('Confirm the ticked balances are correct? Reminders can then be sent for them.') : (alert('Tick at least one balance.'), false)"><i class="fas fa-check mr-1"></i> Confirm balance</button>
                @else
                    <button class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-sm" onclick="return document.querySelector('.pick:checked') ? confirm('Text reminders to the ticked students and their parents? Anyone reminded in the last 7 days is skipped.') : (alert('Tick at least one student.'), false)"><i class="fas fa-paper-plane mr-1"></i> Remind ticked students</button>
                @endif
            </div>
        @endif
    </form>
    {{-- One small form per row for "Send", outside the table form --}}
    @foreach($enrollments as $e)
        <form method="POST" id="send-{{ $e->id }}" action="{{ route('finance.fee-reminders.send', $e) }}" class="hidden"
              onsubmit="return confirm('Text a reminder to this student and their parent now?')">@csrf</form>
    @endforeach
    <div class="mt-4">{{ $enrollments->links() }}</div>
</x-dash.panel>
@endsection
