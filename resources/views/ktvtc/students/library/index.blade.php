@extends('ktvtc.students.layout.studentlayout')

@section('title', 'My Library')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">My Library</h2>
            <p class="text-sm text-gray-500">Member number <span class="font-mono">{{ $member->member_id }}</span>. Borrow and return books at the library desk.</p>
        </div>
        <div class="flex gap-6 text-center">
            <div><p class="text-2xl font-bold text-gray-800">{{ $onLoan->count() }} / {{ $member->max_borrow_limit }}</p><p class="text-xs text-gray-500">Books on loan</p></div>
            <div><p class="text-2xl font-bold {{ $member->outstanding_fines > 0 ? 'text-red-600' : 'text-gray-800' }}">KES {{ number_format((float) $member->outstanding_fines, 2) }}</p><p class="text-xs text-gray-500">Unpaid fines</p></div>
        </div>
    </div>

    @if($blockers)
        <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg px-4 py-3 text-sm text-yellow-800">
            <p class="font-semibold">You can't borrow another book right now:</p>
            <ul class="list-disc ml-5">@foreach($blockers as $b)<li>{{ $b }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-3">On loan</h3>
        @forelse($onLoan as $loan)
            @php $late = $loan->days_overdue; @endphp
            <div class="flex flex-wrap items-center justify-between border-b py-2 text-sm gap-2">
                <div>
                    <p class="font-medium text-gray-800">{{ $loan->item->book->title ?? 'Book' }}</p>
                    <p class="text-xs text-gray-500">Copy {{ $loan->item->barcode ?? '' }} · borrowed {{ $loan->borrow_date?->format('d M Y') }}</p>
                </div>
                @if($late > 0)
                    <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-semibold">{{ $late }} day(s) late, due {{ $loan->due_date->format('d M Y') }}</span>
                @else
                    <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs">Due {{ $loan->due_date?->format('d M Y') }}</span>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">You have no books on loan.</p>
        @endforelse
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-3">Waiting list</h3>
        @forelse($member->reservations as $r)
            <div class="flex flex-wrap items-center justify-between border-b py-2 text-sm gap-2">
                <div>
                    <p class="font-medium text-gray-800">{{ $r->book->title ?? 'Book' }}</p>
                    @if($r->heldItem)
                        <p class="text-xs text-green-700 font-semibold">A copy is waiting for you at the desk until {{ $r->held_until?->format('d M Y') }}.</p>
                    @else
                        <p class="text-xs text-gray-500">Number {{ $r->queue_position }} in the queue</p>
                    @endif
                </div>
                <form method="POST" action="{{ route('student.library.cancel', $r) }}" onsubmit="return confirm('Leave the waiting list for this book?')">
                    @csrf
                    <button class="text-xs text-red-600 hover:underline">Cancel</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">You are not waiting for any book.</p>
        @endforelse
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-3">Find a book</h3>
        <form method="GET" class="flex gap-2 mb-4">
            <input type="text" name="q" value="{{ $search }}" placeholder="Title or ISBN" class="flex-1 border rounded-lg px-3 py-2 text-sm">
            <button class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm">Search</button>
        </form>
        @if($search)
            @forelse($books as $book)
                <div class="flex flex-wrap items-center justify-between border-b py-2 text-sm gap-2">
                    <div>
                        <p class="font-medium text-gray-800">{{ $book->title }}</p>
                        <p class="text-xs text-gray-500">{{ $book->isbn }}</p>
                    </div>
                    @if($book->shelf_count > 0)
                        <span class="text-xs text-green-700">{{ $book->shelf_count }} on the shelf: borrow it at the desk</span>
                    @elseif($member->reservations->contains('book_id', $book->id))
                        <span class="text-xs text-gray-500">You are on the waiting list</span>
                    @else
                        <form method="POST" action="{{ route('student.library.reserve', $book) }}">
                            @csrf
                            <button class="text-xs bg-gray-800 text-white px-3 py-1.5 rounded">All out: join the waiting list</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No books match "{{ $search }}".</p>
            @endforelse
        @endif
    </div>

    @if($history->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Returned</h3>
            <table class="w-full text-sm">
                <tbody>
                    @foreach($history as $loan)
                        <tr class="border-b">
                            <td class="py-2">{{ $loan->item->book->title ?? 'Book' }}</td>
                            <td class="py-2 text-gray-500">{{ $loan->borrow_date?->format('d M Y') }} – {{ $loan->return_date?->format('d M Y') }}</td>
                            <td class="py-2 text-right">@if($loan->fine_amount > 0)<span class="text-red-600">Fine KES {{ number_format((float) $loan->fine_amount, 2) }}</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
