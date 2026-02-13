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
            <h1 class="text-3xl font-bold text-gray-900 font-serif">Book Popularities</h1>
            <p class="text-gray-600 mt-2">Track book engagement and popularity metrics</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="refreshPopularities()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh Data
            </button>
            <button onclick="generateReport()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generate Report
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 text-amber-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Most Popular Book</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $mostPopularScore ?? 0 }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Total Borrows</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalBorrows }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Reservations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalReservations }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Views</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalViews }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Books --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 font-serif mb-4">Top 5 Most Popular Books</h2>
        <div class="space-y-4">
            @forelse($topBooks as $index => $popularity)
                <div class="flex items-center justify-between p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-600 text-white font-bold">
                                {{ $index + 1 }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-3">
                            @if($popularity->book->cover_image)
                                <img src="{{ asset('storage/' . $popularity->book->cover_image) }}"
                                     alt="{{ $popularity->book->title }}"
                                     class="w-12 h-16 object-cover rounded-lg border border-amber-200">
                            @else
                                <div class="w-12 h-16 bg-amber-100 rounded-lg flex items-center justify-center border border-amber-200">
                                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $popularity->book->title }}</h3>
                                <p class="text-sm text-gray-600">
                                    @if($popularity->book->authors->count() > 0)
                                        {{ $popularity->book->authors->first()->full_name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-6">
                        <div class="text-center">
                            <div class="text-lg font-bold text-blue-600">{{ $popularity->borrow_count }}</div>
                            <div class="text-xs text-gray-500">Borrows</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-purple-600">{{ $popularity->reservation_count }}</div>
                            <div class="text-xs text-gray-500">Reservations</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-green-600">{{ $popularity->view_count }}</div>
                            <div class="text-xs text-gray-500">Views</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-amber-600">{{ number_format($popularity->popularity_score, 1) }}</div>
                            <div class="text-xs text-gray-500">Score</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <p>No popularity data available yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Popularities Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
            <h2 class="text-lg font-semibold text-gray-900 font-serif">Book Popularity Rankings</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Rank & Book</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Borrows</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Reservations</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Popularity Score</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-amber-800 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-amber-100">
                    @forelse($popularities as $index => $popularity)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                            {{ $index < 3 ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-800' }} font-bold">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            @if($popularity->book->cover_image)
                                                <img src="{{ asset('storage/' . $popularity->book->cover_image) }}"
                                                     alt="{{ $popularity->book->title }}"
                                                     class="w-12 h-16 object-cover rounded-lg border border-amber-200">
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
                                                {{ $popularity->book->title }}
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                @if($popularity->book->authors->count() > 0)
                                                    {{ $popularity->book->authors->pluck('full_name')->join(', ') }}
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                ISBN: {{ $popularity->book->isbn ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-800 font-bold text-lg">
                                        {{ $popularity->borrow_count }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">borrows</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 text-purple-800 font-bold text-lg">
                                        {{ $popularity->reservation_count }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">reservations</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-800 font-bold text-lg">
                                        {{ $popularity->view_count }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">views</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-center">
                                    <span class="inline-flex items-center justify-center w-16 h-16 rounded-full
                                        {{ $popularity->popularity_score >= 80 ? 'bg-green-100 text-green-800' :
                                           ($popularity->popularity_score >= 60 ? 'bg-blue-100 text-blue-800' :
                                           ($popularity->popularity_score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}
                                        font-bold text-xl">
                                        {{ number_format($popularity->popularity_score, 1) }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">score</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white"
                                      style="background-color: {{ $popularity->book->category->color }}">
                                    {{ $popularity->book->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewBookDetails({{ $popularity->book->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Book
                                    </button>
                                    <button onclick="updatePopularity({{ $popularity->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-amber-300 shadow-sm text-sm leading-4 font-medium rounded-md text-amber-700 bg-white hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Update
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No popularity data found</h3>
                                    <p class="text-gray-600 mb-4">Popularity data will be generated as books are borrowed and viewed.</p>
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

<script>
    function refreshPopularities() {
        if(confirm('Refresh all popularity scores? This will recalculate based on current data.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("book-popularities.refresh") }}';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function generateReport() {
        window.open('{{ route("book-popularities.report") }}', '_blank');
    }

    function updatePopularity(popularityId) {
        if(confirm('Update this book\'s popularity score?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/book-popularities/${popularityId}/update`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function viewBookDetails(bookId) {
        window.location.href = `/books/${bookId}`;
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
        }
    });
</script>
@endsection
