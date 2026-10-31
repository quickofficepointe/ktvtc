@extends('ktvtc.students.layout.studentlayout')

@section('title', 'My Terms')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    @foreach(['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $key => $color)
        @if(session($key))
            <div class="rounded-lg border border-{{ $color }}-200 bg-{{ $color }}-50 text-{{ $color }}-800 px-4 py-3 text-sm">{{ session($key) }}</div>
        @endif
    @endforeach

    {{-- Identity --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center">
                <i class="fas fa-user-graduate text-primary text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-gray-800">{{ $student->full_name }}</h2>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-id-card mr-1"></i>{{ $student->student_number ?? '—' }}
                    @if($student->campus) · <i class="fas fa-building mr-1"></i>{{ $student->campus->name }} @endif
                </p>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-4">
            To register for your next term, at least {{ $threshold }}% of your current term must be paid.
            Your exam card for a term unlocks when that term is fully paid and you have evaluated your trainers.
        </p>
    </div>

    @forelse($courses as $c)
        @php $e = $c['enrollment']; $next = $c['next']; @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="font-bold text-gray-800 text-lg">
                    <i class="fas fa-book text-primary mr-2"></i>{{ $e->course_name }}
                    @if($e->course_code)<span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded ml-1">{{ $e->course_code }}</span>@endif
                </h3>
                <div class="text-sm text-gray-600">
                    Total owed: <strong class="{{ $e->balance > 0 ? 'text-red-600' : 'text-green-600' }}">KES {{ number_format($e->balance, 2) }}</strong>
                    @if($c['credit'] > 0) · Credit for future terms: <strong class="text-green-600">KES {{ number_format($c['credit'], 2) }}</strong>@endif
                </div>
            </div>

            @if($c['ledger']->isEmpty())
                <p class="text-sm text-gray-500">You are not registered for any term yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2 pr-3">Term</th>
                                <th class="py-2 pr-3">Dates</th>
                                <th class="py-2 pr-3 text-right">Fee</th>
                                <th class="py-2 pr-3 text-right">Paid</th>
                                <th class="py-2 pr-3 text-right">Balance</th>
                                <th class="py-2">Exam card</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($c['ledger'] as $row)
                                @php $reg = $row['registration']; @endphp
                                <tr class="border-b align-top">
                                    <td class="py-3 pr-3 font-semibold">Term {{ $row['term'] }}</td>
                                    <td class="py-3 pr-3 whitespace-nowrap">{{ $reg->starts_on->format('d M Y') }} – {{ $reg->ends_on->format('d M Y') }}</td>
                                    <td class="py-3 pr-3 text-right">{{ number_format($row['fee'], 2) }}</td>
                                    <td class="py-3 pr-3 text-right">
                                        {{ number_format($row['paid'], 2) }}
                                        <div class="w-24 ml-auto bg-gray-200 rounded-full h-1.5 mt-1">
                                            <div class="h-1.5 rounded-full {{ $row['percent'] >= 100 ? 'bg-green-500' : ($row['percent'] >= $threshold ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                 style="width: {{ min(100, $row['percent']) }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $row['percent'] }}%</span>
                                    </td>
                                    <td class="py-3 pr-3 text-right {{ $row['balance'] > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">{{ number_format($row['balance'], 2) }}</td>
                                    <td class="py-3">
                                        @if($row['evaluations']->isNotEmpty() && !$reg->starts_on->isFuture())
                                            @php $evDone = $row['evaluations']->where('evaluated', true)->count(); $evAll = $row['evaluations']->count(); @endphp
                                            <a href="{{ route('student.evaluations.show', $reg) }}" class="block text-xs mb-1 {{ $evDone < $evAll ? 'text-orange-700 font-semibold' : 'text-green-700' }} hover:underline">
                                                <i class="fas fa-star mr-1"></i>Evaluate trainers ({{ $evDone }}/{{ $evAll }})
                                            </a>
                                        @endif
                                        @if($row['exam_card']['allowed'])
                                            <a href="{{ route('student.enrollment.exam-card', $reg) }}"
                                               class="inline-flex items-center bg-primary hover:bg-primary-dark text-white px-3 py-1.5 rounded-lg text-xs font-semibold">
                                                <i class="fas fa-download mr-1"></i> Download
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-500"><i class="fas fa-lock mr-1"></i>{{ implode(' ', $row['exam_card']['reasons']) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($reg->fee_items)
                                    <tr class="border-b bg-gray-50">
                                        <td></td>
                                        <td colspan="5" class="py-2 text-xs text-gray-600">
                                            @foreach($reg->fee_items as $item)
                                                <span class="mr-3">{{ $item['name'] }}: {{ number_format($item['amount'], 2) }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Next term --}}
            @if($next['next_term'])
                <div class="rounded-lg border p-4 {{ $next['allowed'] ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-800">Term {{ $next['next_term'] }}</p>
                            @if($next['fee'] !== null)
                                <p class="text-sm text-gray-600">Term fee: KES {{ number_format($next['fee'], 2) }}</p>
                            @endif
                            @foreach($next['reasons'] as $reason)
                                <p class="text-sm text-gray-600"><i class="fas fa-info-circle mr-1 text-gray-400"></i>{{ $reason }}</p>
                            @endforeach
                        </div>
                        @if($next['allowed'])
                            <form method="POST" action="{{ route('student.enrollment.store') }}"
                                  onsubmit="return confirm('Register for Term {{ $next['next_term'] }}?')">
                                @csrf
                                <input type="hidden" name="enrollment_id" value="{{ $e->id }}">
                                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                                    <i class="fas fa-check mr-1"></i> Register for Term {{ $next['next_term'] }}
                                </button>
                            </form>
                        @endif
                    </div>
                    @if(!$next['allowed'] && $e->balance > 0)
                        <a href="{{ route('student.fees.index') }}" class="inline-block mt-3 text-sm text-primary hover:underline">
                            <i class="fas fa-mobile-alt mr-1"></i> Pay fees with M-Pesa
                        </a>
                    @endif
                </div>
            @elseif(!empty($next['reasons']))
                <p class="text-sm text-gray-500">{{ implode(' ', $next['reasons']) }}</p>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-sm text-gray-600">
            You don't have a course enrollment yet. Contact the admissions office.
        </div>
    @endforelse
</div>
@endsection
