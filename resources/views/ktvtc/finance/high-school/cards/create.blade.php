@extends('ktvtc.finance.layouts.app')

@section('title', 'Issue Card')
@section('subtitle', 'Issue a new card to a student')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('finance.cards.index') }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-primary md:ml-2">
            Cards
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Issue Card</span>
    </div>
</li>
@endsection

@section('content')
<div class="w-full max-w-full overflow-hidden">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <form method="POST" action="{{ route('finance.cards.store') }}">
                @csrf

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Select Student <span class="text-red-500">*</span>
                        </label>

                        <select name="student_id"
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                                required>
                            <option value="">Select a student...</option>

                            @foreach($students ?? [] as $student)
                                <option value="{{ $student->id }}"
                                    {{ (isset($selectedStudent) && $selectedStudent->id == $student->id) || old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->full_name }}
                                    ({{ $student->admission_number ?? $student->student_number ?? 'N/A' }} - {{ $student->class ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>

                        @error('student_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Daily Limit</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500 text-sm">KES</span>
                                <input type="number"
                                       name="daily_limit"
                                       value="{{ old('daily_limit', 500) }}"
                                       min="0"
                                       step="1"
                                       class="w-full pl-14 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                            </div>

                            @error('daily_limit')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Per Transaction Limit</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500 text-sm">KES</span>
                                <input type="number"
                                       name="per_transaction_limit"
                                       value="{{ old('per_transaction_limit', 300) }}"
                                       min="0"
                                       step="1"
                                       class="w-full pl-14 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                            </div>

                            @error('per_transaction_limit')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        The card will be issued with a zero balance. Parent/guardian will need to fund the card before use.
                    </div>

                    <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('finance.cards.index') }}"
                           class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm">
                            Cancel
                        </a>

                        <button type="submit"
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold text-sm">
                            <i class="fas fa-credit-card mr-2"></i> Issue Card
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
