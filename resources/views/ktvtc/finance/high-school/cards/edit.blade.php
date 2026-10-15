{{-- resources/views/ktvtc/finance/high-school/cards/edit.blade.php --}}

@extends('ktvtc.finance.layouts.app')

@section('title', 'Edit Card - ' . $cardAccount->card_number)
@section('subtitle', 'Update card limits and settings')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('cards.index') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            Cards
        </a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('cards.show', $cardAccount) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            {{ $cardAccount->card_number }}
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Edit</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('cards.show', $cardAccount) }}"
       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Card
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    {{-- Student Summary --}}
    <div class="finance-card p-5">
        <div class="flex items-start">
            @php
                $student = $cardAccount->student;
                $photoUrl = $student && $student->profile_picture
                    ? asset('storage/' . $student->profile_picture)
                    : asset('images/default/avatar.png');
            @endphp
            <img src="{{ $photoUrl }}" alt="Student" class="w-14 h-14 rounded-full object-cover border-2 border-gray-200">
            <div class="ml-4 flex-1">
                <h3 class="text-base font-bold text-gray-800">{{ $cardAccount->student_name }}</h3>
                <p class="text-xs text-gray-500">Admission: {{ $cardAccount->student_admission_number }}</p>
                <p class="text-xs text-gray-500">Class: {{ $cardAccount->student_class }}</p>
                <p class="text-xs text-gray-500">Card: <span class="font-mono">{{ $cardAccount->card_number }}</span></p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Current Balance</p>
                <p class="text-xl font-bold text-green-600">KES {{ number_format($cardAccount->balance, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('cards.update', $cardAccount) }}">
        @csrf
        @method('PUT')

        <div class="finance-card p-5">
            <h4 class="font-bold text-gray-800 text-sm mb-4 flex items-center">
                <i class="fas fa-sliders-h text-primary mr-2"></i> Card Limits
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Daily Limit --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                        Daily Spending Limit (KES)
                    </label>
                    <input type="number" name="daily_limit" step="0.01" min="0"
                           value="{{ old('daily_limit', $cardAccount->daily_limit ?? 500) }}"
                           required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary text-sm @error('daily_limit') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">
                        Maximum a student can spend per day.
                    </p>
                    @error('daily_limit')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Per Transaction Limit --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                        Per Transaction Limit (KES)
                    </label>
                    <input type="number" name="per_transaction_limit" step="0.01" min="0"
                           value="{{ old('per_transaction_limit', $cardAccount->per_transaction_limit ?? 300) }}"
                           required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary text-sm @error('per_transaction_limit') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">
                        Maximum a student can spend in one transaction.
                    </p>
                    @error('per_transaction_limit')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Low Balance Threshold --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                        Low Balance Threshold (KES)
                    </label>
                    <input type="number" name="low_balance_threshold" step="0.01" min="0"
                           value="{{ old('low_balance_threshold', $cardAccount->low_balance_threshold ?? 100) }}"
                           required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary text-sm @error('low_balance_threshold') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">
                        Alert parents when balance drops below this.
                    </p>
                    @error('low_balance_threshold')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Minimum Balance --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                        Minimum Balance (KES)
                    </label>
                    <input type="number" name="minimum_balance" step="0.01" min="0"
                           value="{{ old('minimum_balance', $cardAccount->minimum_balance ?? 0) }}"
                           required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary text-sm @error('minimum_balance') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">
                        Balance cannot go below this when spending.
                    </p>
                    @error('minimum_balance')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mt-4 text-xs text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                Changing limits affects future transactions only. Already-completed purchases are not affected.
            </div>
        </div>

        {{-- Read-only info --}}
        <div class="finance-card p-5">
            <h4 class="font-bold text-gray-800 text-sm mb-4 flex items-center">
                <i class="fas fa-lock text-gray-400 mr-2"></i> Read-Only Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs text-gray-500">Card Number</p>
                    <p class="font-mono font-semibold text-gray-800">{{ $cardAccount->card_number }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs text-gray-500">Account Number</p>
                    <p class="font-mono font-semibold text-gray-800">{{ $cardAccount->account_number }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs text-gray-500">Current Balance</p>
                    <p class="font-semibold text-green-600">KES {{ number_format($cardAccount->balance, 2) }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs text-gray-500">Total Funded</p>
                    <p class="font-semibold text-gray-800">KES {{ number_format($cardAccount->total_funded ?? 0, 2) }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs text-gray-500">Total Spent</p>
                    <p class="font-semibold text-gray-800">KES {{ number_format($cardAccount->total_spent ?? 0, 2) }}</p>
                </div>
                <div class="bg-gray-50 rounded p-3">
                    <p class="text-xs text-gray-500">Issued At</p>
                    <p class="font-semibold text-gray-800">
                        {{ $cardAccount->issued_at ? $cardAccount->issued_at->format('d M Y, H:i') : '—' }}
                    </p>
                </div>
            </div>

            <div class="mt-4 flex gap-2">
                <a href="{{ route('cards.show', $cardAccount) }}"
                   class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                    <i class="fas fa-external-link-alt mr-1"></i> View full card details
                </a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('cards.adjust-balance', $cardAccount) }}"
                   onclick="event.preventDefault(); window.location.href='{{ route('cards.show', $cardAccount) }}';"
                   class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                    <i class="fas fa-sliders-h mr-1"></i> Adjust balance from card page
                </a>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('cards.show', $cardAccount) }}"
               class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                Cancel
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-semibold">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
