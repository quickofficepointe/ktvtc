@extends('ktvtc.finance.layouts.app')

@section('title', 'Transport Money')
@section('subtitle', 'Transport top-ups on student cards and the cash fare given out')

@section('breadcrumb')
<li><span class="mx-2">/</span><span class="text-gray-400">Transport</span></li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('finance.transport.give') }}" class="btn-primary text-white px-3 py-2 rounded-lg text-sm font-semibold">
        <i class="fas fa-hand-holding-usd mr-1"></i> Give fare
    </a>
    <a href="{{ route('finance.transport.batch') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-semibold">
        <i class="fas fa-users mr-1"></i> Fare for a class
    </a>
    <a href="{{ route('finance.transport.fees') }}" class="border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm">
        <i class="fas fa-percent mr-1"></i> Withdrawal charges
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    <form method="GET" class="finance-card p-4 flex flex-wrap items-end gap-3 text-sm">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Class</label>
            <select name="class" class="border rounded-lg px-3 py-2">
                <option value="">All classes</option>
                @foreach($classes as $c)
                    <option value="{{ $c }}" @selected(request('class') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs text-gray-500 mb-1">Student</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, admission or card no." class="w-full border rounded-lg px-3 py-2">
        </div>
        <button class="btn-primary text-white px-4 py-2 rounded-lg"><i class="fas fa-filter mr-1"></i>Apply</button>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="finance-card p-4 bg-blue-50 border-blue-200">
            <p class="text-xs text-gray-600">Transport money held now</p>
            <p class="text-2xl font-bold text-blue-700">KES {{ number_format($summary['liability'], 2) }}</p>
            <p class="text-xs text-gray-500">owed to students as fare</p>
        </div>
        <div class="finance-card p-4 bg-green-50 border-green-200">
            <p class="text-xs text-gray-600">Topped up in period</p>
            <p class="text-2xl font-bold text-green-700">KES {{ number_format($summary['funded'], 2) }}</p>
        </div>
        <div class="finance-card p-4 bg-purple-50 border-purple-200">
            <p class="text-xs text-gray-600">Fare given in period</p>
            <p class="text-2xl font-bold text-purple-700">KES {{ number_format($summary['given'], 2) }}</p>
            <p class="text-xs text-gray-500">{{ number_format($summary['count']) }} hand-outs</p>
        </div>
        <div class="finance-card p-4 bg-yellow-50 border-yellow-200">
            <p class="text-xs text-gray-600">Withdrawal charges collected</p>
            <p class="text-2xl font-bold text-yellow-700">KES {{ number_format($summary['fees'], 2) }}</p>
            <p class="text-xs text-gray-500">covers M-Pesa cash withdrawals</p>
        </div>
    </div>

    @if($summary['unsplit'] > 0)
        <div class="finance-card p-4 bg-yellow-50 border-yellow-200 text-sm text-yellow-900">
            <i class="fas fa-info-circle mr-1"></i>
            {{ $summary['unsplit'] }} cards still hold one combined balance from before transport and canteen were separated.
            They are split automatically the first time they are used, or all at once with <code>php artisan cards:split-wallets</code>.
            Until then they show KES 0 here.
        </div>
    @endif

    <div class="finance-card p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Students</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 px-3">Student</th>
                        <th class="py-2 px-3">Class</th>
                        <th class="py-2 px-3 text-right">Transport</th>
                        <th class="py-2 px-3 text-right">Canteen</th>
                        <th class="py-2 px-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cards as $card)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">
                                {{ $card->student_name }}
                                <div class="text-xs text-gray-400">Adm {{ $card->student_admission_number }} · {{ $card->card_number }}</div>
                            </td>
                            <td class="py-2 px-3">{{ $card->student_class }}</td>
                            <td class="py-2 px-3 text-right font-semibold {{ $card->transport_balance < 0 ? 'text-red-600' : 'text-blue-700' }}">
                                {{ $card->wallets_split_at ? 'KES ' . number_format($card->transport_balance, 2) : '—' }}
                            </td>
                            <td class="py-2 px-3 text-right">
                                {{ $card->wallets_split_at ? 'KES ' . number_format($card->canteen_balance, 2) : '—' }}
                            </td>
                            <td class="py-2 px-3 text-right whitespace-nowrap">
                                <a href="{{ route('finance.transport.give', ['card' => $card->id]) }}" class="btn-primary text-white px-3 py-1 rounded-lg text-xs">Give fare</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-500">No cards match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $cards->links() }}</div>
    </div>

    <div class="finance-card p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Latest fare records</h3>
        @include('ktvtc.finance.transport.partials.history', ['rows' => $recent, 'showStudent' => true])
    </div>
</div>
@endsection
