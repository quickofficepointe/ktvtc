@extends('ktvtc.library.layout.librarylayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Success/Error Messages --}}
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

    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Book Popularity Tracker</h1>
            <p class="text-gray-600 mt-2">Monitor and analyze book popularity based on borrows, reservations, and views</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('book-popularities.report') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generate Report
            </a>

        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Books Tracked</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['books_tracked'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Average Score</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_score'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Top Score</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['top_score'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Refresh All</p>
                    <form action="{{ route('book-popularities.refresh') }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Recalculate Scores
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Books</label>
                <input type="text" id="searchInput" placeholder="Title, Author..."
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                <select id="sortFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="popularity_desc">Popularity (High to Low)</option>
                    <option value="popularity_asc">Popularity (Low to High)</option>
                    <option value="borrow_desc">Borrow Count</option>
                    <option value="view_desc">View Count</option>
                    <option value="reservation_desc">Reservation Count</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Min Score</label>
                <input type="number" id="scoreFilter" min="0" max="100" step="0.1"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                       placeholder="0">
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()"
                    class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Popularity Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Book Popularity Rankings</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Book Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Borrows</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Reservations</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Popularity Score</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($popularities as $index => $popularity)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-lg font-bold text-gray-900">{{ $index + 1 }}</span>
                                    @if($index < 3)
                                        <div class="w-8 h-8 mt-1 flex items-center justify-center rounded-full
                                            @if($index === 0) bg-yellow-100 text-yellow-600
                                            @elseif($index === 1) bg-gray-100 text-gray-600
                                            @else bg-amber-100 text-amber-600 @endif">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($popularity->book->cover_image)
                                            <img src="{{ asset('storage/' . $popularity->book->cover_image) }}"
                                                 alt="{{ $popularity->book->title }}"
                                                 class="w-16 h-20 object-cover rounded-lg shadow-sm border border-amber-200">
                                        @else
                                            <div class="w-16 h-20 bg-amber-100 rounded-lg flex items-center justify-center border border-amber-200">
                                                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 font-serif hover:text-amber-700 cursor-pointer">
                                            {{ $popularity->book->title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            @if($popularity->book->authors->count() > 0)
                                                By {{ $popularity->book->authors->pluck('full_name')->join(', ') }}
                                            @else
                                                <span class="text-gray-400">No authors</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $popularity->book->category->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="text-lg font-semibold text-gray-900">{{ $popularity->borrow_count }}</span>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(($popularity->borrow_count / max($popularities->max('borrow_count'), 1)) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="text-lg font-semibold text-gray-900">{{ $popularity->reservation_count }}</span>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(($popularity->reservation_count / max($popularities->max('reservation_count'), 1)) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="text-lg font-semibold text-gray-900">{{ $popularity->view_count }}</span>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ min(($popularity->view_count / max($popularities->max('view_count'), 1)) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                        @if($popularity->popularity_score >= 50) bg-green-100 text-green-800
                                        @elseif($popularity->popularity_score >= 25) bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ number_format($popularity->popularity_score, 2) }}
                                    </span>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-amber-600 h-2 rounded-full" style="width: {{ min($popularity->popularity_score, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditModal({{ $popularity->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-amber-300 shadow-sm text-sm leading-4 font-medium rounded-md text-amber-700 bg-white hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <form action="{{ route('book-popularities.update', $popularity) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('POST')
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                            onclick="return confirm('Recalculate score for this book?')">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Recalc
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No popularity data found</h3>
                                    <p class="text-gray-600 mb-4">Start tracking book popularity to see rankings.</p>
                                   
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($popularities->hasPages())
            <div class="px-6 py-4 border-t border-amber-200 bg-amber-50">
                {{ $popularities->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white font-serif">Edit Popularity Data</h3>
                            <p class="text-amber-100 text-sm">Update book popularity metrics</p>
                        </div>
                    </div>
                    <button onclick="closeEditModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="editForm" method="POST" class="space-y-6">
                    @csrf
                    @method('POST')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Borrow Count</label>
                        <input type="number" name="borrow_count" id="editBorrowCount" min="0"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reservation Count</label>
                        <input type="number" name="reservation_count" id="editReservationCount" min="0"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">View Count</label>
                        <input type="number" name="view_count" id="editViewCount" min="0"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-colors">
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeEditModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700 transition-colors shadow-lg hover:shadow-xl">
                            Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal Functions
    function openEditModal(popularityId) {
        fetch(`/book-popularities/${popularityId}/edit`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editBorrowCount').value = data.borrow_count;
                document.getElementById('editReservationCount').value = data.reservation_count;
                document.getElementById('editViewCount').value = data.view_count;
                document.getElementById('editForm').action = `/book-popularities/${popularityId}/update`;
                document.getElementById('editModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading popularity data');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const sort = document.getElementById('sortFilter').value;
        const minScore = document.getElementById('scoreFilter').value;

        let url = '{{ route('book-popularities.index') }}?';
        if (search) url += `search=${encodeURIComponent(search)}&`;
        if (sort) url += `sort=${sort}&`;
        if (minScore) url += `min_score=${minScore}&`;

        window.location.href = url.slice(0, -1);
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEditModal();
        }
    });
</script>
@endsection
