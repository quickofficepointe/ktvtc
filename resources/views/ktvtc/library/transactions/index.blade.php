@extends('ktvtc.library.layout.librarylayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Library Transactions</h1>
            <p class="text-gray-600 mt-2">Manage book borrowing and returns</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openBulkReturnModal()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Bulk Return
            </button>
            <button onclick="openCreateModal()"
                class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Transaction
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Borrows</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeBorrows }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Overdue</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $overdueCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Returned Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $returnedToday }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Fines</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($totalFines, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="flex flex-wrap gap-4">
            <button onclick="filterByStatus('borrowed')"
                class="bg-white hover:bg-amber-100 text-amber-700 px-4 py-2 rounded-lg border border-amber-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Active Borrows
            </button>
            <button onclick="filterByStatus('overdue')"
                class="bg-white hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg border border-red-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Overdue
            </button>
            <button onclick="filterByStatus('returned')"
                class="bg-white hover:bg-green-100 text-green-700 px-4 py-2 rounded-lg border border-green-300 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Returned
            </button>
            <button onclick="clearFilters()"
                class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg transition-colors">
                Clear Filters
            </button>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="searchInput" placeholder="Member, Book, Transaction ID..."
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="borrowed">Borrowed</option>
                    <option value="returned">Returned</option>
                    <option value="overdue">Overdue</option>
                    <option value="lost">Lost</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <select id="dateFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()"
                    class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Transaction History</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Transaction Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Fines</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($transaction->item->book->cover_image)
                                            <img src="{{ asset('storage/' . $transaction->item->book->cover_image) }}"
                                                 alt="{{ $transaction->item->book->title }}"
                                                 class="w-12 h-16 object-cover rounded-lg shadow-sm border border-amber-200">
                                        @else
                                            <div class="w-12 h-16 bg-amber-100 rounded-lg flex items-center justify-center border border-amber-200">
                                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 font-serif">
                                            {{ $transaction->item->book->title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Borrowed by: {{ $transaction->member->first_name }} {{ $transaction->member->last_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Item: {{ $transaction->item->barcode }} • {{ $transaction->branch->name }}
                                        </p>
                                        @if($transaction->notes)
                                            <p class="text-xs text-gray-400 mt-1 italic">"{{ Str::limit($transaction->notes, 50) }}"</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1 text-sm">
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Borrowed: {{ $transaction->borrow_date->format('M d, Y') }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Due: {{ $transaction->due_date->format('M d, Y') }}
                                    </div>
                                    @if($transaction->return_date)
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Returned: {{ $transaction->return_date->format('M d, Y') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($transaction->fine_amount > 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        ${{ number_format($transaction->fine_amount, 2) }}
                                    </span>
                                    @if($transaction->status === 'returned')
                                        <p class="text-xs text-gray-500 mt-1">Paid</p>
                                    @else
                                        <p class="text-xs text-red-500 mt-1">Unpaid</p>
                                    @endif
                                @else
                                    <span class="text-green-600 text-sm font-medium">No fines</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'borrowed' => 'bg-blue-100 text-blue-800',
                                        'returned' => 'bg-green-100 text-green-800',
                                        'overdue' => 'bg-red-100 text-red-800',
                                        'lost' => 'bg-gray-100 text-gray-800'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$transaction->status] }}">
                                    <span class="w-2 h-2 rounded-full mr-2
                                        {{ $transaction->status === 'borrowed' ? 'bg-blue-400' :
                                           ($transaction->status === 'returned' ? 'bg-green-400' :
                                           ($transaction->status === 'overdue' ? 'bg-red-400' : 'bg-gray-400')) }}">
                                    </span>
                                    {{ ucfirst($transaction->status) }}
                                </span>
                                @if($transaction->status === 'borrowed' && $transaction->due_date->isPast())
                                    <p class="text-xs text-red-500 mt-1">
                                        {{ $transaction->due_date->diffForHumans() }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    @if($transaction->status === 'borrowed')
                                        <button onclick="returnBook({{ $transaction->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm leading-4 font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Return
                                        </button>
                                    @endif
                                    <button onclick="viewTransaction({{ $transaction->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No transactions found</h3>
                                    <p class="text-gray-600 mb-4">Start by creating a new borrowing transaction.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Create Transaction
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-amber-200 bg-amber-50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create Transaction Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white font-serif">New Borrow</h3>
                            <p class="text-amber-100 text-sm">Create a new book borrowing transaction</p>
                        </div>
                    </div>
                    <button onclick="closeCreateModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form action="{{ route('transactions.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Member *</label>
                            <select name="member_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select Member</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->first_name }} {{ $member->last_name }} ({{ $member->member_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Book Item *</label>
                            <select name="item_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select Available Book</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->book->title }} ({{ $item->barcode }}) - {{ $item->branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Borrow Date *</label>
                            <input type="date" name="borrow_date" value="{{ old('borrow_date', date('Y-m-d')) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                            <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                      placeholder="Any special notes about this transaction">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700 transition-colors shadow-lg hover:shadow-xl">
                            Create Borrow
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function filterByStatus(status) {
        window.location.href = '{{ route('transactions.index') }}?status=' + status;
    }

    function clearFilters() {
        window.location.href = '{{ route('transactions.index') }}';
    }

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const date = document.getElementById('dateFilter').value;

        let url = '{{ route('transactions.index') }}?';
        if (search) url += `search=${encodeURIComponent(search)}&`;
        if (status) url += `status=${status}&`;
        if (date) url += `date=${date}&`;

        window.location.href = url.slice(0, -1);
    }

    function returnBook(transactionId) {
        if(confirm('Mark this book as returned?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/transactions/${transactionId}/return`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function viewTransaction(transactionId) {
        window.location.href = `/transactions/${transactionId}`;
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
        }
    });
</script>
@endsection
