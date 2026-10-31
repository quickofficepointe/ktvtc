@extends('ktvtc.library.layout.librarylayout')

@section('title', 'Loan #' . $transaction->id)

@section('content')
<div class="max-w-3xl space-y-6">
    @foreach(['success' => 'bg-green-50 border-green-500 text-green-800', 'error' => 'bg-red-50 border-red-500 text-red-800'] as $key => $classes)
        @if(session($key))<div class="border-l-4 rounded-lg px-4 py-3 text-sm {{ $classes }}">{{ session($key) }}</div>@endif
    @endforeach

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <a href="{{ route('library.transactions.index') }}" class="text-sm text-amber-700 hover:underline">&larr; Transactions</a>
        <h1 class="text-xl font-bold text-gray-800 mt-1">{{ $transaction->item->book->title ?? 'Book' }}</h1>
        <p class="text-sm text-gray-500">Copy <span class="font-mono">{{ $transaction->item->barcode ?? '—' }}</span> · {{ $transaction->branch->name ?? '' }}</p>

        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm mt-5">
            <dt class="text-gray-500">Member</dt>
            <dd>@if($transaction->member)<a class="text-amber-700 hover:underline" href="{{ route('library.members.show', $transaction->member) }}">{{ $transaction->member->full_name }}</a> <span class="font-mono text-xs">{{ $transaction->member->member_id }}</span>@else — @endif</dd>
            <dt class="text-gray-500">Borrowed</dt><dd>{{ $transaction->borrow_date?->format('d M Y') }} {{ $transaction->issuer ? 'by ' . $transaction->issuer->name : '' }}</dd>
            <dt class="text-gray-500">Due</dt><dd>{{ $transaction->due_date?->format('d M Y') }} @if($transaction->days_overdue > 0)<span class="text-red-600">({{ $transaction->days_overdue }} day(s) late)</span>@endif</dd>
            <dt class="text-gray-500">Returned</dt><dd>{{ $transaction->return_date?->format('d M Y') ?? 'Not yet' }} {{ $transaction->receiver ? 'to ' . $transaction->receiver->name : '' }}</dd>
            <dt class="text-gray-500">Fine</dt><dd>KES {{ number_format((float) $transaction->fine_amount, 2) }}</dd>
            <dt class="text-gray-500">Status</dt><dd>{{ ucfirst($transaction->status) }}</dd>
            @if($transaction->notes)<dt class="text-gray-500">Notes</dt><dd>{{ $transaction->notes }}</dd>@endif
        </dl>

        @if(!$transaction->return_date)
            <form method="POST" action="{{ route('library.transactions.return', $transaction) }}" class="mt-6" onsubmit="return confirm('Mark this book as returned?')">
                @csrf
                <button class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm">Mark returned</button>
            </form>
        @endif
    </div>
</div>
@endsection
