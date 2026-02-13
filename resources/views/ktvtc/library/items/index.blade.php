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
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Book Items</h1>
            <p class="text-gray-600 mt-2">Manage physical book copies and inventory</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openBulkCreateModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                </svg>
                Bulk Add
            </button>
            <button onclick="openCreateModal()"
                class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Item
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $items->total() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Available</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $availableCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Borrowed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $borrowedCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Under Maintenance</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $maintenanceCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="searchInput" placeholder="Barcode, Book Title..."
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="available">Available</option>
                    <option value="borrowed">Borrowed</option>
                    <option value="reserved">Reserved</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="lost">Lost</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                <select id="conditionFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Conditions</option>
                    <option value="new">New</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                <select id="branchFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
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

    {{-- Items Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Inventory Items</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Item Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Condition & Notes</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Acquisition</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($item->book->cover_image)
                                            <img src="{{ asset('storage/' . $item->book->cover_image) }}"
                                                 alt="{{ $item->book->title }}"
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
                                            {{ $item->book->title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1 font-mono">
                                            <strong>Barcode:</strong> {{ $item->barcode }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            @if($item->book->authors->count() > 0)
                                                By {{ $item->book->authors->pluck('full_name')->join(', ') }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            ISBN: {{ $item->book->isbn ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $item->branch->name }}
                                    </span>
                                    @if($item->book->location)
                                        <p class="text-sm text-gray-600 mt-1">
                                            <svg class="w-4 h-4 inline mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ $item->book->location }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    @php
                                        $conditionColors = [
                                            'new' => 'bg-green-100 text-green-800',
                                            'good' => 'bg-blue-100 text-blue-800',
                                            'fair' => 'bg-yellow-100 text-yellow-800',
                                            'poor' => 'bg-red-100 text-red-800'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $conditionColors[$item->condition] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($item->condition) }}
                                    </span>
                                    @if($item->notes)
                                        <p class="text-xs text-gray-500 italic">
                                            "{{ Str::limit($item->notes, 50) }}"
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1 text-sm">
                                    @if($item->acquisition_date)
                                        <p class="text-gray-600">
                                            <svg class="w-4 h-4 inline mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $item->acquisition_date->format('M d, Y') }}
                                        </p>
                                    @endif
                                    @if($item->acquisition_price)
                                        <p class="text-gray-600">
                                            <svg class="w-4 h-4 inline mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                            </svg>
                                            ${{ number_format($item->acquisition_price, 2) }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'available' => 'bg-green-100 text-green-800',
                                        'borrowed' => 'bg-blue-100 text-blue-800',
                                        'reserved' => 'bg-purple-100 text-purple-800',
                                        'maintenance' => 'bg-yellow-100 text-yellow-800',
                                        'lost' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$item->status] }}">
                                    <span class="w-2 h-2 rounded-full mr-2
                                        {{ $item->status === 'available' ? 'bg-green-400' :
                                           ($item->status === 'borrowed' ? 'bg-blue-400' :
                                           ($item->status === 'reserved' ? 'bg-purple-400' :
                                           ($item->status === 'maintenance' ? 'bg-yellow-400' : 'bg-red-400'))) }}">
                                    </span>
                                    {{ ucfirst($item->status) }}
                                </span>
                                @if($item->status === 'borrowed')
                                    <p class="text-xs text-gray-500 mt-1">
                                        Due: {{ optional($item->currentTransaction)->due_date->format('M d') ?? 'N/A' }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditModal({{ $item->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-amber-300 shadow-sm text-sm leading-4 font-medium rounded-md text-amber-700 bg-white hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    @if($item->status === 'available')
                                        <button onclick="borrowItem({{ $item->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm leading-4 font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                            Borrow
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No items found</h3>
                                    <p class="text-gray-600 mb-4">Start by adding physical book copies to your inventory.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Add First Item
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-amber-200 bg-amber-50">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create Item Modal -->
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
                            <h3 class="text-xl font-bold text-white font-serif">Add Book Item</h3>
                            <p class="text-amber-100 text-sm">Add a physical copy to inventory</p>
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
                <form action="{{ route('items.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Barcode *</label>
                            <div class="flex items-center space-x-3">
                                <input type="text" name="barcode" value="{{ old('barcode') }}" required
                                       class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors font-mono"
                                       placeholder="LIB-001-2024">
                                <button type="button" onclick="generateBarcode()"
                                        class="px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    Generate
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Book *</label>
                            <select name="book_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select Book</option>
                                @foreach($books as $book)
                                    <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                        {{ $book->title }} ({{ $book->isbn ?? 'No ISBN' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                            <select name="branch_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                                <select name="condition"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                    <option value="good" {{ old('condition', 'good') == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('condition') == 'poor' ? 'selected' : '' }}>Poor</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                    <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Acquisition Date</label>
                                <input type="date" name="acquisition_date" value="{{ old('acquisition_date', date('Y-m-d')) }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Acquisition Price ($)</label>
                                <input type="number" name="acquisition_price" value="{{ old('acquisition_price') }}" step="0.01" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                       placeholder="29.99">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                      placeholder="Any notes about this specific copy">{{ old('notes') }}</textarea>
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
                            Add Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Generate barcode function
    function generateBarcode() {
        const prefix = 'LIB';
        const random = Math.floor(100000 + Math.random() * 900000);
        const date = new Date().getFullYear().toString().slice(-2);
        document.querySelector('input[name="barcode"]').value = `${prefix}-${random}-${date}`;
    }

    // Modal Functions
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const condition = document.getElementById('conditionFilter').value;
        const branch = document.getElementById('branchFilter').value;

        let url = '{{ route('items.index') }}?';
        if (search) url += `search=${encodeURIComponent(search)}&`;
        if (status) url += `status=${status}&`;
        if (condition) url += `condition=${condition}&`;
        if (branch) url += `branch=${branch}&`;

        window.location.href = url.slice(0, -1);
    }

    function borrowItem(itemId) {
        window.location.href = `/transactions/create?item_id=${itemId}`;
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
        }
    });
</script>
@endsection
