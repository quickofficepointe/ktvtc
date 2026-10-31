{{-- Fare records table. Expects $rows (FareDisbursement collection) and $showStudent (bool). --}}
<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2 px-3">Given on</th>
                @if($showStudent)<th class="py-2 px-3">Student</th>@endif
                <th class="py-2 px-3 text-right">Cash</th>
                <th class="py-2 px-3 text-right">Fee</th>
                <th class="py-2 px-3 text-right">Deducted</th>
                <th class="py-2 px-3">Recorded</th>
                <th class="py-2 px-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $d)
                <tr class="border-b {{ $d->status === 'reversed' ? 'text-gray-400 line-through' : '' }}">
                    <td class="py-2 px-3 whitespace-nowrap">
                        {{ $d->given_on->format('d M Y') }}
                        @if($d->is_backdated)<span class="text-xs bg-purple-100 text-purple-700 px-1.5 rounded no-underline">past</span>@endif
                    </td>
                    @if($showStudent)
                        <td class="py-2 px-3">{{ $d->student->full_name ?? '—' }} <span class="text-xs text-gray-400">{{ $d->student->admission_number ?? '' }}</span></td>
                    @endif
                    <td class="py-2 px-3 text-right">{{ number_format($d->amount, 2) }}</td>
                    <td class="py-2 px-3 text-right">{{ number_format($d->withdrawal_fee, 2) }}</td>
                    <td class="py-2 px-3 text-right font-semibold">{{ number_format($d->total_deducted, 2) }}</td>
                    <td class="py-2 px-3 text-xs text-gray-500">
                        {{ $d->recorder->name ?? '—' }}<br>{{ $d->created_at->format('d M H:i') }}
                        @if($d->notes)<br><span class="italic">{{ $d->notes }}</span>@endif
                        @if($d->status === 'reversed')<br><span class="no-underline text-red-600">Reversed: {{ $d->reversal_reason }}</span>@endif
                    </td>
                    <td class="py-2 px-3 text-right">
                        @if($d->status === 'given')
                            <form method="POST" action="{{ route('finance.transport.reverse', $d) }}"
                                  onsubmit="const r = prompt('Why are you reversing this fare record?'); if (!r || r.length < 5) return false; this.reason.value = r; return true;">
                                @csrf
                                <input type="hidden" name="reason">
                                <button class="text-xs text-red-600 hover:underline">Reverse</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $showStudent ? 7 : 6 }}" class="py-6 text-center text-gray-500">No fare recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
