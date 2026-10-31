@extends('ktvtc.library.layout.librarylayout')

@section('title', $member->full_name . ' | Library member')

@section('content')
<div class="space-y-6">
    @foreach(['success' => 'bg-green-50 border-green-500 text-green-800', 'error' => 'bg-red-50 border-red-500 text-red-800'] as $key => $classes)
        @if(session($key))<div class="border-l-4 rounded-lg px-4 py-3 text-sm {{ $classes }}">{{ session($key) }}</div>@endif
    @endforeach
    @if($errors->any())<div class="border-l-4 rounded-lg px-4 py-3 text-sm bg-red-50 border-red-500 text-red-800">{{ $errors->first() }}</div>@endif

    <div class="bg-white rounded-xl shadow-sm border p-6 flex flex-wrap justify-between gap-4">
        <div>
            <a href="{{ route('library.members.index') }}" class="text-sm text-amber-700 hover:underline">&larr; Members</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $member->full_name }}</h1>
            <p class="text-sm text-gray-500">
                <span class="font-mono">{{ $member->member_id }}</span> · {{ ucfirst($member->membership_type) }}
                @if($member->student) · student {{ $member->student->student_number }} @endif
                · {{ $member->branch->name ?? 'No branch' }}
            </p>
            <p class="text-sm text-gray-500">{{ $member->phone }} {{ $member->email }}</p>
        </div>
        <div class="flex gap-6 text-center">
            <div><p class="text-2xl font-bold">{{ $member->transactions->whereIn('status', ['borrowed', 'overdue'])->count() }} / {{ $member->max_borrow_limit }}</p><p class="text-xs text-gray-500">On loan</p></div>
            <div><p class="text-2xl font-bold {{ $member->outstanding_fines > 0 ? 'text-red-600' : '' }}">KES {{ number_format((float) $member->outstanding_fines, 2) }}</p><p class="text-xs text-gray-500">Unpaid fines</p></div>
            <div><p class="text-sm font-semibold mt-2">{{ $member->membership_end_date?->format('d M Y') ?? '—' }}</p><p class="text-xs text-gray-500">Membership ends</p></div>
        </div>
    </div>

    @if($blockers)
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg px-4 py-3 text-sm text-yellow-800">
            <p class="font-semibold">Cannot borrow now:</p>
            <ul class="list-disc ml-5">@foreach($blockers as $b)<li>{{ $b }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-6 overflow-x-auto">
            <h2 class="font-semibold text-gray-800 mb-3">Loans</h2>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Book</th><th class="py-2">Borrowed</th><th class="py-2">Due</th><th class="py-2">Returned</th><th class="py-2 text-right">Fine</th><th></th></tr></thead>
                <tbody>
                    @forelse($member->transactions as $loan)
                        <tr class="border-b align-top">
                            <td class="py-2">{{ $loan->item->book->title ?? '—' }}<br><span class="text-xs text-gray-400 font-mono">{{ $loan->item->barcode ?? '' }}</span></td>
                            <td class="py-2 whitespace-nowrap">{{ $loan->borrow_date?->format('d M Y') }}<br><span class="text-xs text-gray-400">{{ $loan->issuer->name ?? '' }}</span></td>
                            <td class="py-2 whitespace-nowrap {{ !$loan->return_date && $loan->days_overdue > 0 ? 'text-red-600 font-semibold' : '' }}">
                                {{ $loan->due_date?->format('d M Y') }}
                                @if(!$loan->return_date && $loan->days_overdue > 0)<br><span class="text-xs">{{ $loan->days_overdue }} day(s) late</span>@endif
                            </td>
                            <td class="py-2 whitespace-nowrap">{{ $loan->return_date?->format('d M Y') ?? '—' }}<br><span class="text-xs text-gray-400">{{ $loan->receiver->name ?? '' }}</span></td>
                            <td class="py-2 text-right">{{ $loan->fine_amount > 0 ? number_format((float) $loan->fine_amount, 2) : '' }}</td>
                            <td class="py-2 text-right">
                                @if(!$loan->return_date)
                                    <form method="POST" action="{{ route('library.transactions.return', $loan) }}" onsubmit="return confirm('Mark this book as returned?')">
                                        @csrf
                                        <button class="text-xs bg-amber-600 text-white px-3 py-1 rounded">Return</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-center text-gray-500">No loans yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="font-semibold text-gray-800 mb-3">Record a fine payment</h2>
                @if($member->outstanding_fines > 0)
                    <form method="POST" action="{{ route('library.members.pay-fine', $member) }}" class="space-y-3 text-sm">
                        @csrf
                        <input type="number" name="amount" step="0.01" min="1" max="{{ $member->outstanding_fines }}" value="{{ $member->outstanding_fines }}" required class="w-full border rounded-lg px-3 py-2" placeholder="Amount">
                        <select name="payment_method" class="w-full border rounded-lg px-3 py-2">
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="bank">Bank</option>
                        </select>
                        <input type="text" name="reference_number" class="w-full border rounded-lg px-3 py-2" placeholder="M-Pesa code / receipt no. (optional)">
                        <button class="w-full bg-amber-600 text-white rounded-lg py-2">Record payment</button>
                    </form>
                @else
                    <p class="text-sm text-gray-500">No fines owed.</p>
                @endif
                @if($member->payments->isNotEmpty())
                    <div class="mt-4 text-sm">
                        @foreach($member->payments as $p)
                            <p class="flex justify-between border-b py-1"><span>{{ $p->payment_date?->format('d M Y') }} · {{ $p->payment_method }} · <span class="font-mono text-xs">{{ $p->reference_number }}</span></span><span>{{ number_format((float) $p->amount, 2) }}</span></p>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="font-semibold text-gray-800 mb-3">Waiting list</h2>
                @forelse($member->reservations->where('status', 'active') as $r)
                    <p class="text-sm border-b py-1">{{ $r->book->title ?? '—' }}
                        <span class="text-xs {{ $r->heldItem ? 'text-green-700' : 'text-gray-500' }}">{{ $r->heldItem ? 'copy ' . $r->heldItem->barcode . ' held' : 'no. ' . $r->queue_position }}</span>
                    </p>
                @empty
                    <p class="text-sm text-gray-500">Not waiting for any book.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
