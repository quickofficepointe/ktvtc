@extends('ktvtc.finance.layouts.app')

@section('title', 'Student Details')
@section('subtitle', 'View student information and card details')

@section('breadcrumb')
<li>
    <span class="mx-2">/</span>
    <a href="{{ route('finance.hs-students.index') }}" class="text-gray-600 hover:text-primary">High School Students</a>
</li>
<li>
    <span class="mx-2">/</span>
    <span class="text-gray-400">{{ $student->full_name }}</span>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('finance.hs-students.edit', $student) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-edit mr-2"></i> Edit
    </a>
    @if($student->cardAccount)
        <a href="{{ route('finance.cards.show', $student->cardAccount) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-credit-card mr-2"></i> View Card
        </a>
        <a href="{{ route('finance.student-fees.create', ['student_id' => $student->id]) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-credit-card mr-2"></i> Record Payment
        </a>
    @else
        <a href="{{ route('finance.cards.create', ['student_id' => $student->id]) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
            <i class="fas fa-credit-card mr-2"></i> Issue Card
        </a>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- Student Info -->
    <div class="lg:col-span-2 finance-card p-5">
        <div class="flex items-start">
            <div class="w-20 h-20 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                @if($student->profile_picture)
                    <img src="{{ asset('storage/' . $student->profile_picture) }}" alt="" class="w-full h-full object-cover">
                @else
                    <i class="fas fa-user text-gray-400 w-full h-full flex items-center justify-center text-3xl"></i>
                @endif
            </div>
            <div class="ml-4">
                <h3 class="text-xl font-bold text-gray-800">{{ $student->full_name }}</h3>
                <p class="text-sm text-gray-500">Admission: {{ $student->admission_number }}</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    <span class="text-xs px-2 py-0.5 bg-gray-100 rounded">{{ $student->class }}</span>
                    <span class="text-xs px-2 py-0.5 rounded
                        @if($student->status === 'active') bg-green-100 text-green-600
                        @elseif($student->status === 'inactive') bg-gray-100 text-gray-500
                        @else bg-blue-100 text-blue-600 @endif">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t">
            <div>
                <p class="text-xs text-gray-500">Parent/Guardian</p>
                <p class="font-medium text-sm">{{ $student->parent_name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Phone</p>
                <p class="font-medium text-sm">{{ $student->parent_phone ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Created At</p>
                <p class="text-sm">{{ $student->created_at->format('d M Y, H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Last Updated</p>
                <p class="text-sm">{{ $student->updated_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Card Info -->
    <div class="finance-card p-5">
        <h4 class="font-bold text-gray-800 mb-3 text-sm">Card Information</h4>
        @if($student->cardAccount)
            <div class="space-y-2">
                <div>
                    <p class="text-xs text-gray-500">Card Number</p>
                    <p class="font-mono font-semibold text-sm">{{ $student->cardAccount->card_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Account Number</p>
                    <p class="font-mono font-semibold text-sm">{{ $student->cardAccount->account_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Balance</p>
                    <p class="text-2xl font-bold text-green-600">KES {{ number_format($student->cardAccount->balance, 2) }}</p>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <p class="text-xs text-gray-500">Daily Limit</p>
                        <p class="font-medium text-sm">KES {{ number_format($student->cardAccount->daily_limit, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Today Spent</p>
                        <p class="font-medium text-sm">KES {{ number_format($student->cardAccount->today_spent ?? 0, 2) }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Status</p>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded
                            @if($student->cardAccount->is_active) bg-green-100 text-green-600
                            @else bg-gray-100 text-gray-500 @endif">
                            {{ $student->cardAccount->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($student->cardAccount->is_locked)
                            <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-600 rounded">Locked</span>
                        @endif
                        @if($student->cardAccount->is_blocked)
                            <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded">Blocked</span>
                        @endif
                    </div>
                </div>
                @if($student->cardAccount->qr_code)
                    <div class="mt-3 pt-3 border-t">
                        <p class="text-xs text-gray-500 mb-1">QR Code</p>
                        <img src="{{ Storage::url($student->cardAccount->qr_code) }}" alt="QR Code" class="w-24 h-24 object-contain border rounded-lg p-1">
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-6">
                <i class="fas fa-credit-card text-4xl text-gray-300 mb-2 block"></i>
                <p class="text-gray-500 text-sm">No card issued yet</p>
                <a href="{{ route('finance.cards.create', ['student_id' => $student->id]) }}" class="inline-block mt-2 text-primary hover:underline text-sm">
                    Issue Card Now →
                </a>
            </div>
        @endif
    </div>

    <!-- Recent Transactions -->
    <div class="lg:col-span-3 finance-card p-5">
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-bold text-gray-800">Recent Transactions</h4>
            @if($student->cardAccount)
                <a href="{{ route('finance.cards.transactions', $student->cardAccount) }}" class="text-sm text-primary hover:underline">View All →</a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Date</th>
                        <th class="pb-2 font-semibold">Type</th>
                        <th class="pb-2 font-semibold text-right">Amount</th>
                        <th class="pb-2 font-semibold text-right">Balance After</th>
                        <th class="pb-2 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions ?? [] as $transaction)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                            <td class="py-2">
                                <span class="text-xs px-2 py-0.5 rounded
                                    @if($transaction->transaction_type === 'funding') bg-green-100 text-green-600
                                    @elseif($transaction->transaction_type === 'purchase') bg-blue-100 text-blue-600
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ ucfirst($transaction->transaction_type) }}
                                </span>
                            </td>
                            <td class="py-2 text-right font-medium
                                @if($transaction->transaction_type === 'funding') text-green-600
                                @else text-red-600 @endif">
                                {{ $transaction->transaction_type === 'funding' ? '+' : '-' }}
                                KES {{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="py-2 text-right">KES {{ number_format($transaction->balance_after, 2) }}</td>
                            <td class="py-2">
                                <span class="text-xs px-2 py-0.5 rounded
                                    @if($transaction->status === 'completed') bg-green-100 text-green-600
                                    @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-600
                                    @else bg-red-100 text-red-600 @endif">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">No transactions yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
