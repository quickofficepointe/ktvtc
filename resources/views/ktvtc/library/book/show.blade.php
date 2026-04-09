@extends('ktvtc.library.layout.librarylayout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('library.books.index') }}"
           class="inline-flex items-center text-gray-600 hover:text-amber-600 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Books
        </a>
    </div>

    <!-- Book Details Card -->
    <div class="bg-white rounded-xl shadow-lg border border-amber-200 overflow-hidden">
        <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
            <h1 class="text-2xl font-bold text-white font-serif">{{ $book->title }}</h1>
            <p class="text-amber-100 mt-1">Book Details</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Book Cover -->
                <div class="md:col-span-1">
                    <div class="bg-gray-50 rounded-lg p-4 border border-amber-200">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}"
                                 alt="{{ $book->title }}"
                                 class="w-full object-cover rounded-lg shadow-md">
                        @else
                            <div class="w-full h-64 bg-amber-100 rounded-lg flex items-center justify-center">
                                <svg class="w-24 h-24 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Book Information -->
                <div class="md:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">ISBN</label>
                            <p class="text-gray-900 font-mono mt-1">{{ $book->isbn ?? 'N/A' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white"
                                      style="background-color: {{ $book->category->color ?? '#3b82f6' }}">
                                    {{ $book->category->name ?? 'Uncategorized' }}
                                </span>
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Authors</label>
                            <p class="text-gray-900 mt-1">
                                @if($book->authors->count() > 0)
                                    {{ $book->authors->pluck('full_name')->join(', ') }}
                                @else
                                    <span class="text-gray-400">No authors assigned</span>
                                @endif
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Publisher</label>
                            <p class="text-gray-900 mt-1">{{ $book->publisher ?? 'N/A' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Publication Year</label>
                            <p class="text-gray-900 mt-1">{{ $book->publication_year ?? 'N/A' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Language</label>
                            <p class="text-gray-900 mt-1">{{ $book->language ?? 'English' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pages</label>
                            <p class="text-gray-900 mt-1">{{ $book->page_count ?? 'N/A' }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</label>
                            <p class="text-gray-900 mt-1">${{ number_format($book->price ?? 0, 2) }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Copies</label>
                            <p class="text-gray-900 mt-1">{{ $book->total_copies }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Available Copies</label>
                            <p class="text-green-600 font-bold mt-1">{{ $book->available_copies }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $book->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $book->is_available ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                    {{ $book->is_available ? 'Available' : 'Unavailable' }}
                                </span>
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Added On</label>
                            <p class="text-gray-900 mt-1">{{ $book->created_at->format('F d, Y') }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Updated</label>
                            <p class="text-gray-900 mt-1">{{ $book->updated_at->format('F d, Y') }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($book->description)
                        <div class="bg-gray-50 rounded-lg p-4 mt-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</label>
                            <p class="text-gray-700 mt-2 leading-relaxed">{{ $book->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-amber-200">
                <button onclick="openEditModal({{ $book->id }})"
                    class="px-6 py-2 bg-amber-600 text-white rounded-lg font-medium hover:bg-amber-700 transition-colors shadow-md">
                    Edit Book
                </button>
                <button onclick="window.location.href='{{ route('library.books.index') }}'"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditModal(bookId) {
        // Redirect to edit page or open edit modal
        window.location.href = `/library/books/${bookId}/edit`;
    }
</script>
@endsection
