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
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Weeding Candidates</h1>
            <p class="text-gray-600 mt-2">Identify and process books for removal from collection</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Candidate
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Candidates</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $candidates->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Review</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Approved</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $approvedCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $rejectedCount }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 font-serif mb-2">Filter Candidates</h3>
                <p class="text-sm text-gray-600">Filter by status, branch, or condition</p>
            </div>
            <div class="flex flex-wrap gap-4">
                <select id="statusFilter" onchange="applyFilters()"
                    class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="processed">Processed</option>
                </select>
                <select id="branchFilter" onchange="applyFilters()"
                    class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                <select id="conditionFilter" onchange="applyFilters()"
                    class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Conditions</option>
                    <option value="poor">Poor</option>
                    <option value="fair">Fair</option>
                    <option value="damaged">Damaged</option>
                    <option value="obsolete">Obsolete</option>
                </select>
                <button onclick="clearFilters()"
                    class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Candidates Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Candidate List</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Book Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Usage Metrics</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Condition</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Review</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($candidates as $candidate)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 font-serif">{{ $candidate->book->title }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">ISBN: {{ $candidate->book->isbn }}</p>
                                    <p class="text-sm text-gray-500 mt-2">{{ Str::limit($candidate->reason, 100) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center border border-blue-200">
                                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $candidate->branch->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $candidate->branch->code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Last Borrowed:</span>
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $candidate->last_borrowed_date ? $candidate->last_borrowed_date->format('M d, Y') : 'Never' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Days Since:</span>
                                        <span class="text-sm font-semibold {{ $candidate->days_since_last_borrow > 365 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $candidate->days_since_last_borrow }} days
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Total Borrows:</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ $candidate->total_borrows }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    {{ $candidate->condition === 'poor' ? 'bg-red-100 text-red-800' :
                                       ($candidate->condition === 'fair' ? 'bg-yellow-100 text-yellow-800' :
                                       ($candidate->condition === 'damaged' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($candidate->condition) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    {{ $candidate->status === 'pending' ? 'bg-blue-100 text-blue-800' :
                                       ($candidate->status === 'approved' ? 'bg-green-100 text-green-800' :
                                       ($candidate->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                    <span class="w-2 h-2 rounded-full mr-2
                                        {{ $candidate->status === 'pending' ? 'bg-blue-400' :
                                           ($candidate->status === 'approved' ? 'bg-green-400' :
                                           ($candidate->status === 'rejected' ? 'bg-red-400' : 'bg-gray-400')) }}"></span>
                                    {{ ucfirst($candidate->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($candidate->reviewed_by)
                                    <div class="space-y-1">
                                        <p class="text-sm text-gray-900 font-medium">{{ $candidate->reviewed_by }}</p>
                                        <p class="text-xs text-gray-500">{{ $candidate->review_date->format('M d, Y') }}</p>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Not reviewed</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openReviewModal({{ $candidate->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-amber-300 shadow-sm text-sm leading-4 font-medium rounded-md text-amber-700 bg-white hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Review
                                    </button>
                                    @if($candidate->can_be_processed)
                                        <button onclick="processCandidate({{ $candidate->id }})"
                                            class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm leading-4 font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Process
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No weeding candidates found</h3>
                                    <p class="text-gray-600 mb-4">Add candidates or adjust your filters.</p>
                                    <button onclick="openCreateModal()"
                                        class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Add First Candidate
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($candidates->hasPages())
        <div class="px-6 py-4 border-t border-amber-200 bg-amber-50">
            {{ $candidates->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create Candidate Modal -->
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
                            <h3 class="text-xl font-bold text-white font-serif">Add Weeding Candidate</h3>
                            <p class="text-amber-100 text-sm">Identify a book for potential removal</p>
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
                <form action="{{ route('weeding-candidates.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Book *</label>
                            <select name="book_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select a book</option>
                                @foreach($books as $book)
                                    <option value="{{ $book->id }}">{{ $book->title }} (ISBN: {{ $book->isbn }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                            <select name="branch_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                <option value="">Select a branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Weeding *</label>
                            <textarea name="reason" rows="4" required
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors"
                                      placeholder="Explain why this book should be considered for removal..."></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Book Condition</label>
                                <select name="condition"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                                    <option value="poor">Poor</option>
                                    <option value="fair">Fair</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="obsolete">Obsolete</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Calculate Usage Metrics</label>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="calculate_metrics" value="1" checked class="rounded border-gray-300 text-amber-600 shadow-sm focus:border-amber-300 focus:ring focus:ring-amber-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600">Automatically calculate usage data</span>
                                    </label>
                                </div>
                            </div>
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
                            Add Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Review Candidate Modal -->
<div id="reviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeReviewModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white font-serif">Review Candidate</h3>
                            <p id="reviewBookTitle" class="text-blue-100 text-sm"></p>
                        </div>
                    </div>
                    <button onclick="closeReviewModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="reviewForm" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div id="reviewContent">
                        <!-- Content will be loaded via AJAX -->
                        <div class="flex justify-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeReviewModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="action" value="approve"
                                class="px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors">
                            Approve
                        </button>
                        <button type="submit" name="action" value="reject"
                                class="px-6 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                            Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Filter Functions
    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const branch = document.getElementById('branchFilter').value;
        const condition = document.getElementById('conditionFilter').value;

        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);

        if (status) params.set('status', status);
        else params.delete('status');

        if (branch) params.set('branch_id', branch);
        else params.delete('branch_id');

        if (condition) params.set('condition', condition);
        else params.delete('condition');

        params.delete('page'); // Reset to first page
        window.location.href = `${url.pathname}?${params.toString()}`;
    }

    function clearFilters() {
        window.location.href = window.location.pathname;
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

    function openReviewModal(candidateId) {
        fetch(`/weeding-candidates/${candidateId}/review`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('reviewBookTitle').textContent = data.book_title;
                document.getElementById('reviewForm').action = `/weeding-candidates/${candidateId}/review`;

                document.getElementById('reviewContent').innerHTML = `
                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Candidate Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Book Title</p>
                                    <p class="font-semibold text-gray-900">${data.book_title}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">ISBN</p>
                                    <p class="font-semibold text-gray-900">${data.book_isbn}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Branch</p>
                                    <p class="font-semibold text-gray-900">${data.branch_name}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Condition</p>
                                    <p class="font-semibold text-gray-900">${data.condition}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Usage Metrics</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <p class="text-sm text-gray-600">Last Borrowed</p>
                                    <p class="font-semibold text-gray-900">${data.last_borrowed_date || 'Never'}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-600">Days Since</p>
                                    <p class="font-semibold ${data.days_since_last_borrow > 365 ? 'text-red-600' : 'text-green-600'}">${data.days_since_last_borrow} days</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-600">Total Borrows</p>
                                    <p class="font-semibold text-gray-900">${data.total_borrows}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 rounded-xl p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Reason for Weeding</h4>
                            <p class="text-gray-700 whitespace-pre-wrap">${data.reason}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Review Notes *</label>
                            <textarea name="review_notes" rows="4" required
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                      placeholder="Enter your review comments..."></textarea>
                        </div>
                    </div>
                `;

                openReviewModalWindow();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading candidate details. Please try again.');
            });
    }

    function openReviewModalWindow() {
        document.getElementById('reviewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function processCandidate(candidateId) {
        if(confirm('Process this candidate? This will mark the book as removed from the collection.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/weeding-candidates/${candidateId}/process`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeReviewModal();
        }
    });

    // Set filter values from URL parameters
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.get('status')) {
            document.getElementById('statusFilter').value = urlParams.get('status');
        }

        if (urlParams.get('branch_id')) {
            document.getElementById('branchFilter').value = urlParams.get('branch_id');
        }

        if (urlParams.get('condition')) {
            document.getElementById('conditionFilter').value = urlParams.get('condition');
        }
    });
</script>
@endsection
