@extends('ktvtc.students.layout.studentlayout')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-6xl mx-auto">
@if(!$student)
    <x-dash.header title="Student portal" />
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-sm text-gray-700">
        Your account is not linked to an admitted student yet. Once the admissions office admits you, your courses, fees and results appear here.
        Sign in with your student number if you have one.
    </div>
@else
    <x-dash.header title="My dashboard" :subtitle="$student->student_number">
        <x-slot name="actions">
            <x-dash.action :href="route('student.results.index')" icon="fa-award">Results</x-dash.action>
            <x-dash.action :href="route('student.enrollment.index')" icon="fa-calendar-check">My terms</x-dash.action>
            <x-dash.action :href="route('student.fees.index')" icon="fa-mobile-screen" primary>Pay fees</x-dash.action>
        </x-slot>
    </x-dash.header>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-dash.kpi label="Fee balance" :value="'KES ' . number_format($balance)" icon="fa-wallet" :tone="$balance > 0 ? 'amber' : 'green'"
            :hint="$balance > 0 ? 'M-Pesa Paybill ' . config('kcb.paybill') . ', account ' . config('kcb.tills.school_fees') . '#' . $student->student_number : 'All fees paid'" :href="route('student.fees.index')" />
        <x-dash.kpi label="Paid so far" :value="'KES ' . number_format($paidTotal)" icon="fa-receipt" tone="blue" hint="Receipts on record" :href="route('student.fees.index')" />
        <x-dash.kpi label="Results published" :value="number_format($publishedCount)" icon="fa-award" tone="green" hint="Units with published marks" :href="route('student.results.index')" />
        <x-dash.kpi label="Library" :value="$onLoan->count() . ' book' . ($onLoan->count() == 1 ? '' : 's')" icon="fa-book-reader" :tone="$fines > 0 ? 'red' : 'gray'"
            :hint="$fines > 0 ? 'Fine owed: KES ' . number_format($fines) : 'On loan, no fines'" :href="route('student.library.index')" />
    </div>

    @foreach($courses as $c)
        @php
            $e = $c['enrollment']; $t = $c['term']; $card = $c['examCard']; $next = $c['next'];
        @endphp
        <x-dash.panel :title="$e->course_name ?? ($e->course->name ?? 'Course')" :subtitle="$e->student_number" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-sm">
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Current term</p>
                    @if($t)
                        <p class="font-semibold text-gray-900">Term {{ $t['term'] }}
                            <span class="font-normal text-gray-500">· {{ $t['registration']->starts_on?->format('d M') }} – {{ $t['registration']->ends_on?->format('d M Y') }}</span></p>
                        <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full {{ $t['percent'] >= 100 ? 'bg-green-600' : ($t['percent'] >= 50 ? 'bg-amber-500' : 'bg-red-600') }}" style="width: {{ min(100, $t['percent']) }}%"></div></div>
                        <p class="text-xs text-gray-500 mt-1">KES {{ number_format($t['paid']) }} of {{ number_format($t['fee']) }} paid ({{ $t['percent'] }}%)</p>
                    @else
                        <p class="text-gray-500">Not registered for a term yet.</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Exam card</p>
                    @if($card && $card['allowed'])
                        <a href="{{ route('student.enrollment.exam-card', $t['registration']) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-green-600 text-white text-sm hover:bg-green-700"><i class="fas fa-download"></i> Download exam card</a>
                    @elseif($card)
                        <ul class="text-gray-600 list-disc ml-4 space-y-0.5">@foreach($card['reasons'] as $r)<li>{{ $r }}</li>@endforeach</ul>
                        @if($t && $t['registration'])<a href="{{ route('student.evaluations.show', $t['registration']) }}" class="text-xs text-red-700 hover:underline">Evaluate your trainers &rarr;</a>@endif
                    @else
                        <p class="text-gray-500">Available once you are registered for a term.</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-1">Next term</p>
                    @if($next && $next['allowed'])
                        <p class="text-green-700 font-medium mb-2">You can register for Term {{ $next['next_term'] }}.</p>
                        <a href="{{ route('student.enrollment.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-red-700 text-white text-sm hover:bg-red-800"><i class="fas fa-calendar-plus"></i> Register now</a>
                    @elseif($next)
                        <ul class="text-gray-600 list-disc ml-4 space-y-0.5">@foreach($next['reasons'] as $r)<li>{{ $r }}</li>@endforeach</ul>
                    @endif
                </div>
            </div>
        </x-dash.panel>
    @endforeach

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-dash.panel title="Recent payments" :href="route('student.fees.index')">
            <ul class="divide-y text-sm">
                @forelse($payments as $p)
                    <li class="py-2 flex justify-between gap-3"><span>{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d M Y') : '' }}<span class="block text-xs text-gray-500 font-mono">{{ $p->transaction_code ?: $p->receipt_number }}</span></span><span class="font-semibold tabular-nums">KES {{ number_format((float) $p->amount) }}</span></li>
                @empty
                    <li class="py-2 text-gray-500">No payments yet.</li>
                @endforelse
            </ul>
        </x-dash.panel>
        <x-dash.panel title="Latest results" :href="route('student.results.index')">
            <ul class="divide-y text-sm">
                @forelse($results as $r)
                    <li class="py-2 flex justify-between gap-3"><span class="text-gray-800">{{ $r->title }}</span><span class="{{ $r->passed ? 'text-green-700' : 'text-red-700' }} font-semibold whitespace-nowrap">{{ rtrim(rtrim(number_format((float) $r->total, 2), '0'), '.') }} · {{ $r->grade }}</span></li>
                @empty
                    <li class="py-2 text-gray-500">No results published yet.</li>
                @endforelse
            </ul>
        </x-dash.panel>
        <x-dash.panel title="Library books" :href="route('student.library.index')">
            <ul class="divide-y text-sm">
                @forelse($onLoan as $loan)
                    <li class="py-2 flex justify-between gap-3"><span class="text-gray-800">{{ $loan->item->book->title ?? 'Book' }}</span>
                        <span class="whitespace-nowrap {{ $loan->days_overdue > 0 ? 'text-red-700 font-semibold' : 'text-gray-500' }}">{{ $loan->days_overdue > 0 ? $loan->days_overdue . ' days late' : 'due ' . $loan->due_date?->format('d M') }}</span></li>
                @empty
                    <li class="py-2 text-gray-500">No books on loan.</li>
                @endforelse
            </ul>
        </x-dash.panel>
    </div>
@endif
</div>
@endsection
